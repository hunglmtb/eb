<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AllocatePlanController extends CodeController {
	
	public function getWorkingTable($postData){
		return null;
	}
	
	public function getPreFix($source_type){
		$obj_id_prefix	=	$source_type;
		$field_prefix	=	$source_type;
		if($source_type=="ENERGY_UNIT"){
			$obj_id_prefix	="EU";
		}
		else if($source_type=="FLOW") {
			$obj_id_prefix="FL";
		}
		
		if($source_type=="FLOW"||$source_type=="ENERGY_UNIT"){
			$field_prefix	= $obj_id_prefix."_DATA";
		}
		return $field_prefix.'_';
	}
	
	public function getObjectTable($src,$data_source){
		$table			=	$src."_DATA_".$data_source;
		$mdl 			= 	\Helper::getModelName($table);
 		$mdl 			= 	"App\Models\\".$mdl;
		return $mdl;
	}
	
    public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		$source_type 	= 	$postData['IntObjectTypeName'];
		$prefix 		= 	$this->getPreFix($source_type);
		$properties = collect([
					(object)['data' =>	'OCCUR_DATE',		'title' => 'Occur Date',	'width'	=>	100,'INPUT_TYPE'=>3,	'DATA_METHOD'=>2,'FIELD_ORDER'=>1],
					(object)['data' =>	$prefix."GRS_VOL"	,'title' => 'Gross Vol'	,	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>2],
					(object)['data' =>	$prefix."GRS_MASS"	,'title' => 'Gross Mass',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>3],
					(object)['data' =>	$prefix."GRS_ENGY"	,'title' => 'Gross Energy',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>4],
					(object)['data' =>	$prefix."GRS_PWR"	,'title' => 'Gross Power',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>5],
		]);
		return ['properties'	=>$properties];
	}
	
	public function getModelName($mdlName,$postData) {
		$source_type 	= 	$postData['IntObjectTypeName'];
		$table			=	$source_type."_DATA_PLAN";
		$tableName		= 	strtolower ( $table );
		$mdlName 		= 	\Helper::camelize ( $tableName, '_' );
		return $mdlName;
	}
	
	protected function deleteData($postData) {
		if (array_key_exists ('deleteData', $postData )) {
			$deleteData = $postData['deleteData'];
			
			$flow_phase 	= 	$postData['ExtensionPhaseType'];
			$object_id 		= 	$postData['ObjectName'];
			$source_type 	= 	$postData['IntObjectTypeName'];
			$occur_date 	= 	$postData['date_begin'];
// 			$occur_date 	= 	Carbon::parse($occur_date);
    		$occur_date		= 	\Helper::parseDate($occur_date);
			$date_from		=	$occur_date;
			$date_to 		= 	$postData['date_end'];
			$date_to		= 	\Helper::parseDate($date_to);
// 			$date_to 		= 	Carbon::parse($date_to);
				
			$obj_id_prefix	=	$source_type;
			$field_prefix	=	$source_type;
			$idField		= 	$source_type;
			
			foreach($deleteData as $mdlName => $mdlData ){
				if ($mdlData) {
					$modelName = $this->getModelName($mdlName,$postData);
					$mdl = "App\Models\\".$modelName;
					
					$where = [];
					if($source_type=="ENERGY_UNIT"){
						$obj_id_prefix				="EU";
						$idField 					= $obj_id_prefix;
						$where["FLOW_PHASE" ] 		= $flow_phase;
					}
					else if($source_type=="FLOW") {
						$obj_id_prefix="FL";
					}
					
					if($source_type=="FLOW"||$source_type=="ENERGY_UNIT"){
						$field_prefix=$obj_id_prefix."_DATA";
					}
					$where["$idField"."_ID" ] 	= $object_id;
					
					$mdl::where($where)
						->whereBetween('OCCUR_DATE', [$date_from,$date_to])
						->delete();
				}
			}
		}
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){

    	$flow_phase 	= 	$postData['ExtensionPhaseType'];
    	$object_id 		= 	$postData['ObjectName'];
		$source_type 	= 	$postData['IntObjectTypeName'];
    	$date_from		=	$occur_date;
    	$date_to 		= 	$postData['date_end'];
    	$date_to 		= 	Carbon::parse($date_to);
    	
    	if($object_id<=0)return response("Object Name $object_id not okay", 401);
    	
    	$obj_id_prefix	=	$source_type;
    	$field_prefix	=	$source_type;
    	$idField		= 	$source_type;
    	$modelName 		= 	$this->getModelName($source_type,$postData);
 		$mdl 			= 	"App\Models\\".$modelName;
    	 
    	$selects = ["ID as DT_RowId","OCCUR_DATE"];
    	$where = [];
    	if($source_type=="ENERGY_UNIT"){
			$obj_id_prefix				="EU";
			$idField 					= $obj_id_prefix;
			$where["FLOW_PHASE" ] 		= $flow_phase;
			$selects[] 					= "FLOW_PHASE as EU_FLOW_PHASE";
			$selects[] 					= "EU_ID";
    	}
		else if($source_type=="FLOW") {
			$obj_id_prefix="FL";
			$selects[] 		= "FLOW_ID";
		}
		else $selects[] 		= "$idField"."_ID";
		
		if($source_type=="FLOW"||$source_type=="ENERGY_UNIT"){
			$field_prefix=$obj_id_prefix."_DATA";
		}
		
		$selects[] 					= "$field_prefix"."_GRS_VOL";
		$selects[] 					= "$field_prefix"."_GRS_MASS";
		$selects[] 					= "$field_prefix"."_GRS_ENGY";
		$selects[] 					= "$field_prefix"."_GRS_PWR";
		$where["$idField"."_ID" ] 	= $object_id;
		//     	\DB::enableQueryLog();
		$dataSet = $mdl::where($where)
						->whereBetween('OCCUR_DATE', [$date_from,$date_to])
						->select($selects)
						->orderBy('OCCUR_DATE')
						->get();
				//  		\Log::info(\DB::getQueryLog());
    	return ['dataSet'=>$dataSet];
    }
}
