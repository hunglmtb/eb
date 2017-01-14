<?php 
namespace App\Models; 
 

 class KeystoreInjectionPointDay extends FeatureKeystore 
{ 
	protected $table 				= 'KEYSTORE_INJECTION_POINT_DAY';
	public static $foreignKeystore 	= "INJECTION_POINT_ID";
	protected $dates 				= ['OCCUR_DATE'];
	// 	protected static $objectModelName = "KeystoreInjectionPoint"; 'INJECTION_POINT_ID'

	protected $fillable  = ['OCCUR_DATE', 'INJECTION_POINT_ID', 'KEYSTORE_ID', 'INJECTED_VOL'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if (!array_key_exists("OCCUR_DATE",$newData)|| !$newData["OCCUR_DATE"]||$newData["OCCUR_DATE"]==''){
			$newData["OCCUR_DATE"] 		= $occur_date;
		}
		return ["INJECTION_POINT_ID" 	=> $newData["INJECTION_POINT_ID"],
				"KEYSTORE_ID" 			=> $newData["KEYSTORE_ID"],
				"OCCUR_DATE" 			=> $occur_date,
		];
	}
	
	public static function getEntries($facility_id=null,$product_type = 0){
		return KeystoreInjectionPoint::select('ID','NAME')->get();
	}
} 
