<?php

namespace App\Models;
use App\Models\DynamicModel;

class Flow extends DynamicModel
{
	protected $table = 'FLOW';
	protected $primaryKey = 'ID';
	
	
	public function CodeFlowPhase()
	{
		return $this->belongsTo('App\Models\CodeFlowPhase', 'PHASE_ID', $this->primaryKey);
	}
	
	
	public static function getEntries($facility_id=null,$product_type = 0){
		$wheres = ['FACILITY_ID'=>$facility_id];
		if ($product_type>0) {
			$wheres['PHASE_ID'] = $product_type;
		}
		$entries = static ::where($wheres)->select('ID','NAME')->get();
		return $entries;
	}
}
