<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class WellCompIntervalDayAlloc extends DynamicModel 
{ 
	protected $table = 'well_comp_interval_day_alloc'; 
	protected $primaryKey = 'ID';
	protected $fillable  = [
		'ID',
		'NAME',
		'OCCUR_DATE',
		'COMP_INTERVAL_ID',
		'EVENT_TYPE',
		'FLOW_PHASE',
		'ACTIVE_HRS',
		'GRS_VOL',
		'GRS_MASS',
		'GRS_PWR',
		'GRS_ENGY'
	];
} 
