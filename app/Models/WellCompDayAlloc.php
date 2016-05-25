<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class WellCompDayAlloc extends DynamicModel 
{ 
	protected $table = 'well_comp_day_alloc'; 
	protected $primaryKey = 'ID';
	protected $fillable  = [
		'ID',
		'NAME',
		'OCCUR_DATE',
		'COMP_ID',
		'EVENT_TYPE',
		'FLOW_PHASE',
		'ACTIVE_HRS',
		'GRS_VOL',
		'GRS_MASS',
		'GRS_PWR',
		'GRS_ENGY'
	];
} 
