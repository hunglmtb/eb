<?php 
namespace App\Models; 
use App\Models\FeatureTankModel; 

 class StorageDataValue extends FeatureTankModel 
{ 
	public  static $ignorePostData = true;
	protected $table = 'STORAGE_DATA_VALUE'; 
	protected $dates = ['LAST_DATA_READ'];
	
	protected $fillable  = ['OCCUR_DATE', 
							'LAST_DATA_READ', 
							'STORAGE_ID', 
							'BEGIN_LEVEL', 
							'END_LEVEL', 
							'BEGIN_VOL', 
							'END_VOL', 
							'BEGIN_LEVEL2', 
							'END_LEVEL2', 
							'BEGIN_VOL2', 
							'END_VOL2', 
							'AVAIL_SHIPPING_VOL', 
							'SW', 
							'STORAGE_GRS_VOL', 
							'STORAGE_NET_VOL', 
							'STORAGE_DENSITY', 
							'STORAGE_GRS_MASS', 
							'STORAGE_NET_MASS', 
							'STATUS_BY', 
							'STATUS_DATE', 
							'RECORD_STATUS'];
	
 } 
