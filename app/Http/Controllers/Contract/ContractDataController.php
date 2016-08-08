<?php
namespace App\Http\Controllers\Contract;

use App\Http\Controllers\CodeController;
use App\Models\PdContractData;
use App\Models\pdCodeContractAttribute;
use App\Models\PdContractTemplateAttribute;
use Illuminate\Http\Request;

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
    	
    	$dataSet 				= $this->getContractData($id,$templateId,$results['properties']);
	    $results['dataSet'] 	= $dataSet;
	    
    	return response()->json(['PdContractData' => $results]);
	}
    
    
    public function getContractData($id,$templateId,$properties){
    	/* return [];
    	
    	$sSQL="SELECT b.CODE,
    	b.NAME,
    	b.ID
    	FROM PD_CONTRACT_TEMPLATE_ATTRIBUTE a,
    	PD_CODE_CONTRACT_ATTRIBUTE b
    	WHERE a.CONTRACT_TEMPLATE=$templateId
    	and a.ACTIVE=1
    	and b.ID=a.ATTRIBUTE "; */
    	
    	/* $sSQL="SELECT b.CODE,
    	b.NAME,
    	b.FORMULA_ID,
    	a.*,
    	b.ID as ATTRIBUTE_ID
    	FROM PD_CONTRACT_DATA a,
    	PD_CODE_CONTRACT_ATTRIBUTE b
    	WHERE a.CONTRACT_ID  = $vid
    	AND a.ATTRIBUTE_ID = b.ID"; */
    	
    	$pdContractData					= PdContractData::getTableName();
    	$pdContractTemplateAttribute	= PdContractTemplateAttribute::getTableName();
    	$pdCodeContractAttribute		= PdCodeContractAttribute::getTableName();
    	
    	$contractDataSet = PdContractData::join($pdCodeContractAttribute,
					    			"$pdContractData.ATTRIBUTE_ID",
					    			'=',
					    			"$pdCodeContractAttribute.ID")
				    			->where("$pdContractData.CONTRACT_ID",'=',$id)
				    			->select(
				    					"$pdContractData.*",
				    					"$pdCodeContractAttribute.ID as DT_RowId",
 				    					"$pdCodeContractAttribute.ID as $pdContractData",
				    					"$pdCodeContractAttribute.NAME as CONTRACT_ID",
				    					"$pdCodeContractAttribute.CODE as ATTRIBUTE_ID",
				    					"$pdCodeContractAttribute.FORMULA_ID",
				    					"$pdCodeContractAttribute.ID as ID"
				    					)
		    					->get();
    	
		    					
    	$selects = ["$pdCodeContractAttribute.ID as DT_RowId",
	    			"$pdCodeContractAttribute.ID as $pdContractData",
	    			"$pdCodeContractAttribute.NAME as CONTRACT_ID",
	    			"$pdCodeContractAttribute.CODE as ATTRIBUTE_ID",
	    			"$pdCodeContractAttribute.ID"];
    	
    	foreach($properties as $property ){
    		$columnName = $property['data'];
    		if ($columnName!='CONTRACT_ID'&&$columnName!='ATTRIBUTE_ID'&&$columnName!='ID') {
	    		$selects[] = \DB::raw("null as $columnName");
    		}
    	}
    	
    	$templateQuery = PdContractTemplateAttribute::join($pdCodeContractAttribute, 
									    			"$pdContractTemplateAttribute.ATTRIBUTE", 
									    			'=', 
									    			"$pdCodeContractAttribute.ID")
										    	->where("$pdContractTemplateAttribute.CONTRACT_TEMPLATE",'=',$templateId)
										    	->where("$pdContractTemplateAttribute.ACTIVE",'=',1)
										    	->select($selects);
// 										    	->get();
    	
										    	
    	$existAttributes = $contractDataSet->pluck('DT_RowId');
    	if (count($existAttributes)>0) {
    		$templateQuery->whereNotIn("$pdCodeContractAttribute.ID", $existAttributes);
    	}
    	$templateDataSet = $templateQuery->get();
    	
    	$dataSet = $contractDataSet->merge($templateDataSet);
    	return $dataSet;
    }
}
