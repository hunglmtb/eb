<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class TankDayAlloc extends DynamicModel 
{ 
	protected $table = 'TANK_DAY_ALLOC';
	protected $primaryKey = 'ID';
	protected $fillable  = [
			'ID',
			'OCCUR_DATE',
			'TANK_ID',
			'BEGIN_LEVEL',
			'END_LEVEL',
			'BEGIN_VOL',
			'END_VOL',
			'SW',
			'TANK_GRS_VOL',
			'TANK_NET_VOL',
			'TANK_DENSITY',
			'TANK_GRS_MASS',
			'TANK_NET_MASS',
			'RECORD_STATUS',
			'STATUS_BY',
			'STATUS_DATE'
	];
} 
