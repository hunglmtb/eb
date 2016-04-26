<?php

namespace App\Models;
use App\Models\EuTestDataValue;
use App\Models\FeatureEuModel;
use App\Trail\QltyDataConstrain;

class EnergyUnitDataTheor extends FeatureEuModel{
	
	use QltyDataConstrain;
	
	protected $table = 'ENERGY_UNIT_DATA_THEOR';
	
	protected $primaryKey = 'ID';
	protected $fillable  = ['OCCUR_DATE',
							'EU_ID',
							'EVENT_TYPE',
							'FLOW_PHASE',
							'ACTIVE_HRS',
							'EU_DATA_GRS_VOL',
							'EU_DATA_GRS_MASS',
							'EU_DATA_GRS_ENGY',
							'EU_DATA_GRS_PWR',
							'STATUS_BY',
							'STATUS_DATE',
							'RECORD_STATUS'];
	
	public static function calculateBeforeUpdateOrCreate(array $attributes, array $values = []){
	
		if(array_key_exists(config("constants.flowPhase"), $values)
				&&array_key_exists(config("constants.euIdColumn"),$attributes)
				&&array_key_exists("OCCUR_DATE",$attributes))//OIL or GAS
		{
			$fdcValues = EnergyUnitDataFdcValue::where($attributes)->first();
			$active_hrs=$fdcValues["ACTIVE_HRS"];
			
			$flow_phase = $attributes[config("constants.flowPhase")];
			$object_id = $attributes[config("constants.euIdColumn")];
			$occur_date = $attributes['OCCUR_DATE'];
 			$rowTest=EuTestDataValue::where([['EU_ID',$object_id],
 												['TEST_USAGE',1]])
				    				->whereDate('EFFECTIVE_DATE', '<=', $occur_date)
				    				->orderBy('EFFECTIVE_DATE')
 									->first();
				
			$fields = ["EU_DATA_GRS_VOL","EU_DATA_NET_VOL",
						config("constants.keyField") 	=>	'EU_ID'];
			
			$_v="";
			$theoFields = CfgFieldProps::getConfigFields( static::getTableName())->get();
			$theoFieldArray =array_column($theoFields->toArray(), 'COLUMN_NAME');
			
			
			if($rowTest && is_numeric($active_hrs))
			{
				$rat=$active_hrs/24;
				foreach($theoFieldArray as $field ){
					if($flow_phase==1) //oil
					{
						if($field=='EU_DATA_GRS_VOL') $_v=$rat*$rowTest->EU_TEST_LIQ_HC_VOL;
						if($field=='EU_DATA_GRS_MASS') $_v=$rat*$rowTest->EU_TEST_LIQ_HC_MASS;
					}
					else if($flow_phase==2) //gas
					{
						if($field=='EU_DATA_GRS_VOL') $_v=$rat*$rowTest->EU_TEST_GAS_HC_VOL;
						if($field=='EU_DATA_GRS_MASS') $_v=$rat*$rowTest->EU_TEST_GAS_HC_MASS;
					}
					else if($flow_phase==21) //gas lift
					{
						if($field=='EU_DATA_GRS_VOL') $_v=$rat*$rowTest->EU_TEST_GAS_LIFT_VOL;
					}
					else if($flow_phase==5) //Condensate
					{
						if($field=='EU_DATA_GRS_VOL') $_v=$rat*$rowTest->EU_TEST_LIQ_1_VOL;
					}
					else if($flow_phase==3) //water
					{
						if($field=='EU_DATA_GRS_VOL') $_v=$rat*$rowTest->EU_TEST_WTR_VOL;
						if($field=='EU_DATA_GRS_MASS') $_v=$rat*$rowTest->EU_WTR_MASS;
					}
					$values[$field] = $_v;
				}
			}
		}
		return $values; 
	}
}
