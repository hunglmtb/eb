<?php

namespace App\Models;
use App\Models\DynamicModel;
use App\Models\EuPhaseConfig;

class EnergyUnit extends DynamicModel
{

	protected $table = 'ENERGY_UNIT';
	protected $primaryKey = 'ID';
	public  static  $idField = 'ID';
	
	
	public function EuPhaseConfig()
	{
		return $this->hasMany('App\Models\EuPhaseConfig', 'EU_ID', $this->primaryKey);
	}
	
	public function CodeStatus()
	{
		return $this->belongsTo('App\Models\CodeStatus', 'STATUS', $this->primaryKey);
	}
	
	public function Facility()
	{
		return $this->belongsTo('App\Models\Facility', 'FACILITY_ID', 'ID');
	}
	
	public static function getEntries($facility_id=null,$product_type = 0){
		if ($facility_id&&$facility_id>0) {
			$wheres = ['FACILITY_ID'=>$facility_id];
		}
		else $wheres = [];
		
		$query = static ::where($wheres)->select('ID','NAME');
		
		if ($product_type>0) {
			$eu_phase_config = EuPhaseConfig::getTableName();
			$query->whereHas('EuPhaseConfig' , function ($query) use ($product_type){
				$query->where("FLOW_PHASE",$product_type);
			});
		}
		$entries = $query->get();
		return $entries;
	}
	
	public function CodeFlowPhase(){
		$EuPhaseConfig 	 = EuPhaseConfig::getTableName();
		$code_flow_phase = CodeFlowPhase::getTableName();
		$eu_id 			= $this->ID;
		$result 		=  CodeFlowPhase::whereHas("EuPhaseConfig",
							function ($query) use($EuPhaseConfig,$eu_id) {
								$query->where("$EuPhaseConfig.EU_ID",$eu_id);
							})
							->get(["$code_flow_phase.ID", "$code_flow_phase.NAME"] );
		return $result;
	}
}
