<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRight extends Model
{
	protected $table = 'user_right';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user_role_right()
	{
		return $this->hasMany('App\Models\UserRoleRight','RIGHT_ID', 'ID');
	}
}
