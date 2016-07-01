<?php

namespace App\Models;
use App\Models\DynamicModel;
use App\Trail\ObjectNameLoad;

class IntObjectType extends DynamicModel
{
	use ObjectNameLoad;
	
	protected $table = 'INT_OBJECT_TYPE';
	protected $primaryKey = 'ID';
	
	public static function getPreosObjectType(){
		$entries = static ::whereIn('CODE',['FLOW','ENERGY_UNIT','TANK','STORAGE'])->get();
		return $entries;
	}
	
}
