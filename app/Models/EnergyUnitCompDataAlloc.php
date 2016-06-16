<?php

namespace App\Models;
use App\Models\FeatureEuModel;

class EnergyUnitCompDataAlloc extends FeatureEuModel
{
	protected $table = 'ENERGY_UNIT_COMP_DATA_ALLOC'; 
	protected $primaryKey = 'ID';
	protected $fillable  = [
			'ID',
			'OCCUR_DATE',
			'EU_ID',
			'EVENT_TYPE',
			'ALLOC_TYPE',
			'FLOW_PHASE',
			'COMPOSITION',
			'ACTIVE_HRS',
			'EU_DATA_GRS_VOL',
			'EU_DATA_GRS_MASS',
			'EU_DATA_GRS_ENGY',
			'EU_DATA_GRS_PWR'
	];	
}
