<?php 
namespace App\Models; 
use App\Models\FeatureTankModel; 

 class TankDataFdcValue extends FeatureTankModel 
{ 
	protected $table = 'TANK_DATA_FDC_VALUE'; 
	protected $dates = ['LAST_DATA_READ'];
	protected $primaryKey = 'ID';
	protected $fillable  = ['OCCUR_DATE',
							 'LAST_DATA_READ',
							 'TANK_ID',
							 'TANK_LOCATION',
							 'OBS_API',
							 'OBS_TEMP',
							 'OBS_PRESS',
							 'BEGIN_LEVEL',
							 'END_LEVEL',
							 'BEGIN_VOL',
							 'END_VOL',
							 'BEGIN_LEVEL2',
							 'END_LEVEL2',
							 'BEGIN_VOL2',
							 'END_VOL2',
							 'SW',
							 'TANK_GRS_VOL',
							 'TANK_NET_VOL',
							 'TANK_WTR_VOL',
							 'TANK_DENSITY',
							 'TANK_GRS_MASS',
							 'TANK_NET_MASS',
							 'VOL_UOM',
							 'MASS_UOM',
							 'CTV',
							 'TEMP_UOM',
							 'PRESS_UOM',
							 'STATUS_BY',
							 'STATUS_DATE',
							 'RECORD_STATUS'];
	
	
	public static function getCalculateFields() {
		return  [
				config("constants.extraFields") 	=>	["END_VOL"=>"BEGIN_VOL","END_LEVEL"=>"BEGIN_LEVEL"],
				config("constants.keyField") 		=>	'TANK_ID'
		];;
	}
} 
