<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdCargo;
use App\Models\PdVoyage;
use App\Models\PdVoyageDetail;
use Illuminate\Http\Request;

class CargoVoyageController extends CodeController {
    
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=> 50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$storage_id			= $postData['Storage'];
    	$date_end 			= $postData['date_end'];
    	$date_end 			= \Helper::parseDate($date_end);
    	
    	$mdlName 			= $postData[config("constants.tabTable")];
    	$mdl 				= "App\Models\\$mdlName";
    	$pdCargo 			= PdCargo::getTableName();
    	 
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::join($pdCargo,
			    			"$dcTable.CARGO_ID",
			    			'=',
			    			"$pdCargo.ID")
		    			->whereDate("$dcTable.SCHEDULE_DATE",'>=',$occur_date)
    					->whereDate("$dcTable.SCHEDULE_DATE",'<=',$date_end)
    					->where("$pdCargo.STORAGE_ID",'=',$storage_id)
				    	->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*") 
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet];
    }
    
	public function loadDetail(Request $request){
    	$postData 				= $request->all();
    	$id 					= $postData['id'];
     	$facility	 			= $postData['Facility'];
     	
    	$pdVoyageDetail			= PdVoyageDetail::getTableName();
    	$results 				= $this->getProperties($pdVoyageDetail);
    	
    	$dataSet 				= $this->getVoyageData($id,$facility,$results['properties']);
	    $results['dataSet'] 	= $dataSet;
	    
    	return response()->json(['PdVoyageDetail' => $results]);
	}
	
    public function getVoyageData($id,$facility,$properties){
    	$pdVoyage						= PdVoyage::getTableName();
    	$pdVoyageDetail					= PdVoyageDetail::getTableName();
    	
    	/* $sSQL="SELECT a.ID, 
    	$fields, 
    	b.SCHEDULE_UOM VOYAGE_SCHEDULE_UOM 
    	FROM pd_voyage_detail a, 
    	pd_voyage b 
    	WHERE a.VOYAGE_ID=b.ID 
    	and b.ID=$vid"; */
    	 
    	$dataSet = PdVoyageDetail::join($pdVoyage,
						    			"$pdVoyageDetail.VOYAGE_ID",
						    			'=',
						    			"$pdVoyage.ID")
				    			->where("$pdVoyage.ID",'=',$id)
				    			->select(
				    					"$pdVoyageDetail.*",
				    					"$pdVoyage.SCHEDULE_UOM as VOYAGE_SCHEDULE_UOM",
				    					"$pdVoyageDetail.ID as DT_RowId"
				    					)
		    					->get();
    	return $dataSet;
    }
}
