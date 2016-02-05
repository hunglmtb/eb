<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;
	
	protected $primaryKey = 'ID';
	
	public $timestamps = false;

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
		parent::__construct();
		$cn = config('database.default');
		if ($cn==='oracle'){
			$this->table = $this->table.'_';
			$this->primaryKey = 'id';
			$this->user_id_col = 'user_id';
		}
	
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
		
		// \DB::enableQueryLog();
		//\Log::info(var_dump($this));
		// $user_user_role = \DB::table('user_user_role')->where('role_id', $this->id)->first();
		$uk = $this->user_user_role();
		$uur = $uk->first();
		// \Log::info('hehe----------------------');
        // \Log::info(\DB::getQueryLog());  
		$ur = $uur->user_role()->first();
		$role = $ur->code;
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
