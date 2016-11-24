<?php 
namespace App\Models; 

 class KeystoreStorageDataValue extends FeatureKeystore { 
	protected $table 					= 'KEYSTORE_STORAGE_DATA_VALUE';
	protected static $objectModel 		= "KeystoreStorage";
	public  static $foreignKeystore 	= "KEYSTORE_STORAGE_ID";
	
} 
