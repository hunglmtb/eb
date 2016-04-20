<?php

namespace App\Http\Controllers;
use App\Models\CodeFlowPhase;
use App\Models\Flow;


class FlowController extends CodeController {
    
	/* protected $type = ['idField'=>'FLOW_ID',
			'name'=>'FLOW',
			'dateField'=>'OCCUR_DATE'
	]; */
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date){
    	$record_freq = $postData['CodeReadingFrequency'];
    	$phase_type = $postData['CodeFlowPhase'];
    	
    	$flow = Flow::getTableName();
    	$codeFlowPhase = CodeFlowPhase::getTableName();
    	
    	$where = ['FACILITY_ID' => $facility_id, 'FDC_DISPLAY' => 1];
    	if ($record_freq>0) {
    		$where["$flow.RECORD_FREQUENCY"]= $record_freq;
    	}
    	if ($phase_type>0) {
    		$where['PHASE_ID']= $phase_type;
    	}
    	
    	//      	\DB::enableQueryLog();
    	$dataSet = Flow::join($codeFlowPhase,'PHASE_ID', '=', "$codeFlowPhase.ID")
				    	->where($where)
				    	->whereDate('EFFECTIVE_DATE', '<=', $occur_date)
				    	//      					->where('OCCUR_DATE', '=', $occur_date)
				    	->leftJoin($dcTable, function($join) use ($flow,$dcTable,$occur_date){
				    		$join->on("$flow.ID", '=', "$dcTable.flow_id");
				    		$join->where('OCCUR_DATE','=',$occur_date);
				    	})
				    	->select("$flow.name as $dcTable",
				    			"$flow.ID as DT_RowId",
				    			"$flow.ID as ".config("constants.flowId"),
				    			"$flow.phase_id as FL_FLOW_PHASE",
				    			"$codeFlowPhase.name as PHASE_NAME",
				    			"$codeFlowPhase.CODE as PHASE_CODE",
				    			//  				     			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*"
				    			)
		    			->orderBy($dcTable)
		    			->orderBy('FL_FLOW_PHASE')
		    			->get();
    	//  		\Log::info(\DB::getQueryLog());
    	$dswk = $dataSet->keyBy('DT_RowId');
    	$objectIds = $dswk->keys();
    	
    	return ['dataSet'=>$dataSet,'objectIds'=>$objectIds];
    }
    
    public function preSave(&$editedData,&$affectedIds,$postData) {
    	$flow = Flow::getTableName();
    	if (array_key_exists("FlowDataFdcValue", $editedData)) {
    		if (!array_key_exists("FlowDataValue", $editedData)){
    			$editedData["FlowDataValue"] = [];
    		}
    		if (!array_key_exists("FlowDataTheor", $editedData)){
    			$editedData["FlowDataTheor"] = [];
    		}
    		foreach ($editedData["FlowDataFdcValue"] as $element) {
    			$key = array_search($element[config("constants.flowId")], 
    								array_column($editedData["FlowDataValue"],
    										config("constants.flowId")));
    			if ($key===FALSE) {
    				$editedData["FlowDataValue"][] =  array_intersect_key($element,
    																		array_flip(array(config("constants.flowId"),
    																				config("constants.flFlowPhase"))));
    			}
    			$key = array_search($element[config("constants.flowId")],
    								array_column($editedData["FlowDataTheor"],
    										config("constants.flowId")));
    			if ($key===FALSE) {
    				$editedData["FlowDataTheor"][] =  array_intersect_key($element,
    						array_flip(array(config("constants.flowId"),
    								config("constants.flFlowPhase"))));
    			}
    			$affectedIds[]=$element['FLOW_ID'];
    		}
    	}
    }
}
