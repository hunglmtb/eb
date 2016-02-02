<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDataScope extends Model
{
	protected $table = 'user_data_scope';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user()
	{
		return $this->hasMany('App\Models\EBUser', 'USER_ID', 'ID');
	}
}
