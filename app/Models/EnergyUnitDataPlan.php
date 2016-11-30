<?php

namespace App\Models;
use App\Models\FeatureEuModel;

class EnergyUnitDataPlan extends FeatureEuModel
{
	protected $table = 'ENERGY_UNIT_DATA_PLAN';
	
	protected $primaryKey = 'ID';
	protected $fillable  = ['OCCUR_DATE', 
							'EU_ID', 
							'EVENT_TYPE', 
							'FLOW_PHASE', 
							'ACTIVE_HRS', 
							'EU_DATA_GRS_VOL', 
							'EU_DATA_GRS_MASS', 
							'EU_DATA_GRS_ENGY', 
							'EU_DATA_GRS_PWR', 
							'RECORD_STATUS', 
							'STATUS_BY', 
							'STATUS_DATE',
							'EU_DATA_NET_MASS'
	];
	
}
