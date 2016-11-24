<?php 
namespace App\Models; 
 

 class KeystoreTankDataValue extends FeatureKeystore 
{ 
	protected $table 					= 'keystore_tank_data_value'; 
	public static $objectModel 			= "KeystoreTank";
	public  static $foreignKeystore 	= "KEYSTORE_TANK_ID";
 } 
