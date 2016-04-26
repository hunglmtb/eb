<?php

namespace App\Http\Controllers;
use App\Models\CodeFlowPhase;
use App\Models\EnergyUnit;
use App\Models\CodeStatus;
use App\Models\EuPhaseConfig;
use App\Models\EnergyUnitDataAlloc;
use App\Models\EnergyUnitCompDataAlloc;

class EuController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "EnergyUnitDataFdcValue";
		$this->idColumn = config("constants.euId");
		$this->phaseColumn = config("constants.euFlowPhase");
		
		$this->valueModel = "EnergyUnitDataValue";
		$this->theorModel = "EnergyUnitDataTheor";
	}
	
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
    	
//     	\DB::enableQueryLog();
    	$dataSet = EnergyUnit::join($codeStatus,'STATUS', '=', "$codeStatus.ID")
				    	->join($euPhaseConfig,function ($query) use ($eu,$euPhaseConfig,$phase_type,$event_type) {
						    					$query->on("$euPhaseConfig.EU_ID",'=',"$eu.ID");
										    	if ($phase_type>0) $query->where("$euPhaseConfig.FLOW_PHASE",'=',$phase_type) ;
										    	if ($event_type>0) $query->where("$euPhaseConfig.EVENT_TYPE",'=',$event_type) ;
										    	//TODO note chu y active co the se dung
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
									    		
 									    			$energyUnitDataAlloc = EnergyUnitDataAlloc::getTableName();
 									    			$energyUnitCompDataAlloc = EnergyUnitCompDataAlloc::getTableName();
											    	if (($alloc_type > 0 && 
											    			($dcTable == $energyUnitDataAlloc ||
											    					$dcTable == $energyUnitCompDataAlloc))) 
									    				$join->where("$dcTable.ALLOC_TYPE",'=',$alloc_type);
				    	})
// 				    	->with($withs)
				    	->select(
				    			"$eu.name as $dcTable",
				    			"$euPhaseConfig.ID as DT_RowId",
 				    			"$codeFlowPhase.name as PHASE_NAME",
				    			"$eu.ID as ".config("constants.euId"),
   				    			"$euPhaseConfig.FLOW_PHASE as EU_FLOW_PHASE",
  				    			"$codeStatus.NAME as STATUS_NAME",
				    			"$codeFlowPhase.CODE as PHASE_CODE",
				    			"$dcTable.*") 
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
				    	
    	//  		\Log::info(\DB::getQueryLog());
    	/* $dswk = $dataSet->keyBy('DT_RowId');
    	$objectIds = $dswk->keys(); */
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    			
    	];
    }
    
    
    protected function afterSave($ids) {
    	/* foreach($ids as $mdlName => $modelIds ){
		    $mdl = "App\Models\\".$mdlName;
    		$mdl::where('ID', 1)->update(['votes' => 1]);
    		$upids = \FormulaHelpers::applyFormula($mdlName,$affectedIds,$occur_date,$typeName,true);
    		$ids[$mdlName] = array_merge($ids[$mdlName], $upids);
    		$ids[$mdlName]  = array_unique($ids[$mdlName]);
    	}
    	
    	
    	foreach($ids as $mdlName => $mdlIds ){
    		$mdl = "App\Models\\".$mdlName;
    		foreach($mdlIds as $key => $id ){
//     			$mdl::updateWith;
    		}
    		$editedData[$mdlName] = $mdlIds;
    	} */
    	
    }
    
    protected function getAffectedObjects($mdlName, $columns, $newData) {
    	$mdl = "App\Models\\".$mdlName;
    	$idField = $mdl::$idField;
    	$objectId = $newData [$idField];
    	$flowPhase = $newData [config ( "constants.euFlowPhase" )];
    	$aFormulas = \FormulaHelpers::getAffects ( $mdlName, $columns, $objectId,$flowPhase);
    	return $aFormulas;
    }
}
