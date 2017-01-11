<?php

namespace App\Models;
use App\Models\FeatureEuModel;

class EnergyUnitDataPlan extends FeatureEuModel
{
	protected $table = 'ENERGY_UNIT_DATA_PLAN';
	
	protected $primaryKey = 'ID';
	protected $fillable  = ['OCCUR_DATE', 
							'EU_ID', 
							'EVENT_TYPE', 
							'PLAN_TYPE', 
							'FLOW_PHASE', 
							'ACTIVE_HRS', 
							'EU_DATA_GRS_VOL', 
							'EU_DATA_GRS_MASS', 
							'EU_DATA_GRS_ENGY', 
							'EU_DATA_GRS_PWR', 
							'RECORD_STATUS', 
							'STATUS_BY', 
							'STATUS_DATE',
							'EU_DATA_NET_MASS'
	];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		$keyFields		= parent::getKeyColumns($newData,$occur_date,$postData);
		$planType 		= array_key_exists('CodePlanType', $postData)?$postData['CodePlanType']:0;
		if ($planType>0) {
			$newData["PLAN_TYPE"] 	= $planType;
			$keyFields["PLAN_TYPE"] = $planType;
		}
		return $keyFields;
	}
}
