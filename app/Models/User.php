<?php namespace App\Models;

use App\Models\DynamicModel;
use App\Models\UserRight;
use App\Models\UserRole;
use App\Models\UserRoleRight;
use App\Models\UserUserRole;
use App\Models\UserWorkspace;
use App\Models\DateTimeFormat;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends DynamicModel implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;
	
	public $timestamps = false;
	protected $primaryKey = 'ID';
	protected $user_id_col = 'USER_ID';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];


	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		parent::__construct();
	}
	
	
	public function userWorkspace()
	{
		return $this->hasOne('App\Models\UserWorkspace',$user_id_col,$primaryKey);
	}
	
	public function workspace()
	{
// 		\DB::enableQueryLog();
	
		$wp = UserWorkspace::join( $this->table, $this->table.'.ID', '=', 'USER_WORKSPACE.USER_ID')
		->join('FACILITY', 'USER_WORKSPACE.W_FACILITY_ID', '=', 'FACILITY.ID')
		->join('LO_AREA', 'FACILITY.AREA_ID', '=', 'LO_AREA.ID')
		->join('LO_PRODUCTION_UNIT', 'LO_AREA.PRODUCTION_UNIT_ID', '=', 'LO_PRODUCTION_UNIT.ID')
		->where( $this->table.'.ID', '=', $this->ID)
		->select('USER_WORKSPACE.*','FACILITY.AREA_ID', 'LO_AREA.PRODUCTION_UNIT_ID')
		->get()->first();
		
// 		\Log::info(\DB::getQueryLog());
	
		return $wp;
	}
	
	public function saveWorkspace($date_begin,$facility_id,$date_end=false)
	{
		// 		\DB::enableQueryLog();
		$columns = ['USER_ID'=>$this->ID];
		$newData = ['USER_ID'=>$this->ID,'USER_NAME'=>$this->username];
		if ($date_begin) {
 			$date_begin = \Helper::parseDate($date_begin);
			$newData['W_DATE_BEGIN']=$date_begin;
		}
		if ($facility_id) $newData['W_FACILITY_ID']=$facility_id;
		if ($date_end) {
 			$date_end 	= \Helper::parseDate($date_end);
 			$newData['W_DATE_END']=$date_end;
		}
		return  UserWorkspace::updateOrCreate($columns, $newData);
	}
	
	public function saveDateTimeFormat($dateformat,$timeformat){
		$columns = ['USER_ID'=>$this->ID];
		$newData = ['USER_ID'=>$this->ID,'USER_NAME'=>$this->username];
		if ($dateformat) $newData['DATE_FORMAT']	=	$dateformat;
		if ($timeformat) $newData['TIME_FORMAT']	=	$timeformat;
		return  UserWorkspace::updateOrCreate($columns, $newData);
	}
	
	public function saveNumberFormat($numberformat){
		$columns = ['USER_ID'=>$this->ID];
		$newData = ['USER_ID'=>$this->ID,'USER_NAME'=>$this->username];
		if (array_key_exists('DECIMAL_MARK', $numberformat)) $newData['DECIMAL_MARK']	=	$numberformat['DECIMAL_MARK'];
		return  UserWorkspace::updateOrCreate($columns, $newData);
	}
	
	
	
	public function configuration(){
		$row	= 	UserWorkspace::where('USER_ID','=',$this->ID)
						->select('DATE_FORMAT','TIME_FORMAT','DECIMAL_MARK')
						->first();
		$formatSetting = [];
		$formatSetting['DATE_FORMAT'] 	= $row&&$row->DATE_FORMAT?$row->DATE_FORMAT:	DateTimeFormat::$defaultFormat['DATE_FORMAT'];
		$formatSetting['TIME_FORMAT'] 	= $row&&$row->TIME_FORMAT?$row->TIME_FORMAT:	DateTimeFormat::$defaultFormat['TIME_FORMAT'];
		$formatSetting['DECIMAL_MARK'] 	= $row&&$row->DECIMAL_MARK?$row->DECIMAL_MARK:	DateTimeFormat::$defaultFormat['DECIMAL_MARK'];
		return $formatSetting;
	}
	
	public function getConfiguration()
	{
// 		$formatSetting 		= 	$this->configuration();//session('configuration');
		$formatSetting 		= 	session('configuration');
		$formatSetting 		= 	$formatSetting?$formatSetting:DateTimeFormat::$defaultFormat;
		$dateFormat 		= 	$formatSetting['DATE_FORMAT']?$formatSetting['DATE_FORMAT']:	DateTimeFormat::$defaultFormat['DATE_FORMAT'];
		$timeFormat 		= 	$formatSetting['TIME_FORMAT']?$formatSetting['TIME_FORMAT']:	DateTimeFormat::$defaultFormat['TIME_FORMAT'];
		$decimalMarkFormat 	= 	$formatSetting['DECIMAL_MARK']?$formatSetting['DECIMAL_MARK']:	DateTimeFormat::$defaultFormat['DECIMAL_MARK'];
		$lowerDateFormat	= 	strtolower($dateFormat);
		$carbonFormat		= 	\Helper::convertDate2CarbonFormat($dateFormat);
		$jqueryFormat		= 	\Helper::convertDate2JqueryFormat($dateFormat);
		$pickerTimeFormat	= 	\Helper::convertTime2PickerFormat($timeFormat);
		$timeFormatSet =  [
				'DATE_FORMAT'				=>		$dateFormat,//'MM/DD/YYYY',
				'TIME_FORMAT'				=>		$timeFormat,//'hh:mm A',
				'DATETIME_FORMAT'			=>		"$dateFormat $timeFormat",// 'MM/DD/YYYY HH:mm',
				'DATE_FORMAT_UTC'			=>		'YYYY-MM-DD',
				'TIME_FORMAT_UTC'			=>		'hh:mm:ss',
				'DATETIME_FORMAT_UTC'		=>		'YYYY-MM-DD HH:mm:ss',
				'DATE_FORMAT_CARBON'		=>		$carbonFormat//'m/d/Y',
		];
		
		$picker =  [
				'DATE_FORMAT'			=>		$lowerDateFormat,//'mm/dd/yyyy',
				'TIME_FORMAT'			=>		$pickerTimeFormat,//'HH:ii P',
				'DATETIME_FORMAT'		=>		"$lowerDateFormat $pickerTimeFormat",/* strtolower($timeFormatSet['DATETIME_FORMAT']),// *///'mm/dd/yyyy hh:ii',
				'DATE_FORMAT_UTC'		=>		'mm/dd/yyyy',
				'TIME_FORMAT_UTC'		=>		'hh:ii:ss',
				'DATETIME_FORMAT_UTC'	=>		'mm/dd/yyyy hh:ii',
				'DATE_FORMAT_JQUERY'	=>		$jqueryFormat//'mm/dd/yy',
		];
		$sample = DateTimeFormat::getSample($formatSetting);
		
		$numberFormat = ['DECIMAL_MARK' =>$decimalMarkFormat];
		return [
				'time'		=>	$timeFormatSet,
				'picker'	=>	$picker,
				'number'	=>	$numberFormat,
				'sample'	=>	$sample,
		];
	}
	
	
	

	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user_data_scope()
	{
		return $this->hasMany('App\Models\UserDataScope', $this->user_id_col, $this->primaryKey);
	}
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function role()
	{
		$user_user_role = UserUserRole::getTableName();
		$user_role_right = UserRoleRight::getTableName();
		$user_right = UserRight::getTableName();
		
		$rows= UserUserRole::join($user_role_right,"$user_user_role.ROLE_ID", '=', "$user_role_right.ROLE_ID")
							->join($user_right,"$user_right.ID", '=', "$user_role_right.RIGHT_ID")
							->where("$user_user_role.USER_ID",$this->ID)
							->select("$user_right.CODE")
							->distinct()
							->get();
		$rs = $rows?$rows->map(function ($item, $key) {
				    			return $item->CODE;
					})->toArray():[];
		return $rs;
	}
	
	
	public function UserRole()
	{
		return $this->belongsToMany ('App\Models\UserRole',UserUserRole::getTableName(),$this->user_id_col,'ROLE_ID');
	}
	
	
	public function right()
	{
	
		$uk = $this->UserUserRole();
		$uur = $uk->first();
		$ur = $uur->UserRole()->get(['CODE']);
		return $ur ;
	}
	
	public function hasRight($right){
// 		$USER_RIGHTS = session('statut');
// 		$result = $USER_RIGHTS&&count($USER_RIGHTS)>0&&in_array("_ALL_", $USER_RIGHTS)||in_array($right, $USER_RIGHTS);
		$USER_RIGHTS = $this->role();
		$result = $USER_RIGHTS&&count($USER_RIGHTS)>0&&in_array($right, $USER_RIGHTS);
		return $result ;
	}
	public function containRight($right){
		$USER_RIGHTS = $this->role();
		$result = $USER_RIGHTS&&count($USER_RIGHTS)>0&&(in_array("_ALL_", $USER_RIGHTS)||in_array($right, $USER_RIGHTS));
		return $result ;
	}
	
	public function updateLogoutLog(){
		$logUser = LogUser::where(['SESSION_ID'=>session()->getId()])->first();
		if ($logUser) {
			$values = [
					'LOGOUT_TIME'	=>	Carbon::now(),
			];
			$logUser->fill($values)->save();
		}
	}
	
	public function updateLoginLog(){
		$attributes = ['SESSION_ID'=>session()->getId()];
		$logUser = LogUser::firstOrNew($attributes);
		$values = [	'USERNAME'		=>	$this->username, 
					'LOGIN_TIME'	=>	Carbon::now(), 
					'IP'			=>	request()->ip(),
					'SESSION_ID'=>session()->getId()
		];
		$logUser->fill($values)->save();
	}
	
	public function hasRole($roleCode)
	{
		if($this->ID)
		{
			$user_user_role = UserUserRole::getTableName();
			$user_role = UserRole::getTableName();
			
			$rows= UserUserRole::join($user_role,"$user_user_role.ROLE_ID", '=', "$user_role.ID")			
			->where([$user_role.".CODE"=>$roleCode, $user_user_role.".USER_ID"=>$this->ID])
			->select($user_role.".CODE")
			->distinct()
			->get();
			
			if(count($rows) > 0){
				return true;
			}else{
				return false;
			}
		}
		return false;
	}
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function UserUserRole()
	{
		return $this->hasMany('App\Models\UserUserRole',$this->user_id_col, $this->primaryKey);
	}

	public function UserRoleRight()
	{
		return $this->hasMany('App\Models\UserRoleRight',$this->user_id_col, $this->primaryKey);
	}
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function posts() 
	{
	  return $this->hasMany('App\Models\Post');
	}

	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function comments() 
	{
	  return $this->hasMany('App\Models\Comment');
	}

	/**
	 * Check media all access
	 *
	 * @return bool
	 */
	public function accessMediasAll()
	{
	    return $this->role->slug == 'admin';
	}

	/**
	 * Check media access one folder
	 *
	 * @return bool
	 */
	public function accessMediasFolder()
	{
	    return $this->role->slug != 'user';
	}

}
