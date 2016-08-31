<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdTransportShipDetail;
use App\Models\PdVoyage;
use App\Models\ShipCargoBlmr;
use App\Models\Storage;
use App\Models\PdShipPortInformation;
use Illuminate\Http\Request;

class VoyageMarineController extends CodeController {
    
    public function getFirstProperty($dcTable){
    	if ($dcTable==PdTransportShipDetail::getTableName()) {
			return  ['data'=>$dcTable,'title'=>'BL/MR','width'=> 100];
    	}
    	return null;
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$storage_id		= $postData['Storage'];
    	$date_end 		= $postData['date_end'];
    	$date_end 		= \Helper::parseDate($date_end);
    	
    	$mdlName 		= $postData[config("constants.tabTable")];
    	$mdl 			= "App\Models\\$mdlName";
    	
    	$pdVoyage 		= PdVoyage::getTableName();
    	$dataSet = $mdl::join($pdVoyage,
			    			"$dcTable.VOYAGE_ID",
			    			'=',
			    			"$pdVoyage.ID")
		    			->whereDate("$pdVoyage.SCHEDULE_DATE",'>=',$occur_date)
    					->whereDate("$pdVoyage.SCHEDULE_DATE",'<=',$date_end)
				    	->where("$pdVoyage.STORAGE_ID",'=',$storage_id)
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
    
    
    public function genBLMR(Request $request){
    	$postData 				= $request->all();
    	$pid					= $postData['id'];
    	
    	$shipCargoBlmr 	= ShipCargoBlmr::where("PARENT_ID",'=',$pid)->where("PARENT_TYPE",'=','S')->first();
    	$xid 			= $shipCargoBlmr?$shipCargoBlmr->ID:0;
    	 
    	$results = \DB::transaction(function () use ($xid,$pid){
    				$pdTransportShipDetail 			= PdTransportShipDetail::getTableName();
    				$shipCargoBlmr 					= ShipCargoBlmr::getTableName();
    				
			    	if($xid>0){
// 			    			\DB::enableQueryLog();
			    			ShipCargoBlmr::join($pdTransportShipDetail,
			    								"$pdTransportShipDetail.ID",
								    			'=',
								    			"$shipCargoBlmr.PARENT_ID")
							    			->where("$shipCargoBlmr.ID",$xid)
							    			->update(array(
							    					"$shipCargoBlmr.ITEM_VALUE" => \DB::raw("$pdTransportShipDetail.RECEIPT_QTY"),
							    					"$shipCargoBlmr.ITEM_UOM" 	=> \DB::raw("$pdTransportShipDetail.QTY_UOM"),
							    					"$shipCargoBlmr.DATE_TIME" 	=> \DB::raw("$pdTransportShipDetail.ARRIVAL_TIME"),
							    			));
// 			    			\Log::info(\DB::getQueryLog());
	    					return 'Success(only update exist rows)';
			    	}
			    	else{
			    		$pdVoyage 				= PdVoyage::getTableName();
			    		$storage 				= Storage::getTableName();
			    		 
			    		PdTransportShipDetail::join($pdVoyage,
							    				"$pdVoyage.ID",
							    				'=',
							    				"$pdTransportShipDetail.VOYAGE_ID")
						    				->join($storage,
					    						"$storage.ID",
					    						'=',
					    						"$pdVoyage.STORAGE_ID")
						    				->where("$pdTransportShipDetail.ID",$pid)
						    				->select([
					    						\DB::raw("$pid as PARENT_ID") ,
						    					\DB::raw("'S' as PARENT_TYPE"),
						    					"$pdVoyage.CARRIER_ID"              ,
						    					"$pdTransportShipDetail.VOYAGE_ID"               ,
						    					"$pdTransportShipDetail.DEPART_PORT as PORT_ID" ,
						    					"$pdTransportShipDetail.CARGO_ID"                ,
						    					"$pdTransportShipDetail.PARCEL_NO"               ,
						    					"$pdVoyage.LIFTING_ACCOUNT"         ,
						    					"$storage.PRODUCT as PRODUCT_TYPE"    ,
						    					"$pdTransportShipDetail.QTY_TYPE as MEASURED_ITEM"  ,
						    					"$pdTransportShipDetail.RECEIPT_QTY as ITEM_VALUE" ,
						    					"$pdTransportShipDetail.QTY_UOM as ITEM_UOM"       ,
						    					"$pdTransportShipDetail.ARRIVAL_TIME as DATE_TIME"  ,
// 						    					'COMMENT'        => 'null 'COMMENT'             ,
						    				])
						    				->chunk(10, function($rows) {
						    					ShipCargoBlmr::insert($rows->toArray());
						    				});
				    	return 'Success!';
			    	}
			    	
    	});
    	return response()->json($results);
    }
}
