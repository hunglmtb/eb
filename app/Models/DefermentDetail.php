<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class DefermentDetail extends DynamicModel 
{ 
	protected $table = 'DEFERMENT_DETAIL'; 
	protected $primaryKey = 'ID';
// 	protected $dates = ['END_TIME','BEGIN_TIME'];
	protected $fillable  = ['DEFERMENT_ID',
							 'EU_ID',
							 'THEOR_OIL_PERDAY',
							 'THEOR_GAS_PERDAY',
							 'THEOR_WATER_PERDAY',
							 'CALC_DEFER_OIL_VOL',
							 'CALC_DEFER_GAS_VOL',
							 'CALC_DEFER_WATER_VOL',
							 'OVR_DEFER_OIL_VOL',
							 'OVR_DEFER_GAS_VOL',
							 'OVR_DEFER_WATER_VOL',
							 'COMMENT'];
} 
