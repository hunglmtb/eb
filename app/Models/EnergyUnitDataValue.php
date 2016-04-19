<?php

namespace App\Models;
use App\Models\DynamicModel;
use App\Models\FlowDataFdcValue;

class EnergyUnitDataValue extends DynamicModel
{
	protected $table = 'ENERGY_UNIT_DATA_VALUE';
	protected $primaryKey = 'ID';
	protected $fillable  = ['EU_ID',
							'OCCUR_DATE',
							'ACTIVE_HRS',
							'RECORD_FREQUENCY',
							'DISP',
							'FL_DATA_GRS_VOL',
							'FL_DATA_NET_VOL',
							'FL_DATA_SW_PCT',
							'FL_DATA_GRS_WTR_VOL',
							'FL_DATA_GRS_MASS',
							'FL_DATA_NET_MASS',
							'FL_DATA_GRS_WTR_MASS',
							'FL_DATA_GRS_ENGY',
							'FL_DATA_GRS_PWR',
							'FL_DATA_DENS',
							'STATUS_BY',
							'STATUS_DATE',
							'RECORD_STATUS' ];
	
	/* public function getDataMethodAttribute($value){
		$rv = $value;
		$user = auth()->user();
		switch ($rv) {
			case 'A':
				$rv = $user->hasRight(["ADMIN_APPROVE"])?$rv:0;
				break;
			case 'V':
				$rv = $user->hasRight(["ADMIN_APPROVE","ADMIN_VALIDATE"])?$rv:0;
				break;
			default:
			break;
		}
		return $rv;
	} */
	
	public static function calculateBeforeUpdateOrCreate(array $attributes, array $values = [],$options=null){

		if($options&&count($options)>0
				&&array_key_exists(config("constants.flowPhase"), $options)
				&&array_key_exists("FLOW_ID",$attributes)
				&&array_key_exists("OCCUR_DATE",$attributes))//OIL or GAS
		{
			if (array_key_exists("FL_DATA_GRS_VOL",$values)
					&&$values["FL_DATA_GRS_VOL"]!=null
					&&$values["FL_DATA_GRS_VOL"]!=''
					&&array_key_exists("FL_DATA_NET_VOL",$values)
					&&$values["FL_DATA_NET_VOL"]!=null
					&&$values["FL_DATA_NET_VOL"]!='') return;
					
			$flow_phase = $options[config("constants.flowPhase")];
			//OIL or GAS
			if(($flow_phase==1 || $flow_phase==2 || $flow_phase==21)){
				$object_id = $attributes["FLOW_ID"];
				$occur_date = $attributes["OCCUR_DATE"];
				
				$fdcValues = FlowDataFdcValue::where(array(['FLOW_ID',$object_id],
															['OCCUR_DATE',$occur_date]))
// 												->select(['OBS_TEMP','OBS_PRESS','OBS_API'])
												->first();
				
				$T_obs = $fdcValues["OBS_TEMP"];
				$P_obs = $fdcValues["OBS_PRESS"];
				$API_obs = $fdcValues["OBS_API"];
				
				$_Bg=\FormulaHelpers::calculateBg($flow_phase,$T_obs,$P_obs,$API_obs,$occur_date,$object_id,'FLOW');
				
				$fields = ["FL_DATA_GRS_VOL","FL_DATA_NET_VOL"];
				foreach ($fields as $field){
	// 				if($ctv==1){
					if (array_key_exists($field,$values)) {
						break;
					}
					$_vFDC  = $fdcValues->$field;
					$values[$field] = $_vFDC;
					switch ($flow_phase) {
						case 1:
							$_v=null;
							if($_vFDC && $_Bg!=null) $_v=$_vFDC*$_Bg;
							$values[$field] = $_v;
							break;
						case 2:
						case 21:
							if($_Bg==null){
								 $values[$field] = null;
							}
							else {
								if($_Bg==0){
									if((($values[$field]!=null&&$values[$field]!=''))){
										throw new Exception("Wrong gas conversion number (zero) for FLOW ID: $object_id");
									}
								}
								else{
									$values[$field] = $values[$field]/$_Bg;
								}
							}
							break;
						default:
						break;
					}
				}
			}
		}
		return $values; 
	}
}
