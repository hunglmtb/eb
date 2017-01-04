<?php

namespace App\Http\Controllers;
use App\Models\CodeInjectPoint;
use App\Models\CodeProductType;
use App\Models\CodeVolUom;
use App\Models\Keystore;
use App\Models\KeystoreInjectionPoint;
use App\Models\KeystoreInjectionPointChemical;
use App\Models\KeystoreInjectionPointDay;
use App\Models\KeystoreStorage;
use App\Models\KeystoreStorageDataValue;
use App\Models\KeystoreTank;
use App\Models\KeystoreTankDataValue;

class ChemicalController extends CodeController {
    
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'Keystore Tank','width'=>230];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){

    	$object_type 					= $postData['CodeInjectPoint'];
    	$product_type 					= 0;
    	$keystoreStorage 				= KeystoreStorage::getTableName();
    	$codeProductType 				= CodeProductType::getTableName();
    	
    	if ($dcTable==KeystoreInjectionPointChemical::getTableName()) {
    		$objectTypeName				= CodeInjectPoint::find($object_type)->CODE;
    		$objectTypeModel			= \Helper::getModelName ( $objectTypeName, '_' );
    		$objectTypeTable			= $objectTypeModel::getTableName();
    		$keystoreInjectionPoint 	= KeystoreInjectionPoint::getTableName();
    		$codeVolUom					= CodeVolUom::getTableName();
    		$keystore					= Keystore::getTableName();
    		$keystoreInjectionPointDay	= KeystoreInjectionPointDay::getTableName();
    		
    		$wheres						= [];
    		if ($objectTypeModel=="Facility") $wheres["Facility"]	= $facility_id;
    		
    		$dataSet	= KeystoreInjectionPointChemical::join($keystoreInjectionPoint, function($join) use ($keystoreInjectionPoint,$dcTable,$object_type){
									    		$join->on("$keystoreInjectionPoint.ID", '=', "$dcTable.INJECTION_POINT_ID");
									    		$join->where("$keystoreInjectionPoint.OBJECT_TYPE",'=',$object_type);
    										})
						    				->join($objectTypeTable,"$keystoreInjectionPoint.OBJECT_ID","=","$objectTypeTable.ID")
						    				->join($keystore,"$dcTable.KEYSTORE_ID","=","$keystore.ID")
						    				->join($codeVolUom,"$keystoreInjectionPoint.QTY_UOM","=","$codeVolUom.ID")
						    				->where($wheres)
						    				->leftJoin($keystoreInjectionPointDay, function($join) use ($keystoreInjectionPointDay,$dcTable,$occur_date){
						    					$join->on("$dcTable.INJECTION_POINT_ID", '=', "$keystoreInjectionPointDay.INJECTION_POINT_ID");
						    					$join->on("$dcTable.KEYSTORE_ID", '=', "$keystoreInjectionPointDay.KEYSTORE_ID");
						    					$join->where("$keystoreInjectionPointDay.OCCUR_DATE",'=',$occur_date);
						    				})
						    				->select(
								    			"$keystoreInjectionPointDay.ID as DT_RowId",
								    			"$dcTable.ID as $dcTable",
								    			"$dcTable.INJECTION_POINT_ID",
								    			"$dcTable.KEYSTORE_ID",
						    					"$objectTypeTable.NAME as OBJECT_NAME",
								    			"$keystoreInjectionPoint.OBJECT_ID",
								    			"$keystoreInjectionPoint.MIN_QTY_DAY",
								    			"$keystoreInjectionPoint.MAX_QTY_DAY",
								    			"$keystoreInjectionPoint.RECOMMEND_QTY_DAY",
								    			"$keystore.NAME as KEYSTORE_NAME",
								    			"$codeVolUom.NAME as UOM_CODE",
								    			"$keystoreInjectionPointDay.INJECTED_VOL"
								    			)
								    		->get();
    	}
    	else{
	    	switch ($dcTable) {
	    		case KeystoreTankDataValue::getTableName():
	    			$mainModel		= "\App\Models\KeystoreTank";
	    			$joinByColumn	= "KEYSTORE_TANK_ID";
	    		break;
	    		case KeystoreStorageDataValue::getTableName():
	    			$mainModel		= "\App\Models\KeystoreStorage";
	    			$joinByColumn	= "KEYSTORE_STORAGE_ID";
    			break;
	    		default:
	    		break;
	    	}
	    	if ($mainModel&&$joinByColumn) {
	    		$keystoreStorage	= $mainModel::getTableName();
	    		$where = [
	    				"$keystoreStorage.FACILITY_ID"=>$facility_id,
	    				"$keystoreStorage.FDC_DISPLAY"=>1,
	    		];
	    		if ($product_type>0) $where["$keystoreStorage.PRODUCT"] = $product_type;
	    		
		    	$dataSet = $mainModel::join($codeProductType,"$keystoreStorage.PRODUCT",'=',"$codeProductType.ID")
						    	->where($where)
						    	->whereDate("$keystoreStorage.START_DATE",'<=',$occur_date)
						    	->leftJoin($dcTable, function($join) use ($keystoreStorage,$dcTable,$occur_date,$joinByColumn){
						    		$join->on("$keystoreStorage.ID", '=', "$dcTable.$joinByColumn");
						    		$join->where("$dcTable.OCCUR_DATE",'=',$occur_date);
						    	})
						    	->select(
						    			"$dcTable.*",
						    			"$keystoreStorage.ID as $joinByColumn",
						    			"$dcTable.ID as DT_RowId",
						    			"$keystoreStorage.NAME as $dcTable",
						    			"$keystoreStorage.PRODUCT as FL_FLOW_PHASE",
						    			"$codeProductType.NAME as PHASE_NAME"
						    			)
		 		    			->orderBy("$dcTable")
		 		    			->orderBy("$keystoreStorage.PRODUCT")
		 		    			->get();
	    	}
    	}
    	//      	\DB::enableQueryLog();
    	//  		\Log::info(\DB::getQueryLog());
    	
    	return ['dataSet'=>$dataSet];
    }
}
