<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class FlowDayAlloc extends DynamicModel 
{ 
	protected $table = 'FLOW_DAY_ALLOC'; 
	protected $primaryKey = 'ID';
	protected $fillable  = [
		'ID',
		'FLOW_ID',
		'OCCUR_DATE',
		'ACTIVE_HRS',
		'RECORD_FREQUENCY',
		'DISP',
		'FL_DAY_GRS_VOL',
		'FL_DAY_NET_VOL',
		'FL_DAY_SW_PCT',
		'FL_DAY_GRS_WTR_VOL',
		'FL_DAY_GRS_MASS',
		'FL_DAY_NET_MASS',
		'FL_DAY_GRS_WTR_MASS',
		'FL_DAY_GRS_ENGY',
		'FL_DAY_GRS_PWR',
		'FL_DAY_DENS',
		'STATUS_BY',
		'STATUS_DATE',
		'RECORD_STATUS'
	];
} 
