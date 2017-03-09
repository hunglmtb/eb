<?php
namespace App\Http\Controllers\Cargo;

use App\Exceptions\DataInputException;
use App\Models\PdCargo;
use App\Models\PdCargoLoad;
use App\Models\PdCargoNomination;
use App\Models\PdCargoSchedule;
use App\Models\PdCargoUnload;
use App\Models\PdTransitCarrier;
use App\Models\PdVoyage;
use App\Models\PdVoyageDetail;
use App\Models\Storage;
use App\Models\TerminalTimesheetData;
use Illuminate\Http\Request;

class CargoNominationController extends CargoAdminController {
    
    public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>90];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	
    	$date_end 		= $postData['date_end'];
    	$date_end		= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	$storage_id 	= $postData['Storage'];
    	$mdlName 		= $postData[config("constants.tabTable")];
    	$mdl 			= "App\Models\\$mdlName";
    	$pdCargo 		= PdCargo::getTableName();
    	
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
// 		    	$targetModel = $extraDataSetColumn['model'];
		    	$targetEloquent = "App\Models\PdTransitCarrier";
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
    	$postData 					= $request->all();
    	$id 						= $postData['nominationId'];
    	$pdCargo					= PdCargo::getTableName();
    	$pdCargoNomination 			= PdCargoNomination::getTableName();
    	 
    	$nomi_row					= PdCargoNomination::find($id);
    	if (!$nomi_row) throw new DataInputException ( "cargo nomination id $id not existed" );
    	
    	$result = \DB::transaction(function () use ($nomi_row,$id){
    		$code						= "BEGIN";
    		$message					= "begin confirming";
    		$checkDate					= \Helper::isNullOrEmpty($nomi_row->NOMINATION_DATE);
    		$checkQty					= \Helper::isNullOrEmpty($nomi_row->NOMINATION_QTY);
    		$warning_msg				= $checkDate||$checkQty?"Request data is being copied to Nomination data":"";
    		 
    		$nomi_row->NOMINATION_DATE	= $checkDate?$nomi_row->REQUEST_DATE:$nomi_row->NOMINATION_DATE;
    		$nomi_row->NOMINATION_QTY	= $checkQty?$nomi_row->REQUEST_QTY:$nomi_row->NOMINATION_QTY;
    		$nomi_row->NOMINATION_UOM	= $checkQty?$nomi_row->REQUEST_QTY_UOM:$nomi_row->NOMINATION_UOM;
    		$nomi_row->save();
    		 
    		//--------- generate CARGO_SCHEDULE ---------------
    		$attributes 					= ['NOMINATION_ID'			=> $id];
    		$values 						= ['CARGO_ID'				=> $nomi_row->CARGO_ID,
    				'SCHEDULE_DATE'			=> $nomi_row->NOMINATION_DATE,
    				'SCHEDULE_QTY'			=> $nomi_row->NOMINATION_QTY,
    				'SCHEDULE_UOM'			=> $nomi_row->NOMINATION_UOM,
    				'PD_TRANSIT_CARRIER_ID'	=> $nomi_row->PD_TRANSIT_CARRIER_ID,
    				'CARGO_STATUS'			=> 3,
    		];
    		$pdCargoSchedule				= PdCargoSchedule::updateOrCreate($attributes,$values);
    		if ($pdCargoSchedule->wasRecentlyCreated) {
    			$pdCargoSchedule->fill(['TRANSIT_TYPE' => $nomi_row->TRANSIT_TYPE])->save();
    		}
    		
    		$code							= "GENERATING";
    		$message						= "generate CARGO_SCHEDULE done";
    		
    		//--------- generate VOYAGE --------------
    		$voyage							= PdVoyage::firstOrNew(["NOMINATION_ID"=>$id]);
    		if(!$voyage->exists){
    			$carrier_row				= PdTransitCarrier::find($nomi_row->PD_TRANSIT_CARRIER_ID);
    			if (!$carrier_row) throw new DataInputException ( "please check carrier of cargo nomination id $id" );
    			$cargo						= PdCargo::find($nomi_row->CARGO_ID);
    			$voyage_code				= $carrier_row->CODE."_#$id";
    			$voyage_name				= $carrier_row->NAME."_#$id";
    				
    			$voyage->CODE				= $voyage_code;
    			$voyage->NAME				= $voyage_name;
    			$voyage->CARRIER_ID			= $nomi_row->PD_TRANSIT_CARRIER_ID;
    			$voyage->CARGO_ID			= $nomi_row->CARGO_ID;
    			$voyage->LIFTING_ACCOUNT	= $cargo->LIFTING_ACCT;
    			$voyage->STORAGE_ID			= $cargo->STORAGE_ID;
    			$voyage->VOYAGE_NO			= $voyage_code;
    			$voyage->INCOTERM			= $nomi_row->INCOTERM;
    			$voyage->SCHEDULE_DATE		= $nomi_row->NOMINATION_DATE;
    			$voyage->ADJUSTABLE_TIME	= $nomi_row->NOMINATION_ADJ_TIME;
    			$voyage->SCHEDULE_QTY		= $nomi_row->NOMINATION_QTY;
    			$voyage->QUANTITY_TYPE		= $cargo->QUANTITY_TYPE;
    			$voyage->SCHEDULE_UOM		= $nomi_row->NOMINATION_UOM;
    			$voyage->BERTH_ID			= null;
    			$voyage->save();
    				
    			$message					= "generate PD_VOYAGE done";
    		}
    		
    		
    		$gen_cargo_load=false;
    		$gen_cargo_unload=false;
    		if($nomi_row->IS_IMPORT==1) $gen_cargo_unload=true;
    		else {
    			$gen_cargo_load=true;
    			if($nomi_row->INCOTERM==5) {
    				$gen_cargo_unload=true;//INCOTERM CODE 5 = DES
    			}
    		}
    		
    		if($gen_cargo_load){
    			$cargoLoad								= PdCargoLoad::firstOrNew(["NOMINATION_ID"=>$id]);
    			if(!$cargoLoad->exists){
    				$cargoLoad->CARGO_ID				= $nomi_row->CARGO_ID;
    				$cargoLoad->DATE_LOAD				= $nomi_row->NOMINATION_DATE;
    				$cargoLoad->LOAD_QTY				= $nomi_row->NOMINATION_QTY;
    				$cargoLoad->LOAD_UOM				= $nomi_row->NOMINATION_UOM;
    				$cargoLoad->TRANSIT_TYPE			= $nomi_row->TRANSIT_TYPE;
    				$cargoLoad->PD_TRANSIT_CARRIER_ID	= $nomi_row->PD_TRANSIT_CARRIER_ID;
    				$cargoLoad->BERTH_ID				= null;
    				$cargoLoad->CARGO_STATUS			= $nomi_row->CARGO_STATUS;
    				$cargoLoad->save();
    		
    				$message							= "generate PD_CARGO_LOAD done";
    			}
    		}
    		
    		if($gen_cargo_unload){
    			$cargoUnload								= PdCargoUnload::firstOrNew(["NOMINATION_ID"=>$id]);
    			if(!$cargoUnload->exists){
    				$cargoUnload->CARGO_ID				= $nomi_row->CARGO_ID;
    				$cargoUnload->DATE_UNLOAD			= $nomi_row->NOMINATION_DATE;
    				$cargoUnload->LOAD_QTY				= $nomi_row->NOMINATION_QTY;
    				$cargoUnload->LOAD_UOM				= $nomi_row->NOMINATION_UOM;
    				$cargoUnload->TRANSIT_TYPE			= $nomi_row->TRANSIT_TYPE;
    				$cargoUnload->PD_TRANSIT_CARRIER_ID	= $nomi_row->PD_TRANSIT_CARRIER_ID;
    				$cargoUnload->BERTH_ID				= null;
    				$cargoUnload->CARGO_STATUS			= $nomi_row->CARGO_STATUS;
    				$cargoUnload->save();
    					
    				$message							= "generate PD_CARGO_UNLOAD done";
    			}
    		}
    		$nomi_row->CARGO_STATUS	= 3;
    		$nomi_row->save();
    		$message 	="ok: $warning_msg";
    		$result 	= 	['code'		=> $code,
    						'message'	=> $message];
    		
    		return $result;
    	});
    	
    	return response()->json($result);
    }
    
    public function reset(Request $request){
    	$postData 					= $request->all();
    	$id 						= $postData['nominationId'];
    	$pdCargo					= PdCargo::getTableName();
    	$pdCargoNomination 			= PdCargoNomination::getTableName();
    
    	$nomi_row					= PdCargoNomination::find($id);
    	if (!$nomi_row) throw new DataInputException ( "cargo nomination id $id not existed" );
    	
    	$code						= "OK";
    	$message					= "reset successfully";
    	
    	TerminalTimesheetData::whereIn('PARENT_ID', function($query) use ($id){
													    $query->select('ID')
													    ->from(PdCargoLoad::getTableName())
													    ->where('NOMINATION_ID', $id);
													})
							  	->where('IS_LOAD', 1)
								->delete();
    	
		PdCargoLoad::where('NOMINATION_ID', $id)->delete();
		
		TerminalTimesheetData::whereIn('PARENT_ID', function($query) use ($id){
														$query->select('ID')
														->from(PdCargoUnload::getTableName())
														->where('NOMINATION_ID', $id);
								})
								->where('IS_LOAD', 0)
								->delete();
		PdCargoUnload::where('NOMINATION_ID', $id)->delete();
		
		PdVoyageDetail::whereIn('VOYAGE_ID', function($query) use ($id){
			$query->select('ID')
			->from(PdVoyage::getTableName())
			->where('NOMINATION_ID', $id);
		})
		->delete();
		PdVoyage::where('NOMINATION_ID', $id)->delete();
		
		PdCargoSchedule::where('NOMINATION_ID', $id)->delete();
    	
		$nomi_row->CARGO_STATUS	= 1;
		$nomi_row->save();
		
		$result 	= 	['code'		=> $code,
						'message'	=> $message];
    	return response()->json($result);
    }
}
