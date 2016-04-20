<?php

namespace App\Models;
use App\Models\DynamicModel;
use App\Models\EuPhaseConfig;

class FeatureEuModel extends DynamicModel
{
	public  static  $idField = 'EU_ID';
	public  static  $typeName = 'ENERGY_UNIT';
	public  static  $dateField = 'OCCUR_DATE';
	
	public static function getKeyColumns($newData,$occur_date,$postData)
	{
		return [self::$idField => $newData[self::$idField],
				'FLOW_PHASE' => $newData[config("constants.euFlowPhase")],
				self::$dateField=>$occur_date];
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
