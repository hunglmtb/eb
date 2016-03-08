<?php

namespace App\Models;
use App\Models\DynamicModel;

class EnergyUnitGroup extends DynamicModel
{
	protected $table = 'ENERGY_UNIT_GROUP';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function facility()
	{
		return $this->belongsTo('App\Models\Facility', 'FACILITY_ID', 'ID');
	}	
}
