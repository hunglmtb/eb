<?php 
namespace App\Models; 
use App\Models\FeatureTankModel; 

 class TankDataValue extends FeatureTankModel 
{ 
	protected $table = 'TANK_DATA_VALUE';
	
	protected $primaryKey = 'ID';
	protected $fillable  = ['TANK_LOCATION',
							'BEGIN_LEVEL',
							'END_LEVEL',
							'BEGIN_VOL',
							'END_VOL',
							'BEGIN_LEVEL2',
							'END_LEVEL2',
							'BEGIN_VOL2',
							'END_VOL2',
							'AVAIL_SHIPPING_VOL',
							'SW',
							'TANK_GRS_VOL',
							'TANK_NET_VOL',
							'TANK_WTR_VOL',
							'TANK_CORR_VOL',
							'TANK_DENSITY',
							'TANK_GRS_MASS',
							'TANK_NET_MASS',
							'STATUS_BY',
							'STATUS_DATE',
							'RECORD_STATUS'];
} 
