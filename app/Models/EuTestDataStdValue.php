<?php 
namespace App\Models; 
use App\Models\FeatureEuTestModel; 
use App\Models\EuTestDataFdcValue; 
use App\Exceptions\DataInputException;

 class EuTestDataStdValue extends FeatureEuTestModel 
{ 
	protected $table = 'EU_TEST_DATA_STD_VALUE'; 
	
	protected $primaryKey = 'ID';
	
	public  $fillable  = ['EU_ID', 
							'BEGIN_TIME', 
							'END_TIME', 
							'TEST_HRS', 
							'EFFECTIVE_DATE', 
							'TEST_METHOD', 
							'TEST_DEVICE_ID', 
							'TEST_USAGE', 
							'EVENT_TYPE', 
							'REFERENCE_ID', 
							'EU_TEST_TOTAL_LIQ_VOL', 
							'EU_TEST_LIQ_HC_VOL', 
							'EU_TEST_WTR_VOL', 
							'EU_TEST_TOTAL_GAS_VOL', 
							'EU_TEST_GAS_HC_VOL', 
							'EU_TEST_GAS_LIFT_VOL', 
							'EU_TEST_GAS_LIFT_ENGY', 
							'GOR', 
							'WATER_CUT', 
							'EU_TEST_TOTAL_LIQ_MASS', 
							'EU_TEST_LIQ_HC_MASS', 
							'EU_WTR_MASS', 
							'EU_TEST_TOTAL_GAS_MASS', 
							'EU_TEST_GAS_HC_MASS', 
							'EU_TEST_SEPARATOR_TEMP', 
							'EU_TEST_SEPARATOR_PRESS', 
							'EU_TEST_PUMP_RATE', 
							'EU_TEST_INJECT_RATE', 
							'EU_TEST_ENGY_QTY', 
							'EU_TEST_POWR_QTY', 
							'EU_TEST_LIQ_1_VOL', 
							'EU_TEST_LIQ_2_VOL', 
							'EU_TEST_LIQ_3_VOL', 
							'EU_TEST_LIQ_1_MASS', 
							'EU_TEST_LIQ_2_MASS', 
							'EU_TEST_LIQ_3_MASS'];
	
 
	public function  getFdcValues($attributes){
		$fdcValues = EuTestDataFdcValue::where($attributes)->first();
		return $fdcValues;
	}
	
	public function updateValuesFromSourceEntry($object_id, $occur_date, $sourceEntry,$rat) {
		
		/* $T_obs = $_REQUEST["FDC_OBS_TEMP$x_id"];
		$P_obs = $_REQUEST["FDC_OBS_PRESS$x_id"];
		$API_obs = $_REQUEST["FDC_OBS_API$x_id"];
		$_Bg1=calculateBg(1,$T_obs,$P_obs,$API_obs,$occur_date,$object_id,'ENERGY_UNIT');
		$_Bg2=calculateBg(2,$T_obs,$P_obs,$API_obs,$occur_date,$object_id,'ENERGY_UNIT');
		
		$T_GL = $_REQUEST["FDC_GASLIFT_TEMP$x_id"];
		$P_GL = $_REQUEST["FDC_GASLIFT_PRESS$x_id"];
		$_BgGL=calculateBg(2,$T_GL,$P_GL,0,$occur_date,$object_id,'ENERGY_UNIT'); */
		
		$T_obs = $sourceEntry ["OBS_TEMP"];
		$P_obs = $sourceEntry ["OBS_PRESS"];
		$API_obs = $sourceEntry ["OBS_API"];
		$_Bg1 = \FormulaHelpers::calculateBg ( 1, $T_obs, $P_obs, $API_obs, $occur_date, $object_id, 'ENERGY_UNIT' );
		$_Bg2 = \FormulaHelpers::calculateBg ( 2, $T_obs, $P_obs, $API_obs, $occur_date, $object_id, 'ENERGY_UNIT' );
		
		$T_GL = $sourceEntry["GASLIFT_TEMP"];
		$P_GL = $sourceEntry["GASLIFT_PRESS"];
		$_BgGL = \FormulaHelpers::calculateBg ( 2, $T_GL, $P_GL, 0, $occur_date, $object_id, 'ENERGY_UNIT' );
		
		$this->EU_TEST_LIQ_HC_VOL= $_Bg1!=null?$sourceEntry->EU_TEST_LIQ_HC_VOL*$_Bg1:null;
		
		if($_Bg2==0) throw new DataInputException ( "Wrong gas conversion number (zero) for ENERGY UNIT ID: $object_id phase 2" );
		$this->EU_TEST_GAS_HC_VOL	= 	$_Bg2!=null?$sourceEntry->EU_TEST_GAS_HC_VOL/$_Bg2:null;
		
		if($_BgGL==0) throw new DataInputException ( "Wrong GAS_LIFT conversion number (zero) for ENERGY UNIT ID: $object_id" );
		$this->EU_TEST_GAS_LIFT_VOL	= 	$_BgGL!=null?$sourceEntry->EU_TEST_GAS_LIFT_VOL/$_BgGL:null;
		
		$this->save();
	}
} 
