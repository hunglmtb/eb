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
		$attributes = parent:: getKeyColumns($newData,$occur_date,$postData);
		if ( array_key_exists ( 'isAdding', $newData ) && array_key_exists ( 'EnergyUnit', $postData )) {
			$newData['EU_ID'] = $postData['EnergyUnit'];
		}
		return $attributes;
	}
	
	public function afterSaving($postData) {
		$occur_date = $this->OCCUR_DATE;
		$object_id	=$this->EU_ID;
		$quality	=$this->getQualityOil($object_id,"WELL",$occur_date);
		
	}
}
