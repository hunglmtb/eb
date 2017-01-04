<?php 
namespace App\Models; 

 class KeystoreStorageDataValue extends FeatureKeystore { 
	protected $table 					= 'KEYSTORE_STORAGE_DATA_VALUE';
	protected static $objectModelName	= "KeystoreStorage";
	public  static $foreignKeystore 	= "KEYSTORE_STORAGE_ID";
	
	protected $fillable  = ['KEYSTORE_STORAGE_ID', 'OCCUR_DATE', 'BEGIN_VOL', 'END_VOL', 'FILLED_VOL', 'INJECTED_VOL', 'CONSUMED_VOL'];
	
	
} 
