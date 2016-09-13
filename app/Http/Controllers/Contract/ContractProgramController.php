<?php
namespace App\Http\Controllers\Contract;

use App\Http\Controllers\CodeController;
use App\Models\PdContractCalculation;
use App\Models\PdContractData;
use App\Models\PdContractQtyFormula;
use App\Models\PdContractYear;
use Illuminate\Http\Request;

class ContractProgramController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->detailModel = "PdContractData";
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'Actions','width'=> 50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    		
    	$date_end 		= $postData['date_end'];
    	$date_end 		= \Helper::parseDate($date_end);
    	
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::whereDate("$dcTable.START_DATE",'>=',$occur_date)
    					->whereDate("$dcTable.START_DATE",'<=',$date_end)
				    	->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*") 
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet];
    }
    
    public function open(Request $request){
    	$postData 				= $request->all();
    	$code 					= $postData['code'];
    	$contract_id 			= $postData['contract_id'];
    	$storage_id 			= $postData['storage_id'];
    	 
		return view ( "front.contract.$code",
				['contract_id'		=> $contract_id,
				'storage_id'		=> $storage_id
		]);
    }
    public function calculate(Request $request){
    	$postData 				= $request->all();
    	$contract_id 			= $postData['contract_id'];
    	$formula_id 			= $postData['PdContractQtyFormula'];
    	$pdContractYear 		= PdContractYear::getTableName();
    	$pdContractCalculation 	= PdContractCalculation::getTableName();
    	
    	
    	$value = PdContractYear::join($pdContractCalculation,function ($query) use ($pdContractYear,$pdContractCalculation,$formula_id) {
     												$query->on("$pdContractCalculation.ID",'=',"$pdContractYear.CALCULATION_ID" );
													$query->where("$pdContractCalculation.FORMULA_ID",'=',$formula_id );
											})
					->where("$pdContractYear.CONTRACT_ID",$contract_id)
					->sum("$pdContractYear.FORMULA_VALUE");
    	
    	/* echo getOneValue("SELECT sum(a.FORMULA_VALUE) 
    			FROM pd_contract_year a ,
    			pd_contract_calculation b 
    			WHERE b.ID = a.CALCULATION_ID AND "
    			. "  a.CONTRACT_ID =  ".$contract_id . " 
    			and b.FORMULA_ID=$formula_id"); */
    
    	return response()->json($value);
    }
}
