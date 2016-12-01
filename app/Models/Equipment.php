<?php

namespace App\Models;
use App\Models\DynamicModel;

class Equipment extends DynamicModel
{
	protected $table = 'EQUIPMENT';
	
	public static function getEntries($facility_id=null,$product_type = 0){
		if ($facility_id&&$facility_id>0) {
			$wheres = ['FACILITY_ID'=>$facility_id];
		}
		else $wheres = [];		
		if ($product_type>0) $wheres['PHASE_ID'] = $product_type;
		$entries = static ::where($wheres)->select('ID','NAME')->get();
		return $entries;
	}
}
