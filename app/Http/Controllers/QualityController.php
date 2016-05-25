<?php

namespace App\Http\Controllers;
use App\Models\CodeQltySrcType;
use Carbon\Carbon;
use App\Models\PdVoyage;
use App\Models\PdVoyageDetail;
use App\Models\Storage;
use Illuminate\Http\Request;

class QualityController extends CodeController {
    
	/* protected $type = ['idField'=>'FLOW_ID',
			'name'=>'FLOW',
			'dateField'=>'OCCUR_DATE'
	]; */
	
	
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "QualityData";
		$this->idColumn = config("constants.qualityId");
		/* $this->phaseColumn = config("constants.flFlowPhase");
	
		$this->valueModel = "FlowDataValue";
		$this->theorModel = "FlowDataTheor"; */
		$this->isApplyFormulaAfterSaving = true;
	}
	
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	$src_type_id = $postData['CodeQltySrcType'];
    	$date_end = $postData['date_end'];
    	$date_end = Carbon::parse($date_end);
    	$filterBy = $postData['cboFilterBy'];
    	
    	$extraDataSet = [];
    	$dataSet = null;
    	$codeQltySrcType = CodeQltySrcType::getTableName();
	    $uoms = $properties['uoms'];
	    $sourceTypekey = array_search('CodeQltySrcType', array_column($uoms, 'id'));
	    $sourceTypes = $uoms[$sourceTypekey]['data'];
	    $objectType = null;
	    
    	$src_type_ids = $src_type_id==0?[1,2,3,4,5,6]:[$src_type_id];
	    $query = null;
// 	    \DB::enableQueryLog();
	    foreach($src_type_ids as $srcTypeId ){
	    	$where = ['SRC_TYPE' => $srcTypeId];
	    	switch ($srcTypeId) {
	    		case 1:						
	    		case 2:						
	    		case 3:						
	    		case 4:
	    			$objectType = $sourceTypes->find($srcTypeId);
			    	$objectType = $objectType->CODE;
		    		$cquery = $mdl::join($objectType,function ($query) use ($objectType,$facility_id,$dcTable) {
								    							$query->on("$objectType.ID",'=',"$dcTable.SRC_ID")
								    							->where("$objectType.FACILITY_ID",'=',$facility_id) ;
									})
							    	->where($where)
							    	->whereDate($filterBy, '>=', $occur_date)
							    	->whereDate($filterBy, '<=', $date_end)
							    	->select(
							    			"$dcTable.ID as $dcTable",
							    			"$dcTable.ID as DT_RowId",
							    			"$dcTable.ID",
							    			"$dcTable.*"
							    			)
					    			->orderBy($dcTable);
// 									->get();
					$query = $query==null?$cquery:$query->union($cquery);
	    			break;
		    	case 5:
		    		$objectType = $sourceTypes->find($srcTypeId);
		    		$objectType = $objectType->CODE;
		    		$storage = Storage::getTableName();
		    		$pdVoyageDetail = PdVoyageDetail::getTableName();
		    		$pdVoyage = PdVoyage::getTableName();
		    		
		    		$cquery = $mdl::join($pdVoyageDetail, "$dcTable.SRC_ID", '=', "$pdVoyageDetail.ID")
									->join($pdVoyage, "$pdVoyageDetail.VOYAGE_ID", '=', "$pdVoyage.ID")
									->join($storage,function ($query) use ($storage,$facility_id,$pdVoyage) {
											    			$query->on("$storage.ID",'=',"$pdVoyage.STORAGE_ID")
											    			->where("$storage.FACILITY_ID",'=',$facility_id) ;
						    		})
									->where($where)
						    		->whereDate($filterBy, '>=', $occur_date)
						    		->whereDate($filterBy, '<=', $date_end)
						    		->select(
						    				"$dcTable.ID as $dcTable",
						    				"$dcTable.ID as DT_RowId",
						    				"$dcTable.ID",
						    				"$dcTable.*"
						    				)
				    				->orderBy($dcTable);
// 				    				->get();
					$query = $query==null?$cquery:$query->union($cquery);
    				break;
	    		case 6:
	    			$objectType = $sourceTypes->find($srcTypeId);
	    			$objectType = $objectType->CODE;
		    		$cquery = $mdl::where($where)
						    		->whereDate($filterBy, '>=', $occur_date)
						    		->whereDate($filterBy, '<=', $date_end)
						    		->select(
						    				"$dcTable.ID as $dcTable",
						    				"$dcTable.ID as DT_RowId",
						    				"$dcTable.ID",
						    				"$dcTable.*"
						    				)
						    				->orderBy($dcTable);
// 						    				->get();
					$query = $query==null?$cquery:$query->union($cquery);
    				break;
	    	}
    	}
	    
    	if ($query!=null) {
    		$dataSet= $query->get();
    	}
//     	\Log::info(\DB::getQueryLog());
    	 
    	if ($dataSet&&$dataSet->count()>0) {
    		if ($src_type_id>0) {
    			$srcTypeData = $this->getExtraDatasetBy($objectType,$facility_id);
    			if ($srcTypeData) {
					$extraDataSet[$src_type_id] = $srcTypeData;
    			}
    		}
    		else{
				$bySrcTypes = $dataSet->groupBy('SRC_ID');
				if ($bySrcTypes) {
					foreach($bySrcTypes as $key => $srcType ){
						$srcTypeID = $srcType[0]->SRC_TYPE;
						$table = $sourceTypes->find($srcTypeID);
						$table = $table->CODE;
						$srcTypeData = $this->getExtraDatasetBy($table,$facility_id);
						if ($srcTypeData) {
							$extraDataSet[$srcTypeID] = $srcTypeData;
						}
					}
				}
    		}
    	}
    	
    	return ['dataSet'=>$dataSet,
     			'extraDataSet'=>$extraDataSet
    	];
    }
    
    
    public function getExtraDatasetBy($objectType,$facility_id){
    	$srcTypeData =null;
    	if($objectType=="PARCEL"){
    		$storage = Storage::getTableName();
    		$pdVoyageDetail = PdVoyageDetail::getTableName();
    		$pdVoyage = PdVoyage::getTableName();
    		$srcTypeData = PdVoyage::join($storage,function ($query) use ($storage,$facility_id,$pdVoyage) {
    			$query->on("$pdVoyage.STORAGE_ID",'=',"$storage.ID")
    			->where("$storage.FACILITY_ID",'=',$facility_id) ;
    		})
    		->join($pdVoyageDetail,"$pdVoyage.ID", '=', "$pdVoyageDetail.VOYAGE_ID")
    		->select(
    				"$pdVoyageDetail.ID",
    				"$pdVoyageDetail.ID as CODE",
    				"$pdVoyage.NAME as NAME",
    				"$pdVoyageDetail.PARCEL_NO as PARCEL_NO"
    				)
    				->orderBy("$pdVoyage.ID")
    				->orderBy("$pdVoyageDetail.PARCEL_NO")
    				->get();
    	}
    	else if($objectType=="RESERVOIR")
    		$srcTypeData =\DB::table($objectType)->get(['ID','CODE','NAME']);
    	else if ($objectType){
    		$srcTypeData =\DB::table($objectType)->where("FACILITY_ID",$facility_id)->get(['ID','CODE','NAME']);
    	}
    	return $srcTypeData;
    }
    
    public function loadsrc(Request $request){
    	//     	sleep(2);
    	$postData = $request->all();
    	$facility_id = $postData['Facility'];
    	$name = $postData['name'];
    	$srcTypeData = [];
    	if ($name=='SRC_TYPE') {
	    	$src_type_id = $postData['value'];
	    	$objectType = $postData['srcType'];
    		$srcTypeData = $this->getExtraDatasetBy($objectType,$facility_id);
    	}
    	return response()->json(['dataSet'=>$srcTypeData,
    							'postData'=>$postData]);
    }
    
    
    
	/* protected function getAffectedObjects($mdlName, $columns, $newData) {
		$mdl = "App\Models\\".$mdlName;
		$idField = $mdl::$idField;
		$objectId = $newData [$idField];
// 		$flowPhase = $newData [config ( "constants.flFlowPhase" )];
		$aFormulas = \FormulaHelpers::getAffects ( $mdlName, $columns, $objectId);
		return $aFormulas;
	} */
    
}
