<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class EnergyUnitDayAlloc extends DynamicModel 
{ 
	protected $table = 'ENERGY_UNIT_DAY_ALLOC';
	protected $primaryKey = 'ID';
	protected $fillable  = [	
		'ID',
		'OCCUR_DATE',
		'EU_ID',
		'EVENT_TYPE',
		'ALLOC_TYPE',
		'FLOW_PHASE',
		'ACTIVE_HRS',
		'EU_DAY_GRS_VOL',
		'EU_DAY_GRS_MASS',
		'EU_DAY_GRS_ENGY',
		'EU_DAY_GRS_PWR',
		'STATUS_BY',
		'STATUS_DATE',
		'RECORD_STATUS'
	];
} 
