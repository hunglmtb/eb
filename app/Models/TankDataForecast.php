<?php 
namespace App\Models; 
use App\Models\FeatureTankModel; 
use App\Trail\LinkingTankModel;

 class TankDataForecast extends FeatureTankModel 
{ 
	use LinkingTankModel;
	protected $table = 'tank_data_forecast'; 
	
	protected $fillable  = ['OCCUR_DATE',
							 'TANK_ID',
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
							 'TANK_DENSITY',
							 'TANK_GRS_MASS',
							 'TANK_NET_MASS',
							 'TANK_GRS_ENGY',
							 'TANK_GRS_PWR'];
} 
