<?php

namespace App\Models;
use App\Models\EbBussinessModel;
use App\Models\EuPhaseConfig;

class FeatureEuModel extends EbBussinessModel
{
	public  static  $idField = 'EU_ID';
	public  static  $typeName = 'ENERGY_UNIT';
	public  static  $dateField = 'OCCUR_DATE';
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if (array_key_exists(config("constants.euId"), $newData)) {
			$newData[static::$idField] = $newData[config("constants.euId")];
			unset($newData[config("constants.euId")]);
		}
		return [static::$idField => $newData[static::$idField],
				config("constants.flowPhase") => $newData[config("constants.euFlowPhase")],
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
}
