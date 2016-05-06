<?php

namespace App\Models;
use App\Models\EbBussinessModel;
use App\Models\EuPhaseConfig;

class FeatureTankModel extends EbBussinessModel
{
	public  static  $idField = 'TANK_ID';
	public  static  $typeName = 'ENERGY_UNIT';
	public  static  $dateField = 'OCCUR_DATE';
	protected $objectModel = 'Tank';
	protected $excludeColumns = ['TANK_ID','OCCUR_DATE'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if (array_key_exists(config("constants.tankId"), $newData)) {
			$newData[static::$idField] = $newData[config("constants.tankId")];
			unset($newData[config("constants.tankId")]);
		}
		$newData[static::$dateField] = $occur_date;
		return [static::$idField => $newData[static::$idField],
				static::$dateField=>$occur_date];
	}
	
	public static function findManyWithConfig($updatedIds)
	{
		$tableName = static ::getTableName();
		$euPhaseConfig = EuPhaseConfig::getTableName();
		$result = static::join($euPhaseConfig, function ($query) use ($tableName,$euPhaseConfig) {
													$query->on("$euPhaseConfig.EU_ID",'=', "$tableName.EU_ID")
															->on("$euPhaseConfig.FLOW_PHASE",'=', "$tableName.FLOW_PHASE");
											})
						->whereIn("$tableName.ID",$updatedIds)
						->select(
								"$euPhaseConfig.ID as ".config("constants.euPhaseConfigId"),
								"$tableName.*")
						->get();
		return $result;
	}
	
	public static function updateWithFormularedValues($values,$object_id,$occur_date,$flow_phase) {
	
		$newData = [static::$idField=>$object_id,config("constants.euFlowPhase")=>$flow_phase];
		$attributes = static::getKeyColumns($newData,$occur_date,null);
	
		return parent::updateOrCreate($attributes,$values);;
	}
}
