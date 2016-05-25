<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class EnergyUnitCoEntDayAlloc extends DynamicModel 
{ 
	protected $table = 'ENERGY_UNIT_CO_ENT_DAY_ALLOC'; 
	protected $primaryKey = 'ID';
	protected $fillable  = [
		'ID',
		'OCCUR_DATE',
		'EU_ID',
		'EVENT_TYPE',
		'ALLOC_TYPE',
		'FLOW_PHASE',
		'ACTIVE_HRS',
		'COST_INT_CTR_ID',
		'BA_ID',
		'EU_DAY_GRS_VOL',
		'EU_DAY_GRS_MASS',
		'EU_DAY_GRS_ENGY',
		'EU_DAY_GRS_PWR'
	];
} 
