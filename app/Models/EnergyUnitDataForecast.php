<?php

namespace App\Models;
use App\Models\FeatureEuModel;

class EnergyUnitDataForecast extends FeatureEuModel
{
	protected $table = 'ENERGY_UNIT_DATA_FORECAST';
	
	protected $primaryKey = 'ID';
	protected $fillable  = ['OCCUR_DATE', 
							'EU_ID', 
							'EVENT_TYPE', 
							'FORECAST_TYPE', 
							'ALLOC_TYPE', 
							'FLOW_PHASE', 
							'ACTIVE_HRS', 
							'EU_DATA_GRS_VOL', 
							'EU_DATA_GRS_MASS', 
							'EU_DATA_GRS_ENGY', 
							'EU_DATA_GRS_PWR', 
							'STATUS_BY', 
							'STATUS_DATE', 
							'RECORD_STATUS',
							'EU_DATA_NET_MASS'
	];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		$keyFields		= parent::getKeyColumns($newData,$occur_date,$postData);
		$forecastType 	= array_key_exists('CodeForecastType', $postData)?$postData['CodeForecastType']:0;
		if ($forecastType>0) {
			$newData["FORECAST_TYPE"] 	= $forecastType;
			$keyFields["FORECAST_TYPE"] = $forecastType;
		}
		return $keyFields;
	}
}
