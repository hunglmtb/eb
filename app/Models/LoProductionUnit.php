<?php

namespace App\Models;
use App\Models\DynamicModel;

class LoProductionUnit extends DynamicModel
{
	protected $table = 'LO_PRODUCTION_UNIT';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	/* public function user_role_right()
	{
		return $this->hasMany('App\Models\UserRoleRight','RIGHT_ID', 'ID');
	} */
}
