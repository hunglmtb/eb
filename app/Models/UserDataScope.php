<?php

namespace App\Models;

use App\Models\DynamicModel;

class UserDataScope extends DynamicModel
{
	protected $table = 'user_data_scope';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user()
	{
		return $this->hasMany('App\Models\User', 'USER_ID', 'ID');
	}
}
