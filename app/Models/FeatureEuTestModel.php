<?php

namespace App\Models;
use App\Models\EbBussinessModel;

class FeatureEuTestModel extends EbBussinessModel
{
	public  static  $idField = 'ID';
	public  static  $typeName = 'EUTEST';
	public  static  $dateField = 'OCCUR_DATE';
	/* protected $objectModel = 'EuTest';
	protected $excludeColumns = ['EU_ID','OCCUR_DATE']; */ 
	
	public static function getKeyColumns(&$newData,$occur_date,$postData)
	{
		return [static::$idField => $newData[static::$idField]];
	}
}
