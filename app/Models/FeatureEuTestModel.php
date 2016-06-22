<?php

namespace App\Models;
use App\Models\EbBussinessModel;
use Carbon\Carbon;

class FeatureEuTestModel extends EbBussinessModel
{
	public  static  $idField = 'ID';
	public  static  $typeName = 'EUTEST';
	public  static  $dateField = 'EFFECTIVE_DATE';
	protected $dates = ['EFFECTIVE_DATE','END_TIME','BEGIN_TIME'];
	
	/* protected $objectModel = 'EuTest';
	protected $excludeColumns = ['EU_ID','OCCUR_DATE']; */ 
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		$attributes = [];
		$attributes['EFFECTIVE_DATE'] = $newData['EFFECTIVE_DATE'];
		if ( array_key_exists ( 'isAdding', $newData ) && array_key_exists ( 'EnergyUnit', $postData )) {
			$newData['EU_ID'] = $postData['EnergyUnit'];
		}
		else $attributes['EU_ID'] = $newData['EU_ID'];
		return $attributes;
	}
	
	public function  getFdcValues($attributes){
		return null;
	}
	
	public function afterSaving($postData) {
		$occur_date = $this->EFFECTIVE_DATE;
		if(!$occur_date) return;
		$object_id	=$this->EU_ID;
		$attributes = [	'EFFECTIVE_DATE'	=>	$occur_date,
						'EU_ID'				=>	$object_id,
		];
		$sourceEntry = $this->getFdcValues($attributes);
		if ($sourceEntry) {
			$start_time = $sourceEntry->BEGIN_TIME;
			$end_time = $sourceEntry->END_TIME;
			$hours = $start_time->diffInSeconds($end_time) / Carbon::SECONDS_PER_MINUTE / Carbon::MINUTES_PER_HOUR;
			if($hours<=0) throw new DataInputException ( "Wrong STD duration (less than or equal zero)" );
			$rat = 24/$hours;
		
			if ($this->isAuto) {
				$commonFields = array_intersect_key($sourceEntry->fillable, $this->fillable);
				foreach ($commonFields as $field){
					$this->$field	= 	$sourceEntry->$field;
				}
				$this->updateValuesFromSourceEntry($object_id, $occur_date, $sourceEntry,$rat);
			}
		}
	}
	
	public function updateValuesFromSourceEntry($object_id, $occur_date, $sourceEntry,$rat) {
	}
}
