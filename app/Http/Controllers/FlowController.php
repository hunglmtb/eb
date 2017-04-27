<?php

namespace App\Http\Controllers;
use App\Models\CodeFlowPhase;
use App\Models\Flow;
use App\Models\FlowDataForecast;
use App\Models\FlowDataPlan;

class FlowController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "FlowDataFdcValue";
		$this->idColumn = config("constants.flowId");
		$this->phaseColumn = config("constants.flFlowPhase");
	
		$this->valueModel = "FlowDataValue";
		$this->theorModel = "FlowDataTheor";
		$this->isApplyFormulaAfterSaving = true;
		$this->keyColumns = [$this->idColumn,$this->phaseColumn];
	}
	
	public function getGroupFilter($postData){
		$filterGroups = array('productionFilterGroup'	=> [],
							 'frequenceFilterGroup'		=> ['CodeReadingFrequency','CodeFlowPhase']
						);
		 
		return $filterGroups;
	}
	
	public function enableBatchRun($dataSet,$mdlName,$postData){
		return true;
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$record_freq 	= $postData['CodeReadingFrequency'];
    	$phase_type 	= $postData['CodeFlowPhase'];
    	$planType 		= array_key_exists('CodePlanType', $postData)?$postData['CodePlanType']:0;
    	$forecastType 	= array_key_exists('CodeForecastType', $postData)?$postData['CodeForecastType']:0;
    	
    	$flow = Flow::getTableName();
    	$codeFlowPhase = CodeFlowPhase::getTableName();
    	
    	$where = ['FACILITY_ID' => $facility_id, 'FDC_DISPLAY' => 1];
    	if ($record_freq>0) {
    		$where["$flow.RECORD_FREQUENCY"]= $record_freq;
//     		$where["$dcTable.RECORD_FREQUENCY"]= $record_freq;
    	}
    	if ($phase_type>0) {
    		$where['PHASE_ID']= $phase_type;
    	}
    	$startOfMonth	= $occur_date->copy();
    	$startOfMonth->startOfMonth();
    	if ($occur_date->ne($startOfMonth)) {
		    $where[]= ["$flow.RECORD_FREQUENCY",'<>',6];
		}
    	//      	\DB::enableQueryLog();
		$columns	= $this->extractRespondColumns($dcTable,$properties);
		if (!$columns) $columns = [];
		array_push($columns,"$dcTable.OCCUR_DATE",
							"$flow.name as $dcTable",
			    			"$dcTable.ID as DT_RowId",
			    			"$flow.ID as ".config("constants.flowId"),
			    			"$flow.ID as ID",
			    			"$flow.phase_id as FL_FLOW_PHASE",
			    			"$codeFlowPhase.name as PHASE_NAME",
			    			"$codeFlowPhase.CODE as PHASE_CODE");
		
    	$dataSet = Flow::join($codeFlowPhase,'PHASE_ID', '=', "$codeFlowPhase.ID")
				    	->where($where)
				    	->whereDate('EFFECTIVE_DATE', '<=', $occur_date)
				    	//      					->where('OCCUR_DATE', '=', $occur_date)
				    	->leftJoin($dcTable, function($join) use ($flow,$dcTable,$occur_date,$planType,$forecastType){
				    		$join->on("$flow.ID", '=', "$dcTable.flow_id");
				    		$join->where('OCCUR_DATE','=',$occur_date);
				    		if (($planType > 0 &&  ($dcTable == FlowDataPlan::getTableName() )))
			    				$join->where("$dcTable.PLAN_TYPE",'=',$planType);
		    				else if (($forecastType > 0 &&  ($dcTable == FlowDataForecast::getTableName() )))
		    					$join->where("$dcTable.FORECAST_TYPE",'=',$forecastType);
				    	})
				    	->select($columns)
		    			->orderBy($dcTable)
		    			->orderBy('FL_FLOW_PHASE')
		    			->get();
    	//  		\Log::info(\DB::getQueryLog());
    	return ['dataSet'=>$dataSet];
    }
    
    public function getObjectIds($dataSet,$postData){
    	$objectIds = $dataSet->map(function ($item, $key) {
    		return ["DT_RowId"			=> $item->DT_RowId,
    				"FL_FLOW_PHASE"		=> $item->FL_FLOW_PHASE,
    				"FLOW_ID"			=> $item->X_FLOW_ID,
    				"X_FLOW_ID"			=> $item->X_FLOW_ID
    		];
    	});
    	return $objectIds;
    }
    
	public function getHistoryConditions($dcTable,$rowData,$row_id){
		$obj_id			= $rowData[config("constants.flowId")];
		return ['FLOW_ID'	=>	$obj_id];
	}

    public function getFirstProperty($dcTable){
    	return  ['data'=>$dcTable,'title'=>'Object name','width'=>300];
    }    
}
