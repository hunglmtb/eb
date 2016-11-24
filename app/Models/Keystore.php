<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class Keystore extends DynamicModel 
{ 
	protected $table = 'KEYSTORE';
	
	public static function getEntries($facility_id=null,$product_type = 0){
	
		/* $wheres = ['FACILITY_ID'=>$facility_id];
		$query = static ::where($wheres)->select('ID','NAME');
	
		if ($product_type>0) {
			$eu_phase_config = EuPhaseConfig::getTableName();
			$query->whereHas('EuPhaseConfig' , function ($query) use ($product_type){
				$query->where("FLOW_PHASE",$product_type);
			});
		}
		$entries = $query->get(); */
		return [];
	}
} 
