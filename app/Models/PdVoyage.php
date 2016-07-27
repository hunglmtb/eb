<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class PdVoyage extends DynamicModel 
{ 
	protected $table 		= 'PD_VOYAGE';
	protected $dates 		= ['SCHEDULE_DATE'];
	protected $fillable  	= ['CODE', 
								'NAME', 
								'MASTER_NAME', 
								'CARRIER_ID', 
								'CARGO_ID', 
								'LIFTING_ACCOUNT', 
								'STORAGE_ID', 
								'VOYAGE_NO', 
								'INCOTERM', 
								'SCHEDULE_DATE', 
								'ADJUSTABLE_TIME', 
								'SCHEDULE_QTY', 
								'QUANTITY_TYPE', 
								'SCHEDULE_UOM', 
								'BERTH_ID', 
								'CONSIGNER', 
								'CONSIGNEE_1', 
								'CONSIGNEE_2', 
								'LOAD_PORT_SAMPLE_ID1', 
								'LOAD_PORT_SAMPLE_ID2', 
								'NOMINATION_ID'];
	
} 
