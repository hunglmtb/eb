<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class PdCargoSchedule extends DynamicModel 
{ 
	protected $table 		= 'PD_CARGO_SCHEDULE';
	protected $dates 		= ['SCHEDULE_DATE'];
	protected $fillable  	= ['CARGO_ID', 
								'SCHEDULE_DATE', 
								'SCHEDULE_QTY', 
								'SCHEDULE_UOM', 
								'TRANSIT_TYPE', 
								'PD_TRANSIT_CARRIER_ID', 
								'MASTER_NAME', 
								'BERTH_ID', 
								'CARGO_STATUS', 
								'ETA', 
								'ETD', 
								'LAYTIME', 
								'LAYCAN', 
								'COMMENT', 
								'NOMINATION_ID'];
	
} 
