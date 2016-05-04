<?php

namespace App\Http\Controllers;
use App\Models\CodeProductType;
use App\Models\Tank;

class StorageController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "EnergyUnitDataFdcValue";
		$this->idColumn = config("constants.euId");
		$this->phaseColumn = config("constants.euFlowPhase");
		
		$this->valueModel = "EnergyUnitDataValue";
		$this->theorModel = "EnergyUnitDataTheor";
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date){
//     	$eu_group_id = $postData['EnergyUnitGroup'];
//     	$record_freq = $postData['CodeReadingFrequency'];
//     	$phase_type = $postData['CodeFlowPhase'];
    	$product_type = $postData['CodeProductType'];
    	
    	$tank = Tank::getTableName();
    	$codeProductType = CodeProductType::getTableName();
    	
    	$euWheres = ['FACILITY_ID' => $facility_id, 'FDC_DISPLAY' => 1];
    	
//     	\DB::enableQueryLog();
    	$dataSet = Tank::join($codeProductType,function ($query) use ($tank,$codeProductType,$product_type) {
						    					$query->on("$codeProductType.ID",'=',"$tank.PRODUCT");
										    	if ($product_type>0) $query->where("$tank.PRODUCT",'=',$product_type);
						}) 
    					->where($euWheres)
				    	->whereDate('START_DATE', '<=', $occur_date)
				    	->leftJoin($dcTable, function($join) use ($tank,$dcTable,$occur_date){
										    		$join->on("$tank.ID", '=', "$dcTable.TANK_ID")
	 									    				->where("$dcTable.OCCUR_DATE",'=',$occur_date);
				    	})
				    	->select(
				    			"$tank.name as $dcTable",
				    			"$tank.ID as DT_RowId",
 				   				"$codeProductType.name as PHASE_NAME",
				    			"$tank.ID as ".config("constants.euId"),
				 				"$tank.STORAGE_ID",
				 				"$tank.product as OBJ_FLOW_PHASE",
				    			"$dcTable.*") 
 		    			->orderBy($dcTable)
  		    			->orderBy("$tank.PRODUCT")
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    	];
    }
    
    
    protected function afterSave($resultRecords,$occur_date) {
    	\DB::enableQueryLog();
    	foreach($resultRecords as $mdlName => $records ){
    		$mdl = "App\Models\\".$mdlName;
    		foreach($records as $record ){
     			$mdl::updateWithQuality($record,$occur_date);
    		}
    	}
  		\Log::info(\DB::getQueryLog());
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
