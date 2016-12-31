<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class MisMeasurement extends EbBussinessModel 
{ 
	protected $table = 'MIS_MEASUREMENT';
	protected $primaryKey = 'ID';
	protected $dates = ['END_TIME','BEGIN_TIME'];
	protected $fillable  = ['CODE', 
							'NAME', 
							'FACILITY_ID', 
							'BEGIN_TIME', 
							'END_TIME', 
							'DURATION', 
							'OBJECT_TYPE', 
							'OBJECT_ID', 
							'MMR_CLASS', 
							'MMR_REASON', 
							'MMR_ROOT_CAUSE', 
							'MMR_ACCUM_QTY_TODATE', 
							'MMR_CORRECT_TODATE', 
							'MMR_ACCUM_DIFF', 
							'MMR_ACCUM_QTY_TOTAL', 
							'MMR_CORRECT_TOTAL', 
							'MMR_TOTAL_DIFF', 
							'MMR_QTY_UOM', 
							'MMR_CALC_METHOD_FORMULA', 
							'MMR_ORIGINATED_ID', 
							'MMR_STATUS', 
							'MMR_APPROVED_ID', 
							'MMR_COMMENT', 
							'STATUS_BY', 
							'STATUS_DATE', 
							'RECORD_STATUS'];
	
	public  static  $idField = 'ID';
// 	public  static  $unguarded = true;

	public function afterSaving($postData) {
		//Tinh toan lai cac gia tri THEOR, GAS
		$shouldSave = false;
		$hours =  $this->DURATION;
		$rat=$hours/24;
	/*
		if($this->DEFER_GROUP_TYPE==2){ //Well
			$eu_id=$this->OBJECT_ID;
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
	*/
		if ($this->wasRecentlyCreated) {
			$this->FACILITY_ID = $postData['Facility'];
			$shouldSave = true;
		}
		if ($shouldSave) $this->save();
	}
} 
