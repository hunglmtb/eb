<?php
namespace App\Http\Controllers\Cargo;
use App\Http\Controllers\Cargo\VoyageController;
use App\Models\ShipCargoBlmrData;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CargoShipblmrController extends VoyageController {
    
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'BL/MR','width'=> $dcTable==ShipCargoBlmrData::getTableName()?100:60];
	}
	
	public function __construct() {
		parent::__construct();
		$this->detailModel = "ShipCargoBlmrData";
	}
	
    public function getDetailData($id,$postData,$properties){
    	$detailTable	 		= ShipCargoBlmrData::getTableName();
    	$dataSet 				= ShipCargoBlmrData::where("BLMR_ID",'=',$id)
				    			->select(
				    					"$detailTable.*",
				    					"$detailTable.ID as DT_RowId",
 				    					"$detailTable.ID as $detailTable"
				    					)
		    					->get();
    	return $dataSet;
    }
    
    public function cal(Request $request){
    	$postData 		= $request->all();
    	$id 			= $postData['id'];
    	$isAll			= isset($postData['isAll'])?$postData['isAll']:false;
//     		$sql="select ID, FORMULA_ID from SHIP_CARGO_BLMR_DATA where BLMR_ID=$vid";
    	$where 			= $isAll?["BLMR_ID" => $id]:["ID" => $id];
    	$blmrData 		= ShipCargoBlmrData::where($where)->select(["ID","FORMULA_ID"])->get();
    	
    	try {
	    	$ids = \DB::transaction(function () use ($blmrData){
	    		$ids = [];
	    		foreach($blmrData as $shipCargoBlmrData ){
		    		$val								= \FormulaHelpers::doEvalFormula($shipCargoBlmrData->FORMULA_ID);
		    		$shipCargoBlmrData->LAST_CALC_TIME 	= Carbon::now();
		    		$shipCargoBlmrData->ITEM_VALUE 		= $val;
		    		$shipCargoBlmrData->save();
		    		$ids[] 								= $shipCargoBlmrData->ID;
	    		}
	    		return $ids;
	    		/* $row=getOneRow("select ID, FORMULA_ID from SHIP_CARGO_BLMR_DATA where ID=$vid");
	    		$val=evalFormula($row[FORMULA_ID],false,$vid);
	    		$sql="update SHIP_CARGO_BLMR_DATA set LAST_CALC_TIME=now(),ITEM_VALUE='$val' where ID=$vid"; */
	    	
	    	});
    	}
    	catch (\Exception $e) {
//     		$results = "error";
			$msg = $e->getMessage();
    		return response()->json($msg, 500);
    	}
    	
    	$updatedData 	= ["ShipCargoBlmrData" 	=> ShipCargoBlmrData::findManyWithConfig($ids)];
    	$results 		= ['updatedData'		=> $updatedData,
    						'postData'			=> $postData];
    	
    	return response()->json($results);
    }
}
