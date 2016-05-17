<?php

namespace App\Models;
use App\Models\EbBussinessModel;

class FeatureFlowModel extends EbBussinessModel
{
	public  static  $idField = 'FLOW_ID';
	public  static  $typeName = 'FLOW';
	public  static  $dateField = 'OCCUR_DATE';
	protected $objectModel = 'Flow';
	protected $excludeColumns = ['FLOW_ID','OCCUR_DATE'];
	protected $disableUpdateAudit = false;
	
	public static function getKeyColumns(&$newData,$occur_date,$postData)
	{
		if (array_key_exists(config("constants.flowId"), $newData)) {
			$newData[static::$idField] = $newData[config("constants.flowId")];
			unset($newData[config("constants.flowId")]);
		}
		$newData[static::$dateField] = $occur_date;
		return [static::$idField => $newData[static::$idField],
				static::$dateField=>$occur_date];
	}
}
