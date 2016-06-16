<?php

namespace App\Http\Controllers;
use App\Models\Equipment;


class EquipmentController extends CodeController {
    
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'Equipment','width'=>180];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){

    	$equip_group = $postData['EquipmentGroup'];
    	$equip_type = $postData['CodeEquipmentType'];
    	
    	$equipment 	= Equipment::getTableName();
    	
    	$where = ["$equipment.FACILITY_ID"=>$facility_id];
    	if ($equip_group>0) $where["$equipment.EQUIPMENT_GROUP"] = $equip_group;
    	if ($equip_type>0) $where["$equipment.EQUIPMENT_TYPE"] = $equip_type;
    	//      	\DB::enableQueryLog();
    	$dataSet = Equipment::where($where)
    					->leftJoin($dcTable, function($join) use ($equipment,$dcTable,$occur_date){
				    		$join->on("$equipment.ID", '=', "$dcTable.EQUIPMENT_ID");
				    		$join->where("$dcTable.OCCUR_DATE",'=',$occur_date);
				    	})
				    	->select(
				    			"$equipment.ID as DT_RowId",
				    			"$equipment.NAME as $dcTable",
				    			"$equipment.FUEL_TYPE",
				    			"$equipment.GHG_REL_TYPE",
// 				    			"$equipment.NAME as FL_NAME",
				    			"$dcTable.*",
				    			"$equipment.ID as EQUIPMENT_ID"
// 				    			"$equipment.FUEL_TYPE as EQP_FUEL_CONS_TYPE",
// 				    			"$equipment.GHG_REL_TYPE as EQP_GHG_REL_TYPE"
				    			)
// 		    			->exclude(["$dcTable.EQP_FUEL_CONS_TYPE"])
 		    			->orderBy("$dcTable")
		    			->get();
    	//  		\Log::info(\DB::getQueryLog());
    	
    	return ['dataSet'=>$dataSet];
    }
}
