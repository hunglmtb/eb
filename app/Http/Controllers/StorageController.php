<?php

namespace App\Http\Controllers;
use App\Models\CodeProductType;
use App\Models\StorageDataForecast;
use App\Models\StorageDataPlan;
use App\Models\StorageDataValue;
use App\Models\Tank;
use App\Models\TankDataForecast;
use App\Models\TankDataPlan;
use App\Models\TankDataValue;

class StorageController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->isApplyFormulaAfterSaving = true;
		$this->fdcModel = "TankDataFdcValue";
		$this->idColumn = config("constants.tankId");
 		$this->phaseColumn = config("constants.tankFlowPhase");
		
 		$this->valueModel = "TankDataValue";
 		$this->keyColumns = [$this->idColumn,$this->phaseColumn];
// 		$this->theorModel = "TankDataTheor";
	}
	
	public function enableBatchRun($dataSet,$mdlName,$postData){
		return true;
	}
	
	public function getObjectIds($dataSet,$postData){
		$objectIds = $dataSet->map(function ($item, $key) {
			return ["DT_RowId"			=> $item->DT_RowId,
					"OBJ_FLOW_PHASE"	=> $item->OBJ_FLOW_PHASE,
					"X_TANK_ID"			=> $item->X_TANK_ID
// 					"TANK_ID"			=> $item->TANK_ID,
			];
		});
			return $objectIds;
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$product_type 	= $postData['CodeProductType'];
    	$planType 		= array_key_exists('CodePlanType', $postData)?$postData['CodePlanType']:0;
    	$forecastType 	= array_key_exists('CodeForecastType', $postData)?$postData['CodeForecastType']:0;
    	 
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
				    	->leftJoin($dcTable, function($join) use ($mainTableName,$dcTable,$occur_date,$joindField,$planType,$forecastType){
										    		$join->on("$mainTableName.ID", '=', "$dcTable.$joindField")
	 									    			->where("$dcTable.OCCUR_DATE",'=',$occur_date);
 									    			if (($planType > 0 && ($dcTable == StorageDataPlan::getTableName()||$dcTable == TankDataPlan::getTableName())))
 									    				$join->where("$dcTable.PLAN_TYPE",'=',$planType);
 									    			else if (($forecastType > 0 && ($dcTable == StorageDataForecast::getTableName()||$dcTable == TankDataForecast::getTableName())))
 									    				$join->where("$dcTable.FORECAST_TYPE",'=',$forecastType);
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
    
    
    protected function afterSave($resultRecords,$occur_date) {
//     	\DB::enableQueryLog();
    	$tankDataValue = TankDataValue::getTableName();
    	$tank = Tank::getTableName();
    	$columns = [ \DB::raw("sum(BEGIN_VOL) 	as	BEGIN_VOL"),
	    			\DB::raw("sum(END_VOL) 			as	END_VOL"),
	    			\DB::raw("sum(BEGIN_LEVEL) 		as	BEGIN_LEVEL"),
	    			\DB::raw("sum(END_LEVEL) 		as	END_LEVEL"),
	    			\DB::raw("sum(TANK_GRS_VOL) 		as	GRS_VOL"),
	    			\DB::raw("sum(TANK_NET_VOL) 		as	NET_VOL"),
	    			\DB::raw("sum(AVAIL_SHIPPING_VOL) as	AVAIL_SHIPPING_VOL")];
    	$attributes = ['OCCUR_DATE'=>$occur_date];
    	$storage_ids = [];
    	foreach($resultRecords as $mdlName => $records ){
//     		$mdl = "App\Models\\".$mdlName;
//     		$mdlRecords = $mdl::with('Tank')->whereIn();
    		foreach($records as $mdlRecord ){
	    		$storageID = $mdlRecord->getStorageId();
	    		if ($storageID) {
		    		$storage_ids[] = $storageID;
	    		}
    		}
    	}
    	$storage_ids = array_unique($storage_ids);
    	 
    	foreach($storage_ids as $storage_id){
	    	$values = TankDataValue::join($tank,function ($query) use ($tankDataValue,$tank,$storage_id) {
						    					$query->on("$tank.ID",'=',"$tankDataValue.TANK_ID")
								    					->where("$tank.STORAGE_ID",'=',$storage_id);
							})
					    	->whereDate('OCCUR_DATE', '=', $occur_date)
					    	->select($columns) 
	  		    			->first();
			
	  		$attributes['STORAGE_ID'] = $storage_id;
	  		$values = $values->toArray();
	  		$values['STORAGE_ID'] = $storage_id;
	  		$values['OCCUR_DATE'] = $occur_date;
	  		StorageDataValue::updateOrCreate($attributes,$values);
    	}
//     	\Log::info(\DB::getQueryLog());
    }
    
	public function getHistoryConditions($table,$rowData,$row_id){
		if(substr($table,0,4)=="TANK"){
			$obj_table="TANK";
			$obj_id_field="TANK_ID";
		}
		else if(substr($table,0,7)=="STORAGE"){
			$obj_table="STORAGE";
			$obj_id_field="STORAGE_ID";
		}
		else return null;
		
		$obj_id			= $rowData[$obj_id_field];
		return [$obj_id_field	=>	$obj_id];
	}
}
