<?php

namespace App\Models;
use App\Models\DynamicModel;

class FeatureFlowModel extends DynamicModel
{
	public  static  $idField = 'FLOW_ID';
	public  static  $typeName = 'FLOW';
	public  static  $dateField = 'OCCUR_DATE';
	
	public static function getKeyColumns($newData,$occur_date,$postData)
	{
		return [self::$idField => $newData[self::$idField],
				self::$dateField=>$occur_date];
	}
	
	public static function findManyWithConfig($updatedIds)
	{
		return parent::findMany($updatedIds);
	}
	
}
