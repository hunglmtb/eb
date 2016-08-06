<?php
namespace App\Http\Controllers\Contract;

use App\Http\Controllers\CodeController;
use Illuminate\Http\Request;
use App\Models\PdContractData;
use App\Models\PdContractTemplate;
use App\Models\PdContractTemplateAttribute;

class ContractDataController extends CodeController {
    
    public function getFirstProperty($dcTable){
//     	$width = $dcTable==PdContractData::getTableName()?40:90;
		return  ['data'=>$dcTable,'title'=>'','width'=> 50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    		
    	$date_end 		= $postData['date_end'];
    	$date_end 		= \Helper::parseDate($date_end);
    	
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::whereDate("$dcTable.BEGIN_DATE",'>=',$occur_date)
    					->whereDate("$dcTable.BEGIN_DATE",'<=',$date_end)
    					/* ->whereDate("$dcTable.END_DATE",'>=',$occur_date)
    					->whereDate("$dcTable.END_DATE",'<=',$date_end) */
				    	->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*") 
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    	];
    }
    
	public function loadDetail(Request $request){
    	$postData 				= $request->all();
    	$id 					= $postData['id'];
     	$templateId 			= $postData['templateId'];
     	
    	$contractDetail 		= PdContractData::getTableName();
    	$results 				= $this->getProperties($contractDetail);
    	
    	$dataSet 				= $this->getContractData($id,$templateId);
	    $results['dataSet'] 	= $dataSet;
	    
    	return response()->json(['PdContractData' => $results]);
	}
    
    
    public function getContractData($id,$templateId){
    	return [];
    	
    	$sSQL="SELECT b.CODE,
    	b.NAME,
    	b.ID
    	FROM PD_CONTRACT_TEMPLATE_ATTRIBUTE a,
    	PD_CODE_CONTRACT_ATTRIBUTE b
    	WHERE a.CONTRACT_TEMPLATE=$templateId
    	and a.ACTIVE=1
    	and b.ID=a.ATTRIBUTE ";
    	
    	$pdContractTemplateAttribute	= PdContractTemplateAttribute::getTableName();
    	$pdCodeContractAttribute		= PdCodeContractAttribute::getTableName();
    	$dataSet = PdContractTemplateAttribute::join($pdCodeContractAttribute, 
									    			"$pdContractTemplateAttribute.ATTRIBUTE", 
									    			'=', 
									    			"$pdCodeContractAttribute.ID")
										    	->where("$pdContractTemplateAttribute.CONTRACT_TEMPLATE",'=',$templateId)
										    	->where("$pdContractTemplateAttribute.ACTIVE",'=',1)
										    	->select(
										    			"$pdCodeContractAttribute.ID",
										    			"$pdCodeContractAttribute.NAME",
										    			"$pdCodeContractAttribute.CODE"
										    			)
										    	->get();
    	return $dataSet;
    }
}
