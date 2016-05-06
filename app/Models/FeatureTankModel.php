<?php

namespace App\Models;
use App\Models\EbBussinessModel;
use App\Models\EuPhaseConfig;

class FeatureTankModel extends EbBussinessModel
{
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
		return [static::$idField => $newData[static::$idField],
				static::$dateField=>$occur_date];
	}
	
	/* public static function updateWithFormularedValues($values,$object_id,$occur_date,$flow_phase) {
	
		$newData = [static::$idField=>$object_id,config("constants.euFlowPhase")=>$flow_phase];
		$attributes = static::getKeyColumns($newData,$occur_date,null);
	
		return parent::updateOrCreate($attributes,$values);;
	} */
}
