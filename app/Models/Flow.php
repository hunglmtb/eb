<?php

namespace App\Models;

class Flow extends EbBussinessModel
{
	protected $table = 'FLOW';
	
	public  static  $idField = 'ID';
	
	public function CodeFlowPhase(){
		return $this->belongsTo('App\Models\CodeFlowPhase', 'PHASE_ID', $this->primaryKey);
	}
	
	
	public static function getEntries($facility_id=null,$product_type = 0){
		if ($facility_id&&$facility_id>0)$wheres = ['FACILITY_ID'=>$facility_id];
		else $wheres = [];
		
		if ($product_type>0) {
			$wheres['PHASE_ID'] = $product_type;
		}
		$entries = static ::where($wheres)->select('ID','NAME')->orderBy('NAME')->get();
		return $entries;
	}
	
	public function getKeyAttributes($mdlName,$column){
		return ["FLOW_ID"				=>	$this->ID,
// 				"RECORD_FREQUENCY"		=>	$this->RECORD_FREQUENCY,
				];
	}
}
