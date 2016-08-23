<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdVoyageDetail extends EbBussinessModel 
{ 
	protected $table = 'PD_VOYAGE_DETAIL';
	protected $fillable  	= ['VOYAGE_ID', 
								'CARGO_ID', 
								'PARCEL_NO', 
								'LIFTING_ACCOUNT', 
								'STORAGE_ID', 
								'QUANTITY_TYPE', 
								'LOAD_DATE', 
								'LOAD_QTY', 
								'LOAD_UOM', 
								'BERTH_ID'];
	
} 
