<?php

namespace App\Http\Controllers;
use App\Models\EnergyUnit;
use Carbon\Carbon;

class EuTestController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "EnergyUnitDataFdcValue";
		$this->idColumn = config("constants.euId");
		$this->phaseColumn = config("constants.euFlowPhase");
		
		$this->valueModel = "EnergyUnitDataValue";
		$this->theorModel = "EnergyUnitDataTheor";
		$this->keyColumns = [$this->idColumn,$this->phaseColumn];
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
    	$object_id = $postData['EnergyUnit'];
    	$date_end = $postData['date_end'];
    	$date_end = Carbon::parse($date_end);
    	
    	$euWheres = ['EU_ID' => $object_id];
    	
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::where($euWheres)
				    	->whereBetween('EFFECTIVE_DATE', [$occur_date,$date_end])
				    	->select(
								"ID as $dcTable",
				    			"ID",
				    			"ID as DT_RowId",
				    			"EU_ID as OBJ_ID",
				    			"EFFECTIVE_DATE as T_EFFECTIVE_DATE",
				    			"$dcTable.*") 
  		    			->orderBy('EFFECTIVE_DATE')
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    	];
    }
    
}
