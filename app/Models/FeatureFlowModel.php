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
		return [self::$idField => $newData[self::$idField],
				self::$dateField=>$occur_date];
	}
	
}
