<?php
namespace App\Http\Controllers\Contract;

use App\Http\Controllers\CodeController;
use App\Models\PdContractData;
use App\Models\pdCodeContractAttribute;
use App\Models\PdContractTemplateAttribute;
use App\Models\PdContractQtyFormula;
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
 		    			
    	return ['dataSet'=>$dataSet];
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
	
	/* public function saveDetail(Request $request){
		return $this->save($request);
	} */
    
    
    public function getContractData($id,$templateId,$properties){
    	$pdContractData					= PdContractData::getTableName();
    	$pdContractTemplateAttribute	= PdContractTemplateAttribute::getTableName();
    	$pdCodeContractAttribute		= PdCodeContractAttribute::getTableName();
    	$pdContractQtyFormula			= PdContractQtyFormula::getTableName();
    	
    	$contractDataSet = PdContractData::join($pdCodeContractAttribute,
					    			"$pdContractData.ATTRIBUTE_ID",
					    			'=',
					    			"$pdCodeContractAttribute.ID")
				    			->leftJoin($pdContractQtyFormula,
				    					"$pdCodeContractAttribute.FORMULA_ID",
				    					'=',
				    					"$pdContractQtyFormula.ID")
				    			->where("$pdContractData.CONTRACT_ID",'=',$id)
				    			->select(
				    					"$pdContractData.*",
				    					"$pdContractData.CONTRACT_ID as CONTRACT_ID_INDEX",
				    					"$pdContractData.ATTRIBUTE_ID as ATTRIBUTE_ID_INDEX",
				    					"$pdCodeContractAttribute.ID as DT_RowId",
 				    					"$pdCodeContractAttribute.ID as $pdContractData",
				    					"$pdCodeContractAttribute.NAME as CONTRACT_ID",
				    					"$pdCodeContractAttribute.CODE as ATTRIBUTE_ID",
 				    					"$pdCodeContractAttribute.ID as ID",
				    					"$pdContractQtyFormula.NAME as FORMULA"
				    					)
		    					->get();
		    					
		    					
    	$selects = ["$pdCodeContractAttribute.ID as DT_RowId",
	    			"$pdCodeContractAttribute.ID as $pdContractData",
	    			"$pdCodeContractAttribute.NAME as CONTRACT_ID",
	    			"$pdCodeContractAttribute.CODE as ATTRIBUTE_ID",
	    			"$pdCodeContractAttribute.ID",
				    "$pdCodeContractAttribute.FORMULA_ID as FORMULA",
    				"$pdCodeContractAttribute.ID as ATTRIBUTE_ID_INDEX",
    				"$pdContractQtyFormula.NAME as FORMULA",
    				\DB::raw("$id as CONTRACT_ID_INDEX"),
    	];
    	
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
								    			->leftJoin($pdContractQtyFormula,
								    					"$pdCodeContractAttribute.FORMULA_ID",
								    					'=',
								    					"$pdContractQtyFormula.ID")
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
