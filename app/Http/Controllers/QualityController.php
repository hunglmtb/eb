<?php

namespace App\Http\Controllers;
use App\Models\CodeQltySrcType;
use Carbon\Carbon;
use App\Models\PdVoyage;
use App\Models\QltyDataDetail;
use App\Models\QltyProductElementType;
use App\Models\QltyData;
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
//     	$qltData = $mdl::getTableName();
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
							    	->whereDate("$dcTable.$filterBy", '>=', $occur_date)
							    	->whereDate("$dcTable.$filterBy", '<=', $date_end)
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
//     			\DB::enableQueryLog();
				$bySrcTypes = $dataSet->groupBy('SRC_ID');
// 				\Log::info(\DB::getQueryLog());
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
    
    public function edit(Request $request){
    	$postData = $request->all();
    	$id = $postData['id'];
//     	$ptype= QltyData::find($id)->get('PRODUCT_TYPE');
    	$dcTable =QltyData::getTableName();
    	$qltyDataDetail =QltyDataDetail::getTableName();
    	$qltyProductElementType =QltyProductElementType::getTableName();
    	
    	$properties = $this->getOriginProperties($qltyDataDetail);
    	 
    	$dataSet = QltyProductElementType::join($dcTable,function ($query) use ($dcTable,$id,$qltyProductElementType) {
										    		$query->on("$dcTable.PRODUCT_TYPE",'=',"$qltyProductElementType.PRODUCT_TYPE")
										    				->where("$dcTable.ID",'=',$id) ;
									    	})
									    	->leftJoin($qltyDataDetail, function($join) use ($qltyDataDetail,$id,$qltyProductElementType){
									    				$join->on("$qltyDataDetail.ELEMENT_TYPE", '=', "$qltyProductElementType.ID")
									    					->where("$qltyDataDetail.QLTY_DATA_ID",'=',$id);
									    	})
								    		->select(
 								    				"$qltyProductElementType.ID as ID",
								    				"$qltyProductElementType.ID as DT_RowId",
								    				"$qltyProductElementType.ORDER",
								    				"$qltyProductElementType.NAME",
								    				"$qltyProductElementType.PRODUCT_TYPE",
								    				"$qltyProductElementType.DEFAULT_UOM",
								    				/* "$qltyDataDetail.ELEMENT_TYPE",
								    				"$qltyDataDetail.VALUE",
								    				"$qltyDataDetail.UOM",
								    				"$qltyDataDetail.GAMMA_C7",
								    				"$qltyDataDetail.MOLE_FACTION",
								    				"$qltyDataDetail.MASS_FRACTION",
								    				"$qltyDataDetail.NORMALIZATION", */
								    				"$qltyDataDetail.*"
								    				)
// 						    				->orderBy("$qltyProductElementType.ORDER")
// 						    				->orderBy("$qltyProductElementType.NAME")
						    				->get();
									    	
    	$datasetGroups = $dataSet->groupBy(function ($item, $key) {
									    return $item['DEFAULT_UOM']=='Mole fraction'?'MOLE_FACTION':'NONE_MOLE_FACTION';
									});
    	
	    $results = [];
	    if ($datasetGroups->has('NONE_MOLE_FACTION')) {
	    	$gasElementColumns = ['ELEMENT_TYPE','VALUE','UOM'];
		    $noneMole = $properties->groupBy(function ($item, $key) use ($gasElementColumns) {
										    return in_array($item->name, $gasElementColumns)?'NONE_MOLE_FACTION':'MOLE_FACTION';
		    });
		    $results['NONE_MOLE_FACTION'] = ['properties'	=>$noneMole['NONE_MOLE_FACTION'],
		    								'dataSet'		=>$datasetGroups['NONE_MOLE_FACTION']];
	    }
	    
	    if ($datasetGroups->has('MOLE_FACTION')) {
	    	$oilElementColumns = ['VALUE','UOM'];
	    	$noneMole = $properties->groupBy(function ($item, $key) use ($oilElementColumns) {
										    return in_array($item->name, $oilElementColumns)?'NONE_MOLE_FACTION':'MOLE_FACTION';
	    	});
    	
    		$results['MOLE_FACTION'] = ['properties'	=>$noneMole['MOLE_FACTION'],
	    								'dataSet'		=>$datasetGroups['MOLE_FACTION']];
	    }
	    
    	return response()->json($results);
    }
    
    public function getDetailDataSet(Request $request){
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
    
    public function editSaving(Request $request){
    	$postData = $request->all();
    	$id = $postData['id'];
    	
    	$qltyDataEntry = QltyData::find($id);
    	if ($qltyDataEntry) {
    		$productType = $qltyDataEntry->PRODUCT_TYPE;
    		switch ($productType){
    			case 1://oil
   					$attributes = ['QLTY_DATA_ID'=>$id];
//    					$values = ['QLTY_DATA_ID'=>$id];
   					$oils = array_key_exists("oil", $postData)?$postData['oil']:null;
   					if ($oils&&count($oils)>0) {
	    				foreach($oils as $oil ){
	    					/* if (array_key_exists("ID", $oil)) {
	    						unset($oil["ID"]);
	    					} */
	    					$attributes['ELEMENT_TYPE'] = $oil['DT_RowId'];
   							$oil['QLTY_DATA_ID'] = $id;
   							$oil['ELEMENT_TYPE'] = $oil['DT_RowId'];
   							QltyDataDetail::updateOrCreate($attributes,$oil);
	    				};
   					}
   					$gases = array_key_exists("gas", $postData)?$postData['gas']:null;
   					if ($gases&&count($gases)>0) {
	    				foreach($gases as $gas ){
	    					/* if (array_key_exists("ID", $gas)) {
	    						unset($gas["ID"]);
	    					} */
	    					$attributes['ELEMENT_TYPE'] = $gas['DT_RowId'];
	    					$gas['QLTY_DATA_ID'] = $id;
	    					$gas['ELEMENT_TYPE'] = $gas['DT_RowId'];
		    				QltyDataDetail::updateOrCreate($attributes,$gas);
	    				};
   					}
    				break;
    			case 2://gas
    				$constantElementTypes = QltyProductElementType::where("PRODUCT_TYPE",'=', $productType)->select("MOL_WEIGHT","CODE","ID")->get();
    				
    				$gases = array_key_exists("gas", $postData)?$postData['gas']:null;
    				if ($gases) {
	   					$attributes = ['QLTY_DATA_ID'=>$id];
	   					$qltDetails = [];
	    				foreach($constantElementTypes as $constantElementType ){
	// 	   					$entries[$eid] = [];
		    				$attributes['ELEMENT_TYPE'] = $constantElementType->ID;
		    				$aqltyDataDetail = QltyDataDetail::firstOrNew($attributes);
		    				$aqltyDataDetail->fill($attributes);
		    				$qltDetails[$constantElementType->ID] = $aqltyDataDetail;
	    				}
	    				
	    				if ($gases&&count($gases)>0) {
	    					foreach($gases as $gas ){
	    						$qltd = $qltDetails[$gas['DT_RowId']];
	    						$qltd->fill($gas);
	    					};
	    					
	    					$totalMole = 0;
	    					foreach($constantElementTypes as $constantElementType ){
	    						$qltd = $qltDetails[$constantElementType->ID];
	    						$qltd->{'calculated'} = $qltd->MOLE_FACTION*$constantElementType->MOL_WEIGHT;
	    						$totalMole+=$qltd->MOLE_FACTION*$constantElementType->MOL_WEIGHT;
	    					};
	    					
	    					if ($totalMole!=0) {
		    					foreach($qltDetails as $qltd ){
		    						$qltd->MASS_FRACTION = $qltd->calculated/$totalMole;
		    						unset($qltd->calculated);
		    						$qltd->save();
		    					};
	    					}
	    					else response()->json('total = 0');
	    				}
	    				else response()->json('no change data detected');
    				}
    				else response()->json('empty data');
    		}
    	}
    	return response()->json('Edit Successfullly');
    }
}
