<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class Deferment extends EbBussinessModel 
{ 
	protected $table 	= 'DEFERMENT';
	protected $dates 	= ['END_TIME','BEGIN_TIME'];
	protected $fillable = ['CODE', 
							'NAME', 
							'FACILITY_ID', 
							'EU_ID', 
							'EVENT_NO', 
							'BEGIN_TIME', 
							'END_TIME', 
							'DURATION', 
							'DEFER_GROUP_TYPE', 
							'DEFER_TARGET', 
							'PLANNED', 
							'CODE1', 
							'CODE2', 
							'CODE3', 
							'DEFER_REASON', 
							'DEFER_STATUS', 
							'DEFER_CATEGORY', 
							'THEOR_OIL_PERDAY', 
							'THEOR_GAS_PERDAY', 
							'THEOR_WATER_PERDAY', 
							'CALC_DEFER_OIL_VOL', 
							'CALC_DEFER_GAS_VOL', 
							'CALC_DEFER_WATER_VOL', 
							'OVR_DEFER_OIL_VOL', 
							'OVR_DEFER_GAS_VOL', 
							'OVR_DEFER_WATER_VOL', 
							'COMMENT'];
	
	public  static  $idField = 'ID';
// 	public  static  $unguarded = true;
	public  static  $dateField = 'BEGIN_TIME';
	
	public static function getObjectTypeCode() {
		return "DEFER_TARGET";
	}
	public static function addExtraQueryCondition(&$where,$object,$objectType){
		$where['DEFER_GROUP_TYPE'] = $objectType;
	}
	
	public function afterSaving($postData) {
		//Tinh toan lai cac gia tri THEOR, GAS
		$shouldSave = false;
		$hours =  $this->DURATION;
		$rat=$hours/24;
	
		if($this->DEFER_GROUP_TYPE==3){
			$eu_id=$this->DEFER_TARGET;
			$rowTest = static ::getEUTest($eu_id,$this->BEGIN_TIME);
			//-----------THEOR------------
			if($rowTest){
				$this->THEOR_OIL_PERDAY			=$rowTest->EU_TEST_LIQ_HC_VOL;
				$this->THEOR_GAS_PERDAY			=$rowTest->EU_TEST_GAS_HC_VOL;
				$this->THEOR_WATER_PERDAY		=$rowTest->EU_WTR_VOL;
				//-----------CALC------------
				$this->CALC_DEFER_OIL_VOL		=$rat*$rowTest->EU_TEST_LIQ_HC_VOL;
				$this->CALC_DEFER_GAS_VOL		=$rat*$rowTest->EU_TEST_GAS_HC_VOL;
				$this->CALC_DEFER_WATER_VOL		=$rat*$rowTest->EU_WTR_VOL;
				$shouldSave = true;
			}
		}
		else{
			//TODO update later
		}
		if ($this->wasRecentlyCreated) {
			$this->FACILITY_ID = $postData['Facility'];
			$shouldSave = true;
		}
		if ($shouldSave) $this->save();
	}
	
	public static function buildLoadQuery($objectId,$object) {
		return static::where(["DEFER_TARGET"	=> $objectId,"DEFER_GROUP_TYPE"	=> 3]);//3 is well
	}
} 
