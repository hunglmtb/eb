<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class PdCargoNomination extends DynamicModel 
{ 
	protected $table = 'PD_CARGO_NOMINATION'; 
	protected $primaryKey = 'ID';
	protected $dates = ['REQUEST_DATE'];
	protected $fillable  = ['NAME', 
							'CARGO_ID', 
							'IS_IMPORT', 
							'INCOTERM', 
							'REQUEST_DATE', 
							'REQUEST_QTY', 
							'REQUEST_QTY_UOM', 
							'ADJUSTABLE_TIME', 
							'REQUEST_TOLERANCE', 
							'PRIORITY', 
							'NOMINATION_DATE', 
							'NOMINATION_QTY', 
							'NOMINATION_UOM', 
							'NOMINATION_ADJ_TIME', 
							'TRANSIT_TYPE', 
							'PD_TRANSIT_CARRIER_ID', 
							'CARGO_STATUS', 
							'LNG_PURGE_QTY', 
							'LNG_GASUP_QTY', 
							'LNG_COOLDOWN_QTY', 
							'LAST_DEST_PORT', 
							'TOTAL_LADEN_TIME', 
							'EXCESS_LADEN_TIME', 
							'TOTAL_BALLAST_TIME', 
							'EXCESS_BALLAST_TIME', 
							'LNG_PRELOAD_TIME', 
							'TOTAL_LOAD_UNLOAD_TIME', 
							'BUNKERING_TIME', 
							'ROUND_TRIP_TIME', 
							'BL_DATE', 
							'BL_QTY', 
							'DEMURRAGE_CHARGE', 
							'PARCEL_QTY'];
} 
