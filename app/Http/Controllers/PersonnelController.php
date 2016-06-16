<?php

namespace App\Http\Controllers;
use App\Models\Personnel;

class PersonnelController extends CodeController {
    
	public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$safetyTable,$facility_id,$occur_date,$properties){

    	/* $sSQL="select a.ID,a.TYPE,
    	a.TITLE,
    	DATE_FORMAT(START_SHIFT,'%m/%d/%Y %h:%i') START_SHIFT,
    	DATE_FORMAT(END_SHIFT,'%m/%d/%Y %h:%i') END_SHIFT,
    	WORK_HOURS 
    	from PERSONNEL a 
    	where a.FACILITY_ID=$facility_id 
    	and a.OCCUR_DATE=STR_TO_DATE('$occur_date','%m/%d/%Y') 
    	order by ID"; */
    	 
    	$personnel = Personnel::getTableName();
    	//      	\DB::enableQueryLog();
    	$dataSet = Personnel::where("FACILITY_ID","=",$facility_id)
				    	->where("OCCUR_DATE","=",$occur_date)
				    	->select(
				    			"ID as DT_RowId",
				    			"$personnel.*"
				    			)
 		    			->orderBy("ID")
		    			->get();
    	//  		\Log::info(\DB::getQueryLog());
    	
    	return ['dataSet'=>$dataSet];
    }
}
