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
	protected $dates = ['LAST_DATA_READ','OCCUR_DATE','STATUS_DATE'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData)
	{
		if (array_key_exists(config("constants.flowId"), $newData)) {
			$newData[static::$idField] = $newData[config("constants.flowId")];
			unset($newData[config("constants.flowId")]);
		}
		if (array_key_exists(static::$dateField, $newData)) {
			$occur_date = $newData[static::$dateField];
		}
		else $newData[static::$dateField] = $occur_date;
		return [static::$idField => $newData[static::$idField],
				static::$dateField=>$occur_date];
	}
	
	public static function getObjects() {
		return Flow::where("ID",">",0)->orderBy("NAME")->get();
	}
	
}
