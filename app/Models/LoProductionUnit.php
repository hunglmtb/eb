<?php

namespace App\Models;
use App\Models\DynamicModel;

class LoProductionUnit extends DynamicModel
{
	protected $table = 'LO_PRODUCTION_UNIT';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function LoArea()
	{
		return $this->hasMany('App\Models\LoArea', 'PRODUCTION_UNIT_ID', 'ID');
	}
}
