<?php
namespace App\Http\Controllers\Contract;

use App\Http\Controllers\CodeController;
use Illuminate\Http\Request;
use App\Models\PdContractDetail;

class ContractDataController extends CodeController {
    
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>90];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    		
    	$date_end 		= $postData['date_end'];
    	$date_end 		= \Helper::parseDate($date_end);
    	
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::whereDate("$dcTable.BEGIN_DATE",'<=',$date_end)
    					->whereDate("$dcTable.BEGIN_DATE",'>=',$occur_date)
    					->whereDate("$dcTable.END_DATE",'<=',$date_end)
    					->whereDate("$dcTable.END_DATE",'>=',$occur_date)
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
    	$postData 			= $request->all();
    	$id 				= $postData['id'];
     	$templateId 		= $postData['templateId'];
     	
    	$contractDetail 	= PdContractDetail::getTableName();
    	$properties 		= $this->getOriginProperties($contractDetail);
    	
    	/* $dataSet = $this->getContractDetail($id,$templateId);
	    $results = [];
    	$results['PdContractDetail'] = ['properties'	=>$properties,
	    							'dataSet'		=>$dataSet]; */
	    
    	return response()->json('keke');
//     	return response()->json($results);
	}
    
    
    public function getContractDetail($id,$templateId){
    	$defermentDetail =DefermentDetail::getTableName();
    	//     	$defermentGroupEu =DefermentGroupEu::getTableName();
    	$energyUnit =EnergyUnit::getTableName();
    	$deferment =Deferment::getTableName();
    	$dataSet = Deferment::join($energyUnit, "$deferment.DEFER_TARGET", '=', "$energyUnit.ID")
    	->leftJoin($defermentDetail, function($join) use ($defermentDetail,$deferment,$energyUnit){
    		$join->on("$defermentDetail.DEFERMENT_ID", '=', "$deferment.ID")
    		->on("$defermentDetail.EU_ID",'=',"$energyUnit.ID");
    	})
    	->where("$deferment.ID",'=',$id)
    	//well
    	->where("$deferment.DEFER_GROUP_TYPE",'=',3)
    	->select(
    			"$energyUnit.ID as ID",
    			"$energyUnit.NAME as NAME",
    			"$energyUnit.ID as DT_RowId",
    			"$deferment.DEFER_GROUP_TYPE",
    			"$defermentDetail.*"
    			)
    			->get();
    			return $dataSet;
    }
}
