<?php

namespace App\Http\Controllers;
use App\Models\KeystoreStorage;
use App\Models\CodeProductType;

class ChemicalController extends CodeController {
    
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'Keystore Tank','width'=>230];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){

    	$object_type = $postData['CodeInjectPoint'];
    	$product_type = 0;
    	
    	$keystoreStorage 	= KeystoreStorage::getTableName();
    	$codeProductType 	= CodeProductType::getTableName();

    	$where = [
    			"$keystoreStorage.FACILITY_ID"=>$facility_id,
    			"$keystoreStorage.FDC_DISPLAY"=>1,
    	];
    	
    	if ($product_type>0) $where["$keystoreStorage.PRODUCT"] = $product_type;
    	//      	\DB::enableQueryLog();
    	$dataSet = KeystoreStorage::join($codeProductType,"$keystoreStorage.PRODUCT",'=',"$codeProductType.ID")
				    	->where($where)
				    	->whereDate("$keystoreStorage.START_DATE",'<=',$occur_date)
				    	->leftJoin($dcTable, function($join) use ($keystoreStorage,$dcTable,$occur_date){
				    		$join->on("$keystoreStorage.ID", '=', "$dcTable.KEYSTORE_STORAGE_ID");
				    		$join->where("$dcTable.OCCUR_DATE",'=',$occur_date);
				    	})
				    	->select(
				    			"$keystoreStorage.ID as X_FL_ID",
				    			"$keystoreStorage.ID as DT_RowId",
				    			"$keystoreStorage.NAME as $dcTable",
				    			"$keystoreStorage.PRODUCT as FL_FLOW_PHASE",
				    			"$codeProductType.NAME as PHASE_NAME",
				    			"$dcTable.*"
				    			)
 		    			->orderBy("$dcTable")
 		    			->orderBy("$keystoreStorage.PRODUCT")
 		    			->get();
    	//  		\Log::info(\DB::getQueryLog());
    	
    	return ['dataSet'=>$dataSet];
    }
}
