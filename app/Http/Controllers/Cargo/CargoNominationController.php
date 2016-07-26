<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdCargoNomination;
use App\Models\PdCargo;
use App\Models\Storage;
use Illuminate\Http\Request;

class CargoNominationController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->extraDataSetColumns = [	'TRANSIT_TYPE'	=>	[	'column'	=>'PD_TRANSIT_CARRIER_ID',
																'model'		=>'PdTransitCarrier'],
		];
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>90];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	
    	$date_end 		= $postData['date_end'];
    	$date_end 		= \Helper::parseDate($date_end);
    	$storage_id 	= $postData['Storage'];
    	 
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
    	$pdCargo = PdCargo::getTableName();
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::join($pdCargo,function ($query) use ($pdCargo,$storage_id,$dcTable) {
								    		$query->on("$dcTable.CARGO_ID",'=',"$pdCargo.ID")
						    				->where("$pdCargo.STORAGE_ID",'=',$storage_id) ;
			    		})
    					->whereDate("$dcTable.REQUEST_DATE",'<=',$date_end)
    					->whereDate("$dcTable.REQUEST_DATE",'>=',$occur_date)
    					->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*"
    							) 
   		    			->orderBy("$dcTable")
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	$extraDataSet 	= $this->getExtraDataSet($dataSet);
    	 
    	return ['dataSet'		=> $dataSet,
    			'extraDataSet'	=> $extraDataSet
    	];
    }
    
	public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde = null){
    	$data = null;
    	switch ($sourceColumn) {
    		case 'TRANSIT_TYPE':
		    	$targetModel = $extraDataSetColumn['model'];
		    	$targetEloquent = "App\Models\\$targetModel";
		    	$data = $targetEloquent::where('TRANSIT_TYPE','=',$sourceColumnValue)
		    							->select("ID as value",
									    		"NAME as text",
		    									"ID",
		    									"NAME",
		    									"CODE"
		    									)
		    							->get();
    			break;
    	}
    	return $data;
    }
    
    public function confirm(Request $request){
    	$postData 			= $request->all();
    	$cargo_id 			= $postData['cargoId'];
    	$pdCargo			= PdCargo::getTableName();
    	$pdCargoNomination 	= PdCargoNomination::getTableName();
    	/* $checkNominated		= PdCargo::join($pdCargoNomination,"$pdCargoNomination.CARGO_ID", '=', "$pdCargo.ID")
								    	->where("$pdCargo.ID",'=',$cargo_id)
								    	->select("$pdCargo.NAME")
								    	->first(); */
    	 
    	$pdCargoNomination		= PdCargoNomination::firstOrNew(["CARGO_ID"=>$cargo_id]);
    	if($pdCargoNomination){
    		if($pdCargoNomination->exists){
    			$result = 	['code'		=> 'EXIST',
    						'message'	=> "Cargo id $cargo_id has been nominated already."];
    		}
    		else{
    			$cargo		= PdCargo::find($cargo_id);
    			if ($cargo) {
	    			$values = [
	    					"CARGO_ID"			=> $cargo_id,
	    					"REQUEST_DATE"		=> $cargo->REQUEST_DATE,
	    					"REQUEST_QTY"		=> $cargo->REQUEST_QTY,
	    					"REQUEST_QTY_UOM"	=> $cargo->REQUEST_UOM,
	    			];
	    			$insertId = $pdCargoNomination->fill($values)->save();
		    		if ($insertId>0) {
			    		$result = 	['code'		=> 'DONE',
			    					'message'	=> "Cargo id $cargo_id is nominated successfully!"];
		    		}
		    		else $result = 	['code'		=> 'ERROR',
			    					'message'	=> "unsuccessfully!"];
    			}
    			else $result = 	['code'		=> 'NOT_EXIST',
			    				 'message'	=> "Cargo id $cargo_id not exist!"];
    		}
    	}
    	else $result = 	['code'		=> 'ERROR',
    					'message'	=> "unsuccessfully!"];
    	
    	return response()->json($result);
    }
}
