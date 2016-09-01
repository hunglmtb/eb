<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Cargo\VoyageController;
use App\Models\PdShipPortInformation;
use App\Models\PdTransportShipDetail;
use Illuminate\Http\Request;

class VoyageMarineController extends VoyageController {
    
	public function __construct() {
		parent::__construct();
		$this->modelName = "App\Models\PdTransportShipDetail";
		$this->parentType = "S";
	}
	
    public function getFirstProperty($dcTable){
    	if ($dcTable==PdTransportShipDetail::getTableName()) {
			return  ['data'=>$dcTable,'title'=>'BL/MR','width'=> 100];
    	}
    	return null;
	}
	
    
	public function loadDetail(Request $request){
    	$postData 				= $request->all();
    	$id 					= $postData['id'];
    	$pdShipPortInformation 	= PdShipPortInformation::getTableName();
    	$results 				= $this->getProperties($pdShipPortInformation);
    	$dataSet 				= $this->getTimesheetData($id,$results['properties']);
	    $results['dataSet'] 	= $dataSet;
	    
    	return response()->json(['PdShipPortInformation' => $results]);
	}
	
    public function getTimesheetData($id,$properties){
    	 
    	$pdShipPortInformation 			= PdShipPortInformation::getTableName();
    	$pdTransportShipDetail 			= PdTransportShipDetail::getTableName();
    	$dataSet = PdShipPortInformation::join($pdTransportShipDetail,function ($query) use ($id,$pdShipPortInformation,$pdTransportShipDetail) {
							    					$query->on("$pdShipPortInformation.VOYAGE_ID",'=',"$pdTransportShipDetail.VOYAGE_ID");
							    					$query->on("$pdShipPortInformation.PARCEL_NO",'=',"$pdTransportShipDetail.PARCEL_NO");
										    		$query->where("$pdTransportShipDetail.ID",'=',$id) ;
												})
						    			->select(
						    					"$pdShipPortInformation.*",
						    					"$pdShipPortInformation.ID as DT_RowId",
		 				    					"$pdShipPortInformation.ID as $pdShipPortInformation"
						    					)
				    					->get();
    	return $dataSet;
    }
}
