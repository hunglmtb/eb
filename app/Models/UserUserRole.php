<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserUserRole extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_user_role';

	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user_role() 
	{
		return $this->belongsTo('App\Models\UserRole', 'ROLE_ID', 'ID');
	}
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user()
	{
		return $this->belongsTo('App\Models\User', 'USER_ID', 'ID');
	}
	
}
