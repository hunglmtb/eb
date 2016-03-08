<?php

namespace App\Models;
use App\Models\DynamicModel;

class LoArea extends DynamicModel
{
	protected $table = 'LO_AREA';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function productionUnit()
	{
		return $this->belongsTo('App\Models\LoProductionUnit', 'PRODUCTION_UNIT_ID', 'ID');
	}
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function facility()
	{
		return $this->hasMany('App\Models\Facility', 'AREA_ID', 'ID');
	}
	
}
