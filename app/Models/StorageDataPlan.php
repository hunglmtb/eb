<?php 
namespace App\Models; 
 

 class StorageDataPlan extends FeatureStorageModel 
{ 
	protected $table = 'storage_data_plan'; 
	protected $objectModel = 'Storage';
	public  static  $idField = 'STORAGE_ID';
// 	public static $unguarded = true;
// 	public  static $ignorePostData = true;

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
							'SW', 
							'STORAGE_GRS_VOL', 
							'STORAGE_NET_VOL', 
							'STORAGE_DENSITY', 
							'STORAGE_GRS_MASS', 
							'STORAGE_NET_MASS', 
							'STORAGE_GRS_ENGY', 
							'STORAGE_GRS_PWR'];
	
 } 
