<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\CodeDeferGroupType;
use App\Models\QltyData;
use App\Models\QltyDataDetail;
use App\Models\QltyProductElementType;
use App\Models\EnergyUnit;
use App\Models\DefermentGroup;

class DefermentController extends CodeController {
    
	protected $extraDataSetColumns;
	public function __construct() {
		parent::__construct();
		$this->isApplyFormulaAfterSaving = false;
		$this->extraDataSetColumns = [	'DEFER_GROUP_TYPE'	=>	[	'column'	=>'DEFER_TARGET',
																	'model'		=>'DefermentGroup'],
										'CODE1'				=>	[	'column'	=>'CODE2',
																	'model'		=>'CodeDeferCode2'],
										'CODE2'				=>	[	'column'	=>'CODE3',
																	'model'		=>'CodeDeferCode3']
									];
		
		/* $this->fdcModel = "DefermentData";
		$this->idColumn = config("constants.defermentId"); */
		/* $this->phaseColumn = config("constants.flFlowPhase");
	
		$this->valueModel = "FlowDataValue";
		$this->theorModel = "FlowDataTheor"; */
// 		$this->isApplyFormulaAfterSaving = true;
	}
	
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	$date_end = $postData['date_end'];
    	$date_end = Carbon::parse($date_end);
    	
    	$extraDataSet = [];
    	$dataSet = null;
    	$codeDeferGroupType = CodeDeferGroupType::getTableName();
    	/* $uoms = $properties['uoms'];
	    $sourceTypekey = array_search('CodeQltySrcType', array_column($uoms, 'id'));
	    $sourceTypes = $uoms[$sourceTypekey]['data'];
	    $objectType = null;  */
	    
	    $where = ['FACILITY_ID' => $facility_id];
	    
    	$dataSet = $mdl::leftJoin($codeDeferGroupType,"$dcTable.DEFER_GROUP_TYPE",'=',"$codeDeferGroupType.ID")
							    	->where($where)
							    	->whereDate("$dcTable.BEGIN_TIME", '>=', $occur_date)
							    	->whereDate("$dcTable.END_TIME", '<=', $date_end)
							    	->select(
 							    			"$dcTable.ID as $dcTable",
							    			"$codeDeferGroupType.CODE as DEFER_GROUP_CODE",
							    			"$dcTable.ID as DT_RowId",
							    			"$dcTable.ID",
							    			"$dcTable.*"
							    			)
// 					    			->orderBy($dcTable)
 									->get();
	    
//     			\DB::enableQueryLog();
//     	\Log::info(\DB::getQueryLog());
    	
    	if ($dataSet&&$dataSet->count()>0) {
    		$bunde = ['FACILITY_ID' => $facility_id];
    		foreach($this->extraDataSetColumns as $column => $extraDataSetColumn){
    			$extraDataSet[$column] = $this->getExtraEntriesBy($column,$extraDataSetColumn,$dataSet,$bunde);
    		}
    	}
    	
    	return ['dataSet'=>$dataSet,
     			'extraDataSet'=>$extraDataSet
    	];
    }
    
    
    public function getExtraEntriesBy($sourceColumn,$extraDataSetColumn,$dataSet,$bunde){
    	$extraDataSet = null;
    	$subDataSets = $dataSet->groupBy($sourceColumn);
    	if ($subDataSets&&count($subDataSets)>0) {
    		$extraDataSet = [];
    		foreach($subDataSets as $key => $subData ){
    			$entry = $subData[0];
    			$sourceColumnValue = $entry->$sourceColumn;
    			if ($sourceColumn=='DEFER_GROUP_TYPE') {
    				$bunde['DEFER_GROUP_CODE'] = $entry->DEFER_GROUP_CODE;
    			}
    			$data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde);
    			if ($data) {
    				$extraDataSet[$sourceColumnValue] = $data;
    			}
    		}
    		$extraDataSet=count($extraDataSet)>0?$extraDataSet:null;
    	}
    	return $extraDataSet;
    }
    
    public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde){
    	$data = null;
    	switch ($sourceColumn) {
    		case 'CODE1':
    		case 'CODE2':
		    	$targetModel = $extraDataSetColumn['model'];
		    	$targetEloquent = "App\Models\\$targetModel";
		    	$data = $targetEloquent::where('PARENT_ID','=',$sourceColumnValue)->select(
									    			"*",
		 							    			"ID as value",
									    			"NAME as text"
									    			)->get();
    			break;
    			
    		case 'DEFER_GROUP_TYPE':
    			if ($bunde&&array_key_exists('DEFER_GROUP_CODE', $bunde)) {
	    			$defer_group_code = $bunde['DEFER_GROUP_CODE'];
	    			if ($defer_group_code=='WELL') {
	    				$facility_id = $bunde['FACILITY_ID'];
	    				$data = EnergyUnit::where(['FACILITY_ID'=>$facility_id,
	    									'FDC_DISPLAY'=>1
	    							])
	    							->select("ID","NAME","ID as value","NAME as text")
	    							->orderBy('text')
	    							->get();
	    			}
	    			else{
	    				$data = DefermentGroup::where(['DEFER_GROUP_TYPE'=>$sourceColumnValue])
							    				->select("ID","NAME","ID as value","NAME as text")
							    				->orderBy('text')
							    				->get();
	    			}
    			}
    			break;
    	}
    	return $data;
    }
    
    
    public function loadsrc(Request $request){
    	//     	sleep(2);
    	$postData = $request->all();
    	$sourceColumn = $postData['name'];
    	$sourceColumnValue = $postData['value'];
    	$bunde = [];
    	$dataSet = [];
    	if ($sourceColumn=='DEFER_GROUP_TYPE') {
    		$codeColumn = 'DEFER_GROUP_CODE';
    		$entry = CodeDeferGroupType::select("CODE as $codeColumn")->find($sourceColumnValue);
	    	$bunde[$codeColumn] = $entry->$codeColumn;
	    	
	    	$facility_id = $postData['Facility'];
	    	$bunde['FACILITY_ID'] = $facility_id;
    	}
    	$targetExists = true;
    	$loopIndex = 0;
    	while($loopIndex<5){
    		$loopIndex++;
    		if (!array_key_exists($sourceColumn, $this->extraDataSetColumns)) break;
	    	$extraDataSetColumn = $this->extraDataSetColumns[$sourceColumn];
	    	$targetColumn = $extraDataSetColumn['column'];
	    	$data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde);
	    	$dataSet[$targetColumn] = [	'data'			=>	$data,
								    	'ofId'			=>	$sourceColumnValue,
								    	'sourceColumn'	=>	$sourceColumn
								    	];
	    	
	    	$sourceColumn = $targetColumn;
	    	$sourceColumnValue = $data&&$data->count()>0?$data[0]->ID:null;
	    	if (!$sourceColumnValue) break;
    	}
    	
    	return response()->json(['dataSet'=>$dataSet,
    							'postData'=>$postData]);
    }
    /*
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
	    					$attributes['ELEMENT_TYPE'] = $oil['DT_RowId'];
   							$oil['QLTY_DATA_ID'] = $id;
   							$oil['ELEMENT_TYPE'] = $oil['DT_RowId'];
   							QltyDataDetail::updateOrCreate($attributes,$oil);
	    				};
   					}
   					$gases = array_key_exists("gas", $postData)?$postData['gas']:null;
   					if ($gases&&count($gases)>0) {
	    				foreach($gases as $gas ){
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
    } */
}
