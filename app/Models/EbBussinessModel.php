<?php

namespace App\Models;

use App\Models\DynamicModel;

class EbBussinessModel extends DynamicModel {
	
	public static function findManyWithConfig($updatedIds) {
		return parent::findMany ( $updatedIds );
	}
	
	protected static $enableCheckCondition = false;
	
	public static function updateValues(array $attributes, array &$values = [], $type, $fields) {
		$unnecessary = true;
		foreach ( $fields as $field ) {
			$unnecessary = $unnecessary&&array_key_exists($field,$values)&&$values[$field]!=null&&$values[$field]!='';
		}
		
		if ($unnecessary) return;
					
		$flow_phase = $values[config("constants.flFlowPhase")];
		//OIL or GAS
		if(($flow_phase==1 || $flow_phase==2 || $flow_phase==21)){
			$object_id = $attributes[$fields[config("constants.keyField")]];
			$occur_date = $attributes["OCCUR_DATE"];
			
			$fdcValues = static :: getFdcValues($attributes);
			$T_obs = $fdcValues ["OBS_TEMP"];
			$P_obs = $fdcValues ["OBS_PRESS"];
			$API_obs = $fdcValues ["OBS_API"];
			
			$_Bg = \FormulaHelpers::calculateBg ( $flow_phase, $T_obs, $P_obs, $API_obs, $occur_date, $object_id, $type );
			
			foreach ( $fields as $field ) {
				if (config("constants.keyField")==$field) {
					continue;
				}
				// if($ctv==1){
				if (array_key_exists ( $field, $values )) {
					break;
				}
				$_vFDC = $fdcValues->$field;
				if (static ::$enableCheckCondition && $_Bg==null && $_vFDC != '') {
					throw new Exception ( "Can not calculate conversion for ENERGY UNIT ID: $object_id (check API, Temprature, Pressure value)");
					return;
				}
				$values [$field] = $_vFDC;
				switch ($flow_phase) {
					case 1 :
						$_v = null;
						if ($_vFDC && $_Bg != null)
							$_v = $_vFDC * $_Bg;
						$values [$field] = $_v;
						break;
					case 2 :
					case 21 :
						if ($_Bg == null) {
							$values [$field] = null;
						} else {
							if ($_Bg == 0) {
								if ((($values [$field] != null && $values [$field] != ''))) {
									throw new Exception ( "Wrong gas conversion number (zero) for $type ID: $object_id" );
								}
							} else {
								$values [$field] = $values [$field] / $_Bg;
							}
						}
						break;
					default :
						break;
				}
			}
	}
	}
}
