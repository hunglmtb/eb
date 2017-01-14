<?php

namespace App\Http\Controllers;
use App\Models\CodeEventType;
use App\Models\CodeFlowPhase;
use App\Models\CodeStatus;
use App\Models\EnergyUnit;
use App\Models\EnergyUnitCompDataAlloc;
use App\Models\EnergyUnitDataAlloc;
use App\Models\EnergyUnitDataForecast;
use App\Models\EnergyUnitDataPlan;
use App\Models\EuPhaseConfig;

class EuController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->isApplyFormulaAfterSaving = true;
		$this->fdcModel 	= "EnergyUnitDataFdcValue";
		$this->idColumn 	= config("constants.euId");
		$this->phaseColumn 	= config("constants.euFlowPhase");
		
		$this->valueModel 	= "EnergyUnitDataValue";
		$this->theorModel 	= "EnergyUnitDataTheor";
		
		$this->keyColumns 	= [$this->idColumn,$this->phaseColumn];
		$this->keyColumns[] = config("constants.eventType");
	}
	

	public function enableBatchRun($dataSet,$mdlName,$postData){
		return true;
	}
	
	public function getObjectIds($dataSet,$postData){
		$objectIds = $dataSet->map(function ($item, $key) {
			return ["DT_RowId"			=> $item->DT_RowId,
					"EU_FLOW_PHASE"		=> $item->EU_FLOW_PHASE,
					"EU_ID"				=> $item->X_EU_ID,
					"X_EU_ID"			=> $item->X_EU_ID
			];
		});
		return $objectIds;
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$eu_group_id 	= $postData['EnergyUnitGroup'];
    	$record_freq 	= $postData['CodeReadingFrequency'];
    	$phase_type 	= $postData['CodeFlowPhase'];
    	$event_type 	= $postData['CodeEventType'];
    	$alloc_type 	= array_key_exists('CodeAllocType', $postData)?$postData['CodeAllocType']:0;
    	$planType 		= array_key_exists('CodePlanType', $postData)?$postData['CodePlanType']:0;
    	$forecastType 	= array_key_exists('CodeForecastType', $postData)?$postData['CodeForecastType']:0;
    	 
    	$eu = EnergyUnit::getTableName();
    	$codeFlowPhase = CodeFlowPhase::getTableName();
    	$codeStatus = CodeStatus::getTableName();
    	$euPhaseConfig = EuPhaseConfig::getTableName();
    	$codeEventType = CodeEventType::getTableName();
    	 
    	$euWheres = ['FACILITY_ID' => $facility_id, 'FDC_DISPLAY' => 1];
    	if ($record_freq>0) $euWheres["$eu.DATA_FREQ"]= $record_freq;
     	if ($eu_group_id>0) $euWheres["$eu.EU_GROUP_ID"]= $eu_group_id;
//     	else $euWheres["$eu.EU_GROUP_ID"]= null;
    	
//      	\DB::enableQueryLog();
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
						->join($codeEventType,"$euPhaseConfig.EVENT_TYPE", '=', "$codeEventType.ID")
						->where($euWheres)
				    	->whereDate('EFFECTIVE_DATE', '<=', $occur_date)
				    	->leftJoin($dcTable, function($join) use ($eu,$dcTable,$euPhaseConfig,$occur_date,$alloc_type,$planType,$forecastType){
											    	//TODO add table name 
										    		$join->on("$eu.ID", '=', "$dcTable.EU_ID")
	 					 				    			->on("$dcTable.FLOW_PHASE",'=',"$euPhaseConfig.FLOW_PHASE")
	 					 				    			->on("$dcTable.EVENT_TYPE",'=',"$euPhaseConfig.EVENT_TYPE")
	 					 				    			->where("$dcTable.OCCUR_DATE",'=',$occur_date);
									    		
 									    			$energyUnitDataAlloc 		= EnergyUnitDataAlloc::getTableName();
 									    			$energyUnitCompDataAlloc 	= EnergyUnitCompDataAlloc::getTableName();
											    	if (($alloc_type > 0 &&  ($dcTable == $energyUnitDataAlloc || $dcTable == $energyUnitCompDataAlloc))) 
									    				$join->where("$dcTable.ALLOC_TYPE",'=',$alloc_type);
											    	else if (($planType > 0 &&  ($dcTable == EnergyUnitDataPlan::getTableName() ))) 
									    				$join->where("$dcTable.PLAN_TYPE",'=',$planType);
								    				else if (($forecastType > 0 &&  ($dcTable == EnergyUnitDataForecast::getTableName() )))
								    					$join->where("$dcTable.FORECAST_TYPE",'=',$forecastType);
				    	})
				    	->select(
				    			"$dcTable.*",
				    			"$eu.name as $dcTable",
				    			"$euPhaseConfig.ID as DT_RowId",
 				    			"$codeFlowPhase.name as PHASE_NAME",
 				    			"$codeEventType.name as TYPE_NAME",
				    			"$euPhaseConfig.EVENT_TYPE as ".config("constants.eventType"),
				    			"$eu.ID as ".config("constants.euId"),
   				    			"$euPhaseConfig.FLOW_PHASE as EU_FLOW_PHASE",
  				    			"$codeStatus.NAME as STATUS_NAME",
				    			"$codeFlowPhase.CODE as PHASE_CODE",
				    			"$codeEventType.CODE as TYPE_CODE"
				    			) 
 		    			->orderBy($dcTable)
  		    			->orderBy('EU_FLOW_PHASE')
  		    			->get();
//   		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    	];
    }
    
    
    public function checkExistPostEntry($editedData,$model,$element,$idColumn){
    	$found	= current(array_filter($editedData[$model], function($item) use($model,$element) { 
    		return $item[config("constants.euId")] 	== $element[config("constants.euId")]&&
    				$item["EU_FLOW_PHASE"] 			== $element["EU_FLOW_PHASE"]&&
    				count($item)>4;
    		;
    	}));
    	
    	return $found===FALSE;
    }
    
    protected function afterSave($resultRecords,$occur_date) {
//     	\DB::enableQueryLog();
    	foreach($resultRecords as $mdlName => $records ){
    		$mdl = "App\Models\\".$mdlName;
    		foreach($records as $record ){
     			$mdl::updateWithQuality($record,$occur_date);
    		}
    	}
//   		\Log::info(\DB::getQueryLog());
    }
    
    protected function getFlowPhase($newData) {
    	return $newData [config ( "constants.euFlowPhase" )];
    }
    
    public function getHistoryConditions($dcTable,$rowData,$row_id){
    	$obj_id			= $rowData[config("constants.euId")];
    	$where			= ["EU_ID"	=> $obj_id];
    	if($row_id<=0) {
    		$where['FLOW_PHASE']	= 2;
    	}
    	else{
    		$where['FLOW_PHASE']	= $rowData['FLOW_PHASE'];
    		$where['EVENT_TYPE']	= $rowData['EVENT_TYPE'];
    	}
    	return $where;
    }
}
