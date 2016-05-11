<?php 
namespace App\Models; 
use App\Models\DynamicModel; 
use App\Trail\LinkingTankModel;

 class TankDataAlloc extends DynamicModel 
{ 
	use LinkingTankModel;
	protected $table = 'tank_data_alloc'; 
} 
