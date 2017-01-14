<?php

namespace App\Models;
use App\Models\FeatureFlowModel;
use App\Trail\ForecastModel;

class FlowDataForecast extends FeatureFlowModel
{
	use ForecastModel;
	protected $table 		= 'FLOW_DATA_FORECAST';
	protected $fillable  	= 	['FLOW_ID',
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
								'RECORD_STATUS',
								'FORECAST_TYPE'];
	
}
