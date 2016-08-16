<?php
namespace App\Http\Controllers\Contract;

use App\Http\Controllers\CodeController;
use App\Models\pdCodeContractAttribute;
use App\Models\PdContractData;
use App\Models\PdContractTemplateAttribute;
use Illuminate\Http\Request;

class ContractTemplateController extends CodeController {
    
    public function getFirstProperty($dcTable){
//     	$width = $dcTable==PdContractData::getTableName()?40:90;
		return  ['data'=>$dcTable,'title'=>'','width'=> 50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    		
    	$date_end 		= $postData['date_end'];
    	$date_end 		= \Helper::parseDate($date_end);
    	
    	$mdlName 		= $postData[config("constants.tabTable")];
    	$mdl 			= "App\Models\\$mdlName";
    	$pdCodeContractAttribute= PdCodeContractAttribute::getTableName();
    	 
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::whereDate("$dcTable.EFFECTIVE_DATE",'>=',$occur_date)
    					->whereDate("$dcTable.EFFECTIVE_DATE",'<=',$date_end)
				    	->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*") 
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet];
    }
    
	public function loadAttributes(Request $request){
    	$postData 				= $request->all();
    	$id 					= $postData['id'];
    	$pdCodeContractAttribute= PdCodeContractAttribute::getTableName();
    	$results 				= $this->getProperties($pdCodeContractAttribute);
    	
    	$dataSet 				= $this->getContractData($id,$results['properties']);
	    $results['dataSet'] 	= $dataSet;
	     
    	return response()->json(['PdContractTemplateAttribute' => $results]);
	}
	
    
    public function getContractData($id,$properties){
    	$pdContractTemplateAttribute	= PdContractTemplateAttribute::getTableName();
    	$pdCodeContractAttribute		= PdCodeContractAttribute::getTableName();
    	
    	$dataSet 						= PdContractTemplateAttribute
    										::join($pdCodeContractAttribute,
									    			"$pdContractTemplateAttribute.ATTRIBUTE",
									    			'=',
									    			"$pdCodeContractAttribute.ID")
							    			->where("$pdContractTemplateAttribute.CONTRACT_TEMPLATE",'=',$id)
							    			->where("$pdContractTemplateAttribute.ACTIVE",'=',1)
							    			->select(
							    					"$pdContractTemplateAttribute.ID as DT_RowId",
			 				    					"$pdContractTemplateAttribute.ID",
			 				    					"$pdContractTemplateAttribute.ID as $pdCodeContractAttribute",
			 				    					"$pdContractTemplateAttribute.CONTRACT_TEMPLATE",
			 				    					"$pdContractTemplateAttribute.ATTRIBUTE",
							    					"$pdCodeContractAttribute.NAME",
							    					"$pdCodeContractAttribute.CODE"
							    					)
					    					->get();
    	return $dataSet;
    }
}
