<?php

namespace App\Http\Controllers;
use App\Models\CodeQltySrcType;
use Carbon\Carbon;


class QualityController extends CodeController {
    
	/* protected $type = ['idField'=>'FLOW_ID',
			'name'=>'FLOW',
			'dateField'=>'OCCUR_DATE'
	]; */
	
	
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "QualityData";
		$this->idColumn = config("constants.qualityId");
		/* $this->phaseColumn = config("constants.flFlowPhase");
	
		$this->valueModel = "FlowDataValue";
		$this->theorModel = "FlowDataTheor"; */
		$this->isApplyFormulaAfterSaving = true;
	}
	
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	$src_type_id = $postData['CodeQltySrcType'];
    	$date_end = $postData['date_end'];
    	$date_end = Carbon::parse($date_end);
    	$filterBy = $postData['cboFilterBy'];
    	
    	$where = ['SRC_TYPE' => $src_type_id];
    	
    	$codeQltySrcType = CodeQltySrcType::getTableName();
    	switch ($src_type_id) {
    		case 1:						
    		case 2:						
    		case 3:						
    		case 4:
    			$uoms = $properties['uoms'];
    			$key = array_search('CodeQltySrcType', array_column($uoms, 'id'));
    			$objectType = $uoms[$key]['data']->find($src_type_id);
		    	$objectType = $objectType->CODE;
	    		$dataSet = $mdl::join($objectType,function ($query) use ($objectType,$facility_id,$dcTable) {
							    							$query->on("$objectType.ID",'=',"$dcTable.SRC_ID")
							    							->where("$objectType.FACILITY_ID",'=',$facility_id) ;
								})
						    	->where($where)
						    	->whereDate($filterBy, '>=', $occur_date)
						    	->whereDate($filterBy, '<=', $date_end)
						    	->select(
						    			"$dcTable.ID as $dcTable",
						    			"$dcTable.ID as DT_RowId",
						    			"$dcTable.ID",
						    			"$dcTable.*"
						    			)
				    			->orderBy($dcTable)
								->get();
    			break;
    	
    		default:
    			;
    			break;
    	}
    	
    	return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    	];
    }
    
	/* protected function getAffectedObjects($mdlName, $columns, $newData) {
		$mdl = "App\Models\\".$mdlName;
		$idField = $mdl::$idField;
		$objectId = $newData [$idField];
// 		$flowPhase = $newData [config ( "constants.flFlowPhase" )];
		$aFormulas = \FormulaHelpers::getAffects ( $mdlName, $columns, $objectId);
		return $aFormulas;
	} */
    
}
