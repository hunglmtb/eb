<?php

namespace App\Models;
use App\Models\DynamicModel;

class EnergyUnit extends DynamicModel
{

	protected $table = 'ENERGY_UNIT';
	protected $primaryKey = 'ID';
	
	
	public function EuPhaseConfig()
	{
		return $this->hasMany('App\Models\EuPhaseConfig', 'EU_ID', $this->primaryKey);
	}
	
	public function CodeStatus()
	{
		return $this->belongsTo('App\Models\CodeStatus', 'STATUS', $this->primaryKey);
	}
}
