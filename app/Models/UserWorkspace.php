<?php

namespace App\Models;
use App\Models\DynamicModel;

class UserWorkspace extends DynamicModel
{
	protected $table = 'USER_WORKSPACE';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function areas()
	{
		return $this->hasMany('App\Models\LoArea', 'PRODUCTION_UNIT_ID', 'ID');
	}
}
