<?php namespace App\Models;

use App\Models\DynamicModel;
use App\Models\UserRight;
use App\Models\UserRole;
use App\Models\UserRoleRight;
use App\Models\UserUserRole;
use App\Models\UserWorkspace;

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
		
// 		$wp->W_DATE_BEGIN = Carbon::parse($wp->W_DATE_BEGIN);
// 		$wp->W_DATE_END = Carbon::parse($wp->W_DATE_END);
	
// 		\Log::info(\DB::getQueryLog());
	
		return $wp;
	}
	
	public function saveWorkspace($date_begin,$facility_id,$date_end=false)
	{
		// 		\DB::enableQueryLog();
		$columns = ['USER_ID'=>$this->ID];
		$newData = ['USER_ID'=>$this->ID,'USER_NAME'=>$this->username];
		if ($date_begin) {
 			$date_begin = Carbon::parse($date_begin);
			$newData['W_DATE_BEGIN']=$date_begin;
		}
		if ($facility_id) $newData['W_FACILITY_ID']=$facility_id;
		if ($date_end) {
 			$date_end = Carbon::parse($date_end);
			$newData['W_DATE_END']=$date_end;
		}
		return  UserWorkspace::updateOrCreate($columns, $newData);
	}
	
	public function getConfiguration()
	{
		$timeFormat =  [
				'DATE_FORMAT'				=>		'MM/DD/YYYY',
				'TIME_FORMAT'				=>		'hh:mm A',
				'DATETIME_FORMAT'			=>		'MM/DD/YYYY HH:mm',
				'DATE_FORMAT_UTC'			=>		'YYYY-MM-DD',
				'TIME_FORMAT_UTC'			=>		'hh:mm:ss',
				'DATETIME_FORMAT_UTC'		=>		'YYYY-MM-DD HH:mm:ss',
				'DATE_FORMAT_CARBON'		=>		'm/d/Y',
		];
		
		$picker =  [
				'DATE_FORMAT'			=>		'mm/dd/yyyy',
				'TIME_FORMAT'			=>		'HH:ii P',
				'DATETIME_FORMAT'		=>		'mm/dd/yyyy hh:ii',
				'DATE_FORMAT_UTC'		=>		'mm/dd/yyyy',
				'TIME_FORMAT_UTC'		=>		'hh:ii:ss',
				'DATETIME_FORMAT_UTC'	=>		'mm/dd/yyyy hh:ii',
				'DATE_FORMAT_JQUERY'	=>		'mm/dd/yy',
		];
		return [
				'time'		=>	$timeFormat,
				'picker'	=>	$picker,
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
		/* $userRole = $this->UserRole();
		$roles = $userRole->pluck('CODE'); */
		/* $sSQL="select distinct c.CODE 
		from user_user_role a, 
		user_role_right b, 
		user_right c 
		where a.user_id=$current_user_id 
		and a.role_id=b.role_id and 
		b.right_id=c.id"; */
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
		/* $rs = [];
		$row= UserUserRole::with(['UserRoleRight' => function ($query) {
															$query->with('UserRight');
													}]
							)->where('USER_ID',$this->ID);
		
							
		$roles = $row->get();
		
		foreach ($roles as $role){
			$rs[] = $role->UserRoleRight->UserRight->CODE;
		} */
		//\Log::info(var_dump($this));
		// $UserUserRole = \DB::table('UserUserRole')->where('role_id', $this->id)->first();
		/* $uk = $this->with('UserUserRole.UserRole')->get();
		$uk = $this->UserUserRole()->get(); */
		/* $uur = $uk->first();
		$ur = $uur->UserRole()->get(['CODE']); */
// 		$ur = $uur->UserRole()->first();
// 		$role = $ur->CODE;
// 		\Log::error('hehe------------ROLE----------'.$role .' HEHE' );
//         \Log::info(\DB::getQueryLog());  
		
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
