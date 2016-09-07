<?php
namespace App\Http\Controllers\Cargo;
use App\Http\Controllers\Cargo\VoyageController;

class VoyagePipelineController extends VoyageController {
    
	public function __construct() {
		parent::__construct();
		$this->modelName = "App\Models\PdTransportGroundDetail";
		$this->parentType = "G";
	}
	
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'BL/MR','width'=> 60];
	}
	
	public function getUpdateFields($shipCargoBlmr,$transportType){
		return array(
				"$shipCargoBlmr.ITEM_VALUE" => \DB::raw("ifnull($transportType.ADJUSTED_QUANTITY,$transportType.QUANTITY)"),
				"$shipCargoBlmr.ITEM_UOM" 	=> \DB::raw("$transportType.QUANTITY_UOM"),
				"$shipCargoBlmr.DATE_TIME" 	=> \DB::raw("$transportType.BEGIN_LOADING_TIME"),
		);
	}
	
	public function getInsertFields($pid,$pdVoyage,$storage,$transportType){
		return [
				\DB::raw("$pid as PARENT_ID") ,
				\DB::raw("'$this->parentType' as PARENT_TYPE"),
				"$pdVoyage.CARRIER_ID"              ,
				"$transportType.VOYAGE_ID"               ,
				"$transportType.ORIGIN_PORT as PORT_ID" ,
				"$transportType.CARGO_ID"                ,
				"$transportType.PARCEL_NO"               ,
				"$pdVoyage.LIFTING_ACCOUNT"         ,
				"$storage.PRODUCT as PRODUCT_TYPE"    ,
// 				"$transportType.QTY_TYPE as MEASURED_ITEM"  ,
				\DB::raw("ifnull($transportType.ADJUSTED_QUANTITY,$transportType.QUANTITY)  as ITEM_VALUE"),
				"$transportType.QUANTITY_UOM as ITEM_UOM"       ,
				"$transportType.BEGIN_LOADING_TIME as DATE_TIME"  ,
				// 				'COMMENT'        => 'null 'COMMENT'             ,
		];
	}
}
