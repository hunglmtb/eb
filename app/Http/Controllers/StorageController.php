<?php

namespace App\Http\Controllers;
use App\Models\CodeProductType;
use App\Models\Tank;

class StorageController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "TankDataFdcValue";
		$this->idColumn = config("constants.tankId");
 		$this->phaseColumn = config("constants.tankFlowPhase");
		
 		$this->valueModel = "TankDataValue";
// 		$this->theorModel = "TankDataTheor";
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date){
    	$product_type = $postData['CodeProductType'];
    	switch ($dcTable) {
    		case 'storage_data_value'     :
    		case 'storage_data_plan'      :
    		case 'storage_data_forecast'  :
			case 'STORAGE_DATA_VALUE'     :
    		case 'STORAGE_DATA_PLAN'      :
    		case 'STORAGE_DATA_FORECAST'  :
    			$mdlName = "Storage";
    			$joindField = "STORAGE_ID";
    			$extraColumn = false;
    			break;
    		default:
    			$mdlName = "Tank";
    			$joindField = "TANK_ID";
    			$extraColumn = "STORAGE_ID";
    			break;
    	}
    	
    	$mdl = "App\Models\\$mdlName";
    	$mainTableName = $mdl::getTableName();
    	$codeProductType = CodeProductType::getTableName();
    	
    	$euWheres = ['FACILITY_ID' => $facility_id, 'FDC_DISPLAY' => 1];
    	
//     	\DB::enableQueryLog();

    	$columns = ["$mainTableName.name as $dcTable",
			    	"$mainTableName.ID as DT_RowId",
			    	"$codeProductType.name as PHASE_NAME",
			    	"$mainTableName.ID as ".config("constants.tankId"),
			    	"$mainTableName.product as OBJ_FLOW_PHASE",
			    	"$dcTable.*"];
    	
    	if ($extraColumn) $columns[] = "$mainTableName.$extraColumn";
    	
    	$dataSet = $mdl::join($codeProductType,function ($query) use ($mainTableName,$codeProductType,$product_type) {
						    					$query->on("$codeProductType.ID",'=',"$mainTableName.PRODUCT");
										    	if ($product_type>0) $query->where("$mainTableName.PRODUCT",'=',$product_type);
						}) 
    					->where($euWheres)
				    	->whereDate('START_DATE', '<=', $occur_date)
				    	->leftJoin($dcTable, function($join) use ($mainTableName,$dcTable,$occur_date,$joindField){
										    		$join->on("$mainTableName.ID", '=', "$dcTable.$joindField")
	 									    				->where("$dcTable.OCCUR_DATE",'=',$occur_date);
				    	})
				    	->select($columns) 
 		    			->orderBy($dcTable)
  		    			->orderBy("$mainTableName.PRODUCT")
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    	];
    }
}
