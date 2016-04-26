<?php

namespace App\Models;
use App\Models\EbBussinessModel;

class FeatureFlowModel extends EbBussinessModel
{
	public  static  $idField = 'FLOW_ID';
	public  static  $typeName = 'FLOW';
	public  static  $dateField = 'OCCUR_DATE';
	
	public static function getKeyColumns($newData,$occur_date,$postData)
	{
		if (array_key_exists(config("constants.flowId"), $newData)) {
			$newData[static::$idField] = $newData[config("constants.flowId")];
			unset($newData[config("constants.flowId")]);
		}
		return [static::$idField => $newData[static::$idField],
				static::$dateField=>$occur_date];
	}
	
	public static function updateWithFormularedValues($values,$object_id,$occur_date,$flow_phase) {
		
		$attributes = static::getKeyColumns([static::$idField=>$object_id],$occur_date,null);
		/* $updateRecords = static ::where('OCCUR_DATE',$occur_date)
								 ->where(static ::$idField,$formulas->OBJECT_ID)
								 ->update($values);
		 
		if ($updateRecords>0) {
			 return static::where('OCCUR_DATE',$occur_date)
						 	->where(static ::$idField,$formulas->OBJECT_ID)
							->select('ID')
							->first()->ID;
		 }; */
		
		return parent::updateOrCreate($attributes,$values);;
	}
}
