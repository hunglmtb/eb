<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

class TicketController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "TicketDataFdcValue";
		$this->idColumn = config("constants.ticketId");
		$this->phaseColumn = config("constants.ticketFlowPhase");
		
		$this->valueModel = "TicketDataValue";
		$this->theorModel = "TicketDataTheor";
		$this->keyColumns = [$this->idColumn,$this->phaseColumn];
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
    	
    	$wheres = ['TANK_ID' => $object_id];
    	
    	/* $sSQL="	SELECT a.ID T_ID, 
    			a.TANK_ID OBJ_ID,
    			a.OCCUR_DATE T_OCCUR_DATE".
    			($fields?", a.".str_replace(",",",a.",$fields):"")." 
    			FROM `$table` a 
    			where a.OCCUR_DATE between STR_TO_DATE('$date_begin', '%m/%d/%Y') and STR_TO_DATE('$date_end', '%m/%d/%Y') 
		    	and a.TANK_ID='$object_id' 
		    	order by a.OCCUR_DATE,
		    	a.LOADING_TIME,
		    	a.TICKET_NO"; */
    	 
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::where($wheres)
				    	->whereBetween('OCCUR_DATE', [$occur_date,$date_end])
				    	->select(
								"ID as $dcTable",
				    			"TANK_ID as OBJ_ID",
				    			"ID as DT_RowId",
				    			"OCCUR_DATE as T_OCCUR_DATE",
				    			"$dcTable.*") 
  		    			->orderBy('OCCUR_DATE')
  		    			->orderBy('LOADING_TIME')
  		    			->orderBy('TICKET_NO')
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    	];
    }
    
}
