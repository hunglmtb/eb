<?php

namespace App\Http\Controllers;
use App\Models\EnergyUnit;
use Carbon\Carbon;

class EuTestController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->isApplyFormulaAfterSaving = true;
		$this->fdcModel = "EuTestDataFdcValue";
		$this->idColumn = 'ID';
// 		$this->phaseColumn = config("constants.euFlowPhase");
		
		$this->valueModel = "EuTestDataStdValue";
		$this->theorModel = "EuTestDataValue";
		$this->keyColumns = [$this->idColumn,'EU_ID','EFFECTIVE_DATE'];
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
    	$object_id = $postData['EnergyUnit'];
    	$date_end = $postData['date_end'];
//     	$date_end = Carbon::parse($date_end);
    	$date_end		= 	\Helper::parseDate($date_end);
    	
    	$euWheres = ['EU_ID' => $object_id];
    	
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::where($euWheres)
				    	->whereBetween('EFFECTIVE_DATE', [$occur_date,$date_end])
				    	->select(
// 								"ID",
				    			"ID as DT_RowId",
// 				    			"EU_ID as OBJ_ID",
// 				    			"EFFECTIVE_DATE as T_EFFECTIVE_DATE",
				    			"$dcTable.*") 
  		    			->orderBy('EFFECTIVE_DATE')
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    	];
    }
    
    protected function preSave(&$editedData, &$affectedIds, $postData) {
    	if ($editedData) {
    		if (array_key_exists ($this->fdcModel, $editedData )) {
    			$this->preSaveModel ( $editedData, $affectedIds, $this->valueModel,$this->fdcModel);
    		}
    		if (array_key_exists ($this->valueModel, $editedData )) {
    			$this->preSaveModel( $editedData, $affectedIds, $this->theorModel,$this->valueModel);
    		}
    	}
    }
    
    
    public function getHistoryConditions($dcTable,$rowData,$row_id){
    	return ['EU_ID'			=>	$rowData["EU_ID"],
    	];
    }
    
    public function getHistoryData($mdl, $field,$rowData,$where, $limit){
    	$row_id			= $rowData['ID'];
    	if ($row_id<=0) return [];
    	 
    	$occur_date		= $rowData['BEGIN_TIME'];
    	$history 		= $mdl::where($where)
    	->whereDate('BEGIN_TIME', '<', $occur_date)
    	->whereNotNull($field)
    	->orderBy('BEGIN_TIME','desc')
    	->skip(0)->take($limit)
    	->select('BEGIN_TIME as OCCUR_DATE',
    			"$field as VALUE"
    			)
    			->get();
    			return $history;
    }
    
    public function getFieldTitle($dcTable,$field,$rowData){
    	$row = EnergyUnit::where(['ID'=>$rowData['EU_ID']])
					    	->select('NAME')
					    	->first();
    	$obj_name		= $row?$row->NAME:"";
    	return $obj_name;
    }
    
}
