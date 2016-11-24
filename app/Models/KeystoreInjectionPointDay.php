<?php 
namespace App\Models; 
 

 class KeystoreInjectionPointDay extends FeatureKeystore 
{ 
	protected $table 				= 'KEYSTORE_INJECTION_POINT_DAY';
	public static $foreignKeystore 	= "INJECTION_POINT_ID";
	// 	protected static $objectModel = "KeystoreInjectionPoint"; `INJECTION_POINT_ID`
	
	public static function getEntries($facility_id=null,$product_type = 0){
		return KeystoreInjectionPoint::select('ID','NAME')->get();
	}
} 
