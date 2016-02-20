<?php namespace App\Models;

use App\Models\DynamicModel;

class UserRoleRight extends DynamicModel  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_role_right';

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
	public function user_right()
	{
		return $this->belongsTo('App\Models\UserRight','RIGHT_ID', 'ID');
	}
	
}
