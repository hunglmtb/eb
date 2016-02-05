<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_role';

	
	// public function role() 
	// {
		// $cn = config('database.default');
		// if ($cn==='oracle'){
			// return $this->code;
		// }
		// else{
			// return $this->CODE;
		// }
	// }
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user_user_role() 
	{
	  return $this->hasMany('App\Models\UserUserRole','ROLE_ID', 'ID');
	}
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user_role_right()
	{
		return $this->hasMany('App\Models\UserRoleRight','ROLE_ID', 'ID');
	}

}
