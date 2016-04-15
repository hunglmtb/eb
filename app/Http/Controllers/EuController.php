<?php

namespace App\Http\Controllers;
use App\Models\CodeFlowPhase;
use App\Models\EnergyUnit;
use App\Models\CodeStatus;
use App\Models\EuPhaseConfig;


class EuController extends CodeController {
    
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date){
    	$eu_group_id = $postData['EnergyUnitGroup'];
    	$record_freq = $postData['CodeReadingFrequency'];
    	$phase_type = $postData['CodeFlowPhase'];
    	$event_type = $postData['CodeEventType'];
    	$alloc_type = array_key_exists('CodeAllocType', $postData)?$postData['CodeAllocType']:0;
    	 
    	$eu = EnergyUnit::getTableName();
    	$codeFlowPhase = CodeFlowPhase::getTableName();
    	$codeStatus = CodeStatus::getTableName();
    	$euPhaseConfig = EuPhaseConfig::getTableName();
    	
    	$euWheres = ['FACILITY_ID' => $facility_id, 'FDC_DISPLAY' => 1];
    	if ($record_freq>0) $euWheres["$eu.DATA_FREQ"]= $record_freq;
     	if ($eu_group_id>0) $euWheres["$eu.EU_GROUP_ID"]= $eu_group_id;
    	else $euWheres["$eu.EU_GROUP_ID"]= null;
    	
    	\DB::enableQueryLog();
    	$dataSet = EnergyUnit::join($codeStatus,'STATUS', '=', "$codeStatus.ID")
				    	->join($euPhaseConfig,function ($query) use ($eu,$euPhaseConfig,$phase_type,$event_type) {
						    					$query->on("$euPhaseConfig.EU_ID",'=',"$eu.ID");
										    	if ($phase_type>0) $query->where("$euPhaseConfig.FLOW_PHASE",'=',$phase_type) ;
										    	if ($event_type>0) $query->where("$euPhaseConfig.EVENT_TYPE",'=',$event_type) ;
		// 							    		$query->with('CodeFlowPhase');
		// 							    		$query->select('FLOW_PHASE as EU_FLOW_PHASE');
						}) 
						->join($codeFlowPhase,"$euPhaseConfig.FLOW_PHASE", '=', "$codeFlowPhase.ID")
    					->where($euWheres)
				    	->whereDate('EFFECTIVE_DATE', '<=', $occur_date)
				    	->leftJoin($dcTable, function($join) use ($eu,$dcTable,$occur_date,$alloc_type,$euPhaseConfig){
										    	//TODO add table name 
									    		$join->on("$eu.ID", '=', "$dcTable.EU_ID")
 					 				    			->on("$dcTable.FLOW_PHASE",'=',"$euPhaseConfig.FLOW_PHASE")
 									    			->where("$dcTable.OCCUR_DATE",'=',$occur_date);
									    		
										    	if (($alloc_type > 0 && 
										    			($dcTable == "ENERGY_UNIT_DATA_ALLOC" ||
										    					$dcTable == "ENERGY_UNIT_COMP_DATA_ALLOC"))) 
								    				$join->where("$dcTable.ALLOC_TYPE",'=',$alloc_type);
				    	})
// 				    	->with($withs)
				    	->select(
				    			"$eu.name as $dcTable",
				    			"$eu.ID as DT_RowId",
 				    			"$codeFlowPhase.name as PHASE_NAME",
				    			"$eu.ID as X_EU_ID",
   				    			"$euPhaseConfig.FLOW_PHASE as EU_FLOW_PHASE",
  				    			"$codeStatus.NAME as STATUS_NAME",
				    			"$codeFlowPhase.CODE as PHASE_CODE",
				    			"$dcTable.*"
				    			) 
 		    			->orderBy($dcTable)
  		    			->orderBy('EU_FLOW_PHASE')
  		    			->get();
  		    			/* ->skip(0)->take(5)->get(["$eu.name as $dcTable",
				    			"$eu.ID as DT_RowId",
// 				    			"$codeFlowPhase.name as PHASE_NAME",
				    			"$eu.ID as X_EU_ID",
  				    			"$euPhaseConfig.FLOW_PHASE as EU_FLOW_PHASE",
 				    			"$codeStatus.NAME as STATUS_NAME",
 				    			"$dcTable.*"]); */
				    	
// 		    			global $facility_id, $record_freq, $phase_type, $event_type, $alloc_type, $occur_date, $eu_group_id;
		    			$sSQL = "select a.EU_NAME,a.PHASE_NAME,a.X_EU_ID, a.EU_FLOW_PHASE,a.STATUS_NAME,x.*
						from
						(
						select a.name EU_NAME, a.ID X_EU_ID, b.FLOW_PHASE EU_FLOW_PHASE, c.name PHASE_NAME, s.NAME STATUS_NAME
						from
						ENERGY_UNIT a,
						EU_PHASE_CONFIG b,
						CODE_FLOW_PHASE c,
						CODE_STATUS s
						where
						a.id=b.eu_id
						and b.flow_phase=c.id
						and a.STATUS=s.ID
						" . ($record_freq > 0 ? " and a.DATA_FREQ=$record_freq" : "") . "
						" . ($event_type > 0 ? " and b.EVENT_TYPE=$event_type" : "") . "
						" . ($phase_type > 0 ? " and b.FLOW_PHASE=$phase_type" : "") . "
						" . ($eu_group_id > 0 ? " and a.EU_GROUP_ID=$eu_group_id" : " and a.EU_GROUP_ID is null") . "
		    			and a.FDC_DISPLAY='1'
		    			and a.EFFECTIVE_DATE<=STR_TO_DATE('$occur_date', '%m/%d/%Y')
		    			and a.facility_id='$facility_id'
		    			) a
		    			left join $dcTable x on x.eu_id=a.X_EU_ID
		    			and x.flow_phase=a.EU_FLOW_PHASE
		    			" . (($alloc_type > 0 && ($dcTable == "ENERGY_UNIT_DATA_ALLOC" || $dcTable == "ENERGY_UNIT_COMP_DATA_ALLOC")) ? " and x.ALLOC_TYPE=$alloc_type" : "") . "
		    			and x.OCCUR_DATE=STR_TO_DATE('$occur_date', '%m/%d/%Y')
		    			order by EU_NAME, EU_FLOW_PHASE";
    	//  		\Log::info(\DB::getQueryLog());
    	/* $dswk = $dataSet->keyBy('DT_RowId');
    	$objectIds = $dswk->keys(); */
 		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    			
    	];
    }
}
