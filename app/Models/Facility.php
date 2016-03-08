<?php

namespace App\Models;
use App\Models\DynamicModel;

class Facility extends DynamicModel
{
	protected $table = 'FACILITY';
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function area()
	{
		return $this->belongsTo('App\Models\LoArea', 'AREA_ID', 'ID');
	}
	
	public function tank()
	{
		return $this->hasMany('App\Models\Tank', 'FACILITY_ID', 'ID');
	}
	
	public function energyUnitGroup()
	{
		return $this->hasMany('App\Models\EnergyUnitGroup', 'FACILITY_ID', 'ID');
	}
	
	
}
