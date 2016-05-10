<?php

namespace App\Models;
use App\Models\EbBussinessModel;
use App\Models\EuPhaseConfig;

class FeatureTankModel extends EbBussinessModel
{
	public  static  $enableLevelCalculating = false;
	public  static  $idField = 'TANK_ID';
	public  static  $typeName = 'TANK';
	public  static  $dateField = 'OCCUR_DATE';
	protected $objectModel = 'Tank';
	protected $excludeColumns = ['TANK_ID','OCCUR_DATE'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if (array_key_exists(config("constants.tankId"), $newData)) {
			$newData[static::$idField] = $newData[config("constants.tankId")];
			unset($newData[config("constants.tankId")]);
		}
		$newData[static::$dateField] = $occur_date;
		$cls = [static::$idField => $newData[static::$idField],
				static::$dateField=>$occur_date];
		if (array_key_exists(config("constants.tankFlowPhase"), $newData)) {
			$cls[config("constants.flowPhase")] = $newData[config("constants.tankFlowPhase")];
		}
		return $cls;
	}
	
	public static function calculateBeforeUpdateOrCreate(array &$attributes, array $values = []){
	
		if(array_key_exists(config("constants.flowPhase"), $attributes)
				&&array_key_exists(config("constants.tankIdColumn"),$attributes)
				&&array_key_exists("OCCUR_DATE",$attributes))//OIL or GAS
		{
			$fields = static::getCalculateFields();
			if ($fields) {
				static::updateValues($attributes,$values,'TANK',$fields);
			}
				
		}
		if(array_key_exists(config("constants.flowPhase"), $attributes)) unset($attributes[config("constants.flowPhase")]);
		return $values;
	}
	
	public static function getCalculateFields() {
		return null;
	}
	
	
	public static function updateValues(array $attributes, array &$values = [], $type, $fields) {
		if (array_key_exists(config("constants.extraFields"), $fields)) {
			$object_id = $attributes [$fields [config ( "constants.keyField" )]];
			$occur_date = $attributes ["OCCUR_DATE"];
			
			$extraFields = $fields[config("constants.extraFields")];
			$sourceFields = array_keys($extraFields);
// 			\DB::enableQueryLog();
			$yesterdayRecord = static :: where('OCCUR_DATE','=',$occur_date->copy()->subDay())
										->where(static::$idField,'=',$object_id)
										->select($sourceFields)
										->first();
// 			\Log::info(\DB::getQueryLog());
			if ($yesterdayRecord!=null) {
				foreach ( $extraFields as $sourceField => $targetField ) {
					$values[$targetField] = $yesterdayRecord->$sourceField;
				}
			}
		}
		if (array_key_exists(config("constants.mainFields"), $fields)) {
			$mainFields = $fields[config("constants.mainFields")];
			$mainFields[config ( "constants.keyField" )] = $fields [config ( "constants.keyField" )]; 
			parent::updateValues($attributes, $values, $type,$mainFields);
		}
	}
	
	/* public static function updateWithFormularedValues($values,$object_id,$occur_date,$flow_phase) {
	
		$newData = [static::$idField=>$object_id,config("constants.euFlowPhase")=>$flow_phase];
		$attributes = static::getKeyColumns($newData,$occur_date,null);
	
		return parent::updateOrCreate($attributes,$values);;
	} */
}
