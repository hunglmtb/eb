<?php

namespace App\Http\Controllers;
use App\Models\CodeFlowPhase;
use App\Models\Flow;


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
	
	/* public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'Object name','width'=>230];
	} */
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
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
    
	protected function getAffectedObjects($mdlName, $columns, $newData) {
		$mdl = "App\Models\\".$mdlName;
		$idField = $mdl::$idField;
		$objectId = $newData [$idField];
// 		$flowPhase = $newData [config ( "constants.flFlowPhase" )];
		$aFormulas = \FormulaHelpers::getAffects ( $mdlName, $columns, $objectId);
		return $aFormulas;
	}
	
	public function getHistory($mdl,$field,$rowData,$limit){
		$dcTable		= $mdl::getTableName();
		$obj_name		= $rowData[$dcTable];
		
		$row_id			= $rowData['ID'];
		$obj_id			= $rowData["FLOW_ID"];
		
		$fieldName		= $this->getFieldLabel($field,$mdl::getTableName());
		$occur_date		= $row_id>0?$rowData['OCCUR_DATE']:Carbon::now();
		$history = $mdl::where('FLOW_ID','=',$obj_id)
						->whereDate('OCCUR_DATE', '<', $occur_date)
						->whereNotNull($field)
						->orderBy('OCCUR_DATE','desc')
						->skip(0)->take($limit)
						->select('OCCUR_DATE',					    		
								"$field as VALUE"
								)
						->get();
		
		return ['name'		=> $obj_name,
				'dataSet'	=> $history,
				'fieldName'	=> $fieldName
		];
	}
    
}
