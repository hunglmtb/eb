<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Tank;

class TicketController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "RunTicketFdcValue";
		$this->idColumn = 'ID';
		$this->phaseColumn = 'FLOW_PHASE';
		
 		$this->valueModel = "RunTicketValue";
		$this->keyColumns = [$this->idColumn,$this->phaseColumn,'TANK_ID','OCCUR_DATE','TICKET_NO'];
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
    	$object_id = $postData['Tank'];
    	$date_end = $postData['date_end'];
    	$date_end = Carbon::parse($date_end);
    	
    	$tank = Tank::getTableName();
    	 
    	$wheres = ['TANK_ID' => $object_id];
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::join($tank,"$dcTable.TANK_ID", '=', "$tank.ID")
    					->where($wheres)
				    	->whereBetween('OCCUR_DATE', [$occur_date,$date_end])
				    	->select(
								"$dcTable.ID as $dcTable",
				    			"$dcTable.TANK_ID as OBJ_ID",
				    			"$tank.PRODUCT as FLOW_PHASE",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.OCCUR_DATE as T_OCCUR_DATE",
				    			"$dcTable.*") 
  		    			->orderBy("$dcTable.OCCUR_DATE")
  		    			->orderBy("$dcTable.LOADING_TIME")
  		    			->orderBy("$dcTable.TICKET_NO")
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    	];
    }
    
    public function checkExistPostEntry($editedData,$model,$element,$idColumn){
    	$tankID = 'TANK_ID';
    	$occurDate = 'OCCUR_DATE';
    	$ticketNo = 'TICKET_NO';
    	$tankIds = array_column($editedData[$model],$tankID);
    	$occurDates = array_column($editedData[$model],$occurDate);
    	$ticketNos = array_column($editedData[$model],$ticketNo);
    	
    	$notExist = (array_search($element[$tankID],$tankIds)===FALSE)||
		    	(array_search($element[$occurDate],$occurDates)===FALSE)||
		    	(array_search($element[$ticketNo],$ticketNos)===FALSE);
    	return $notExist;
    }
    
}
