<?php
namespace App\Http\Controllers\Cargo;
use App\Http\Controllers\Cargo\VoyageController;
use App\Models\ShipCargoBlmrData;

class CargoShipblmrController extends VoyageController {
    
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
}
