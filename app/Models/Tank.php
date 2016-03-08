<?php

namespace App\Models;
use App\Models\DynamicModel;

class Tank extends DynamicModel
{
	protected $table = 'TANK';
	
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
