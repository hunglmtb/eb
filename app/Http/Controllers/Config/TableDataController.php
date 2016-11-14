<?php
namespace App\Http\Controllers\Config;

use App\Http\Controllers\CodeController;
use App\Models\PdCargoNomination;
use App\Models\Storage;

class TableDataController extends CodeController {
    
    public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>90];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	
    	$date_end 		= array_key_exists('date_end',  $postData)?$postData['date_end']:null;
    	if ($date_end) {
	    	$date_end 		= \Helper::parseDate($date_end);
    	}
    	
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
    	$storage = Storage::getTableName();
    	$pdCargoNomination = PdCargoNomination::getTableName();
    	 
//     	\DB::enableQueryLog();
    	$query 	= $mdl::join($storage,"$dcTable.STORAGE_ID", '=', "$storage.ID")
    					->leftJoin($pdCargoNomination,"$pdCargoNomination.CARGO_ID", '=', "$dcTable.ID")
    					->where(["$storage.FACILITY_ID" => $facility_id])
				    	->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$pdCargoNomination.ID as IS_NOMINATED",
				    			"$dcTable.*");
//   		    			->orderBy('EFFECTIVE_DATE')
//   		    			->get();
  		if ($date_end) 		$query->whereDate("$dcTable.REQUEST_DATE",'<=',$date_end);
  		if ($occur_date) 	$query->whereDate("$dcTable.REQUEST_DATE",'>=',$occur_date);
  		$dataSet = $query->get();
//  		\Log::info(\DB::getQueryLog());
  		return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    	];
    }
}
