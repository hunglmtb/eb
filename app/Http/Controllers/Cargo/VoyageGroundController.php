<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Cargo\VoyageController;
use Illuminate\Http\Request;

class VoyageGroundController extends VoyageController {
    
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
	
    /* public function genBLMR(Request $request){
    	$postData 				= $request->all();
    	$pid					= $postData['id'];
    	
    	
    	
    	$xid=getOneValue("select ID from ship_cargo_blmr where PARENT_ID=$pid and PARENT_TYPE='G'");
    	$xid=getOneValue("select ID from ship_cargo_blmr where PARENT_ID=$pid and PARENT_TYPE='S'");
    	if($xid>0)
    	{
    		$sql="update `ship_cargo_blmr` x join pd_transport_ground_detail a on x.PARENT_ID=a.ID
    		set x.`ITEM_VALUE`=ifnull(a.ADJUSTED_QUANTITY,a.QUANTITY),x.`ITEM_UOM`=a.QUANTITY_UOM , x.`DATE_TIME`=a.BEGIN_LOADING_TIME where a.ID=$xid";
    		
    		$sql="update `ship_cargo_blmr` x join PD_TRANSPORT_SHIP_DETAIL a on x.PARENT_ID=a.ID
    		set x.`ITEM_VALUE`=a.RECEIPT_QTY ,x.`ITEM_UOM`=a.QTY_UOM , x.`DATE_TIME`=a.ARRIVAL_TIME where a.ID=$xid";
    	}
    	else
    	{
    		$sql="INSERT INTO `ship_cargo_blmr`(PARENT_ID,PARENT_TYPE,`CARRIER_ID`, `VOYAGE_ID`, `PORT_ID`, `CARGO_ID`, `PARCEL_NO`, `LIFTING_ACCOUNT`, `PRODUCT_TYPE`, `MEASURED_ITEM`, `ITEM_VALUE`, `ITEM_UOM`, `DATE_TIME`, `COMMENT`)
    		select $pid PARENT_ID,'G' PARENT_TYPE,
    		b.`CARRIER_ID`, a.`VOYAGE_ID`, a.ORIGIN_PORT `PORT_ID`,
    		a.`CARGO_ID`, a.`PARCEL_NO`, b.`LIFTING_ACCOUNT`, c.PRODUCT `PRODUCT_TYPE`,
    		null `MEASURED_ITEM`, ifnull(a.ADJUSTED_QUANTITY,a.QUANTITY) `ITEM_VALUE`,a.QUANTITY_UOM `ITEM_UOM`,
    		a.BEGIN_LOADING_TIME `DATE_TIME`,null `COMMENT` 
    		from pd_transport_ground_detail a, pd_voyage b, STORAGE c where a.ID=$pid and a.VOYAGE_ID=b.ID and b.STORAGE_ID=c.ID";
    		
    		$sql="INSERT INTO `ship_cargo_blmr`(PARENT_ID,PARENT_TYPE,`CARRIER_ID`, `VOYAGE_ID`, `PORT_ID`, `CARGO_ID`, `PARCEL_NO`, `LIFTING_ACCOUNT`, `PRODUCT_TYPE`, `MEASURED_ITEM`, `ITEM_VALUE`, `ITEM_UOM`, `DATE_TIME`, `COMMENT`)
    		select $pid PARENT_ID,'S' PARENT_TYPE,
    		b.`CARRIER_ID`, a.`VOYAGE_ID`, a.DEPART_PORT `PORT_ID`,
    		a.`CARGO_ID`, a.`PARCEL_NO`, b.`LIFTING_ACCOUNT`, c.PRODUCT `PRODUCT_TYPE`, 
    		a.QTY_TYPE `MEASURED_ITEM`, a.RECEIPT_QTY `ITEM_VALUE`,a.QTY_UOM `ITEM_UOM`, 
    		a.ARRIVAL_TIME `DATE_TIME`,null `COMMENT` 
    		from PD_TRANSPORT_SHIP_DETAIL a, pd_voyage b, STORAGE c where a.ID=$pid and a.VOYAGE_ID=b.ID and b.STORAGE_ID=c.ID";
    	}
    	
    	
    } */
}
