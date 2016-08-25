<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdCargoLoad;
use App\Models\PdCargo;
use App\Models\PdCodeLoadActivity;
use App\Models\TerminalTimesheetData;
use Illuminate\Http\Request;

class CargoLoadController extends CodeController {
    
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=> 50];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$storage_id		= $postData['Storage'];
    	$date_end 		= $postData['date_end'];
    	$date_end 		= \Helper::parseDate($date_end);
    	
    	$mdlName 		= $postData[config("constants.tabTable")];
    	$mdl 			= "App\Models\\$mdlName";
    	 
    	$pdCargo 		= PdCargo::getTableName();
    	$dataSet = $mdl::join($pdCargo,
			    			"$dcTable.CARGO_ID",
			    			'=',
			    			"$pdCargo.ID")
		    			->whereDate("$dcTable.DATE_LOAD",'>=',$occur_date)
    					->whereDate("$dcTable.DATE_LOAD",'<=',$date_end)
				    	->where("$pdCargo.STORAGE_ID",'=',$storage_id)
    					->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*") 
  		    			->get();
 		    			
    	return ['dataSet'=>$dataSet];
    }
    
	public function loadDetail(Request $request){
    	$postData 				= $request->all();
    	$id 					= $postData['id'];
    	$terminalTimesheetData 	= TerminalTimesheetData::getTableName();
    	$results 				= $this->getProperties($terminalTimesheetData);
    	$dataSet 				= $this->getTimesheetData($id,$results['properties']);
	    $results['dataSet'] 	= $dataSet;
	    
    	return response()->json(['TerminalTimesheetData' => $results]);
	}
	
    public function getTimesheetData($id,$properties){
    	$terminalTimesheetData 			= TerminalTimesheetData::getTableName();
    	$pdCodeLoadActivity				= PdCodeLoadActivity::getTableName();
    	$dataSet = TerminalTimesheetData::join($pdCodeLoadActivity,
								    			"$terminalTimesheetData.ACTIVITY_ID",
								    			'=',
								    			"$pdCodeLoadActivity.ID")
						    			->where("$terminalTimesheetData.IS_LOAD",'=',1)
						    			->where("$terminalTimesheetData.PARENT_ID",'=',$id)
						    			->select(
						    					"$terminalTimesheetData.*",
						    					"$terminalTimesheetData.ID as DT_RowId",
		 				    					"$terminalTimesheetData.ID as $terminalTimesheetData"
// 						    					"$pdCodeLoadActivity.NAME as ACTIVITY_NAME"
// 						    					"$pdCodeLoadActivity.NAME as ACTIVITY_ID"
						    					)
				    					->get();
    	return $dataSet;
    }
}
