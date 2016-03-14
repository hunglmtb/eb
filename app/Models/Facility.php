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
	public function Area()
	{
		return $this->belongsTo('App\Models\LoArea', 'AREA_ID', 'ID');
	}
	
	public function Tank($fields=null)
	{
		if ($fields!=null&&is_array($fields)) {
			return $this->hasMany('App\Models\Tank', 'FACILITY_ID', 'ID')->select($fields);
		}
		return $this->hasMany('App\Models\Tank', 'FACILITY_ID', 'ID');
	}
	
	public function EnergyUnitGroup($fields=null)
	{
		if ($fields!=null&&is_array($fields)) {
			return $this->hasMany('App\Models\EnergyUnitGroup', 'FACILITY_ID', 'ID')->select($fields);
		}
		return $this->hasMany('App\Models\EnergyUnitGroup', 'FACILITY_ID', 'ID');
	}
	
}
