<?php namespace App\Models;

use App\Models\DynamicModel;
use App\Models\UserWorkspace;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Carbon\Carbon;
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
	
		$wp = User::join('USER_WORKSPACE', $this->table.'.ID', '=', 'USER_WORKSPACE.USER_ID')
		->join('FACILITY', 'USER_WORKSPACE.W_FACILITY_ID', '=', 'FACILITY.ID')
		->join('LO_AREA', 'FACILITY.AREA_ID', '=', 'LO_AREA.ID')
		->join('LO_PRODUCTION_UNIT', 'LO_AREA.PRODUCTION_UNIT_ID', '=', 'LO_PRODUCTION_UNIT.ID')
		->where( $this->table.'.ID', '=', $this->ID)
		->select('USER_WORKSPACE.*', 'USER_WORKSPACE.W_DATE_BEGIN','USER_WORKSPACE.W_DATE_END', 'FACILITY.AREA_ID', 'LO_AREA.PRODUCTION_UNIT_ID')
		->get()->first();
		
		$wp->W_DATE_BEGIN = Carbon::parse($wp->W_DATE_BEGIN);
		$wp->W_DATE_END = Carbon::parse($wp->W_DATE_END);
	
// 		\Log::info(\DB::getQueryLog());
	
		return $wp;
	}
	
	public function saveWorkspace($date_begin,$facility_id,$date_end=false)
	{
		// 		\DB::enableQueryLog();
		$columns = ['USER_ID'=>$this->ID];
		$newData = [];
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
		
// 		\DB::enableQueryLog();
		//\Log::info(var_dump($this));
		// $user_user_role = \DB::table('user_user_role')->where('role_id', $this->id)->first();
		$uk = $this->user_user_role();
		$uur = $uk->first();
		$ur = $uur->user_role()->first();
		$role = $ur->CODE;
// 		\Log::error('hehe------------ROLE----------'.$role .' HEHE' );
//         \Log::info(\DB::getQueryLog());  
		return $role ;
	}
	
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user_user_role()
	{
		return $this->hasMany('App\Models\UserUserRole',$this->user_id_col, $this->primaryKey);
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
