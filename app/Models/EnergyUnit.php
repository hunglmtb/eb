<?php

namespace App\Models;
use App\Models\DynamicModel;

class EnergyUnit extends DynamicModel
{

	protected $table = 'ENERGY_UNIT';
	protected $primaryKey = 'ID';
	
	
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
		
		$wheres = ['FACILITY_ID'=>$facility_id];
		$query = static ::where($wheres)->select('ID','NAME');
		
		if ($product_type>0) {
			//TODO update euphase configs
// 			$sSQL="select ID,NAME from $table_name a where a.FACILITY_ID=$facility_id ".($product_type>0?" and exists(select 1 from eu_phase_config b where b.EU_ID=a.ID and b.FLOW_PHASE=$product_type)":"")."";
// 			$query->whereHas('EuPhaseConfig');
// 			$wheres['PHASE_ID'] = $product_type;
		}
		$entries = $query->get();
		return $entries;
	}
}
