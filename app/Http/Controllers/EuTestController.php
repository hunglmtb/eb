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
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date){
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
    	$object_id = $postData['EnergyUnit'];
    	$date_end = $postData['date_end'];
    	$date_end = Carbon::parse($date_end);
    	 
    	$euWheres = ['EU_ID' => $object_id];
    	
    	/* $sSQL="SELECT a.ID T_ID, a.EU_ID OBJ_ID,a.EFFECTIVE_DATE T_EFFECTIVE_DATE"
    			.($fields?", a.".str_replace(",",",a.",$fields):"").
    	" FROM `$table` a 
    	where a.EFFECTIVE_DATE between STR_TO_DATE('$date_begin', '%m/%d/%Y') 
    	and STR_TO_DATE('$date_end', '%m/%d/%Y') 
    	and a.eu_id='$object_id' 
    	order by a.EFFECTIVE_DATE"; */
    	 
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::where($euWheres)
				    	->whereBetween('EFFECTIVE_DATE', [$occur_date,$date_end])
				    	->select(
// 				    			"ID as T_ID",
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
}
