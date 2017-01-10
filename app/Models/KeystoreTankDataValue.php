<?php 
namespace App\Models; 
 

 class KeystoreTankDataValue extends FeatureKeystore 
{ 
	protected $table 					= 'keystore_tank_data_value'; 
	public static $objectModelName 		= "KeystoreTank";
	public  static $foreignKeystore 	= "KEYSTORE_TANK_ID";
	protected $dates 					= ['OCCUR_DATE'];
	
	protected $fillable  = ['KEYSTORE_TANK_ID', 'OCCUR_DATE', 'BEGIN_LEVEL', 'END_LEVEL', 'BEGIN_VOL', 'END_VOL', 'FILLED_VOL', 'INJECTED_VOL', 'CONSUMED_VOL' ];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if (!array_key_exists("OCCUR_DATE",$newData)|| !$newData["OCCUR_DATE"]||$newData["OCCUR_DATE"]==''){
			$newData["OCCUR_DATE"] 		= $occur_date;
		}
		return ["KEYSTORE_TANK_ID" 		=> $newData["KEYSTORE_TANK_ID"],
				"OCCUR_DATE" 			=> $newData["OCCUR_DATE"],
		];
	}
	
	public static function findManyWithConfig($updatedIds){
		
		$keystoreTankDataValue			= KeystoreTankDataValue::getTableName();
		$keystoreTank					= KeystoreTank::getTableName();
		$codeProductType 				= CodeProductType::getTableName();
		
		$dataSet 						= KeystoreTankDataValue::/* join($codeProductType,"$keystoreTank.PRODUCT",'=',"$codeProductType.ID")
											-> */join($keystoreTank,
													"$keystoreTankDataValue.KEYSTORE_TANK_ID",
													'=',
													"$keystoreTank.ID")
										->whereIn("$keystoreTankDataValue.ID",$updatedIds)
										->select(
											"$keystoreTankDataValue.*",
											"$keystoreTankDataValue.ID as DT_RowId",
											"$keystoreTank.NAME as $keystoreTankDataValue",
											"$keystoreTank.PRODUCT as FL_FLOW_PHASE"
// 											"$codeProductType.NAME as PHASE_NAME"
										)
										->orderBy("$keystoreTank.PRODUCT")
										->get();
		return $dataSet;
	}
 } 
