<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\TerminalActivitySetList;
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
    	$dataSet = TerminalTimesheetData::where("$terminalTimesheetData.IS_LOAD",'=',1)
						    			->where("$terminalTimesheetData.PARENT_ID",'=',$id)
						    			->select(
						    					"$terminalTimesheetData.*",
						    					"$terminalTimesheetData.ID as DT_RowId",
		 				    					"$terminalTimesheetData.ID as $terminalTimesheetData"
						    					)
				    					->get();
    	return $dataSet;
    }
    
    
    public function activities(Request $request){
    	$postData 				= $request->all();
    	$set_id					= $postData['id'];
    	/* $set_id=$_REQUEST[id];
    	$s="select b.ID,
    	b.NAME 
    	from TERMINAL_ACTIVITY_SET_LIST a,
    	PD_CODE_LOAD_ACTIVITY b 
    	where a.SET_ID=$set_id 
    	and a.ACTIVITY_ID=b.ID 
    	order by b.`ORDER`";
    	
    	$re=mysql_query($s) or die("fail: ".$s."-> error:".mysql_error());
    	while($row=mysql_fetch_array($re))
    	{
    		echo "$row[ID]:$row[NAME];";
    	} */
    	
    	$terminalActivitySetList 		= TerminalActivitySetList::getTableName();
//     	$pdCodeLoadActivity				= PdCodeLoadActivity::getTableName();
    	$dataSet 						= TerminalActivitySetList::where("SET_ID",'=',$set_id)
									    	->select("ACTIVITY_ID","ACTIVITY_ID as $terminalActivitySetList")
									    	->get();
    	/* TerminalActivitySetList::join($terminalActivitySetList,
    			"$terminalActivitySetList.ACTIVITY_ID",
    			'=',
    			"$pdCodeLoadActivity.ID")
    			->where("SET_ID",'=',$set_id)
    			->orderBy("$pdCodeLoadActivity.ORDER")
    			->select(
    					"$dcTable.ID as $dcTable",
    					"$dcTable.ID as DT_RowId",
    					"$dcTable.*")
    					->get(); */
    	
    	$results = ['updatedData'	=>['TerminalTimesheetData'=>$dataSet],
    				'postData'		=>$postData];
    	return response()->json($results);
    }
}
