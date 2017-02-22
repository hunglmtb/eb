<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\ViewComposers\ProductionGroupComposer;
use App\Models\BaAddress;
use App\Models\CfgFieldProps;
use App\Models\CodeAllocType;
use App\Models\CodeBoolean;
use App\Models\CodeCommentStatus;
use App\Models\CodeDeferCategory;
use App\Models\CodeDeferCode1;
use App\Models\CodeDeferGroupType;
use App\Models\CodeDeferReason;
use App\Models\CodeDeferStatus;
use App\Models\CodeEqpFuelConsType;
use App\Models\CodeEqpGhgRelType;
use App\Models\CodeEqpOfflineReason;
use App\Models\CodeEventType;
use App\Models\CodeFlowPhase;
use App\Models\CodePersonnelTitle;
use App\Models\CodePersonnelType;
use App\Models\CodePressUom;
use App\Models\CodeProductType;
use App\Models\CodeQltySrcType;
use App\Models\CodeReadingFrequency;
use App\Models\CodeSafetySeverity;
use App\Models\CodeStatus;
use App\Models\CodeTestingMethod;
use App\Models\CodeTestingUsage;
use App\Models\CodeTicketType;
use App\Models\CodeVolUom;
use App\Models\CustomizeDateCollection;
use App\Models\EbFunctions;
use App\Models\Facility;
use App\Models\IntSystem;
use App\Models\PdTransitCarrier;
use App\Models\Personnel;
use App\Models\StandardUom;
use App\Models\Tank;	
use App\Models\TmTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class CodeController extends EBController {
	 
	protected $fdcModel;
	protected $idColumn;
	protected $phaseColumn;
	protected $valueModel ;
	protected $keyColumns ;
	protected $theorModel ;
	protected $isApplyFormulaAfterSaving;
	protected $extraDataSetColumns;
	protected $detailModel;
	
	public function __construct() {
		parent::__construct();
		$this->isApplyFormulaAfterSaving 	= false;
	}
	
	public function getCodes(Request $request){
		$options = $request->only('type','value', 'dependences','extra');
		$bunde = $options['extra'];
		$type = $options['type'];
		
		if ($type=='date_end'||$type=='date_begin') {
			$unit = new CustomizeDateCollection($type,$options['value']);
		}
		else{
			$mdl = 'App\Models\\'.$type;
			$unit = $mdl::find($options['value']);
		}
		
		$originUnit 	= $unit;
		$results 		= [];
		$currentUnits 	= [$type	=> $unit];
		foreach($options['dependences'] as $model ){
			$modelName 	= $model;
			$elementId	= $modelName;
			$currentId 	= null;
			$sourceUnit = $unit;
			$isAdd		= true;
			if (is_array($model)) {
				if (array_key_exists("source", $model)) {
					$currentSourceName = $model["source"];
					$sourceUnit = array_key_exists($currentSourceName, $currentUnits)?$currentUnits[$currentSourceName]:$originUnit;
				}
				else $sourceUnit = $originUnit;
				$modelName  = $model["name"];
				$isAdd = !array_key_exists("independent", $model)||!$model["independent"];
				
				if (array_key_exists("elementId", $model)) $elementId	= $model['elementId'];
			}
			
			if ($sourceUnit!=null) {
				$rs = ProductionGroupComposer::initExtraDependence($results,$model,$sourceUnit,$bunde);
				$eCollection 	= $rs['collection'];
				$modelName 		= $rs['model'];
				$currentId 		= $rs['currentId'];
			}
			else  break;
			if (is_string($model) && array_key_exists($model,  config("constants.subProductFilterMapping"))&&
				array_key_exists('default',  config("constants.subProductFilterMapping")[$model])) {
				$eCollection[] = config("constants.subProductFilterMapping")[$model]['default'];
			}
			$unit = ProductionGroupComposer::getCurrentSelect ( $eCollection,$currentId );
			$currentUnits[$modelName]	= $unit;
			$mdlName 		= 'App\Models\\'.$modelName;
			$aOption 		= $mdlName::getOptionDefault($modelName,$unit);
			$elementId		= is_string($elementId)?$elementId:$modelName;
			$filterArray 	= \Helper::getFilterArray ( $elementId, $eCollection, $unit,$aOption );
			if ($isAdd) $results [] = $filterArray;
		}
		
		return response($results, 200)->header('Content-Type', 'application/json');
    }
    
    public function load(Request $request){
    	$postData 		= $request->all();
    	$results		= $this->loadTableData($postData);
    	return response()->json($results);
    }
    
    public function loadTableData($postData){
    	$dcTable 		= $this->getWorkingTable($postData);
    	$facility_id 	= array_key_exists('Facility',  $postData)?$postData['Facility']:null;
    	$occur_date 	= null;
    	if (array_key_exists('date_begin',  $postData)){
    		$occur_date = $postData['date_begin'];
    		$occur_date = \Helper::parseDate($occur_date);
    	}
    	
    	$results 		= $this->getProperties($dcTable,$facility_id,$occur_date,$postData);
    	$data 			= $this->getDataSet($postData,$dcTable,$facility_id,$occur_date,$results);
    	$secondaryData 	= $this->getSecondaryData($postData,$dcTable,$facility_id,$occur_date,$results);
    	$results['secondaryData'] = $secondaryData;
    	$results['postData'] = $postData;
    	if ($data&&is_array($data)) {
    		$results 	= array_merge($results, $data);
    		$dataSet	= array_key_exists('dataSet',  $results)?$results['dataSet']:null;
    		$mdlName 	= array_key_exists(config("constants.tabTable"), $postData)?$postData[config("constants.tabTable")]:null;
    		
    		if ($dataSet&&$dataSet instanceof Collection && $dataSet->count()>0) {
    			$rQueryList			= [];
    			$properties			= $results['properties'];
    			$enableBatchRun		= $this->enableBatchRun($dataSet,$mdlName,$postData);
    			$dataSet->each(function ($item, $key) use ($enableBatchRun,$properties,&$rQueryList,$mdlName,$occur_date,$postData){
    				if ($item&&$item instanceof Model) {
	    				if ($enableBatchRun&& ($item->DT_RowId===null||$item->DT_RowId==="")) {
	    					$item->DT_RowId	= substr( md5(rand()), 0, 10);
	    				}
	    				if ($properties) {
	    					$properties->each(function ($property, $key) use ($item,&$rQueryList,$mdlName,$occur_date,$postData) {
	    						if ($property&&$property instanceof CfgFieldProps) {
	    							if ($property->shouldLoadLastValueOf($item)) {
		    							$column		= $property->data;
					    				$query		= $item->getLastValueOfColumn($mdlName,$column,$occur_date,$postData);
			    						if ($query) {
			    							if (!array_key_exists($column, $rQueryList)) $rQueryList[$column] = [];
			    							$rQueryList[$column][]	= $query;
			    						}
	    							}
	    						}
	    					});
	    				}
    				}
    			});
    			if ($enableBatchRun&&$mdlName) {
    				$objectIds				= $this->getObjectIds($dataSet,$postData);
    				$results["objectIds"]	= [$mdlName	=> $objectIds];
    			}
    			
    			$lastValues	= [];
    			foreach($rQueryList as $column => $rQuerys){
    				$rQuery	= null;
    				foreach($rQuerys as $query){
    					$rQuery	= $rQuery&&$query?$rQuery->union($query):$query;
    				};
    				if ($rQuery) {
    					$values	= $rQuery->get();
	    				if ($values) {
	    					$values	= $values->keyBy('DT_RowId');
	    					$lastValues[$column]	= $values;
	    				}
    				}
    			};
    			
    			foreach($lastValues as $column => $values){
    				$key = $properties->search(function ($item, $key) use ($column) {
    					return $item&&$item instanceof Model && $item->data == $column;
    				});
    				if ($key>=0) {
    					$property	= $properties->get($key);
	    				if ($property) $property->LAST_VALUES = $values;
    				}
    			};
    		}
    	}
    	return $results;
    }
    
    public function getObjectIds($dataSet,$postData){
    	/* $dswk 	= $dataSet->keyBy('DT_RowId');
    	$objectIds = $dswk->keys();
    	 */
    	$objectIds = $dataSet->map(function ($item, $key) {
    		return ["DT_RowId"			=> $item->DT_RowId,
    				"ID"				=> $item->ID,
    		];
    	});
    	return $objectIds;
    }
    
    public function enableBatchRun($dataSet,$mdlName,$postData){
        return false;
    }
    
    public function loadDetail(Request $request){
    	$postData 				= $request->all();
    	$id 					= $postData['id'];
    	$tab					= isset($postData['tab'])?$postData['tab']:$this->detailModel;
    	$detailModel			= "App\Models\\$tab";
    	$detailTable	 		= $detailModel::getTableName();
    	$results 				= $this->getProperties($detailTable);
    	$dataSet 				= $this->getDetailData($id,$postData,$results['properties']);
    	$results['dataSet'] 	= $dataSet;
    	 
    	return response()->json([$this->detailModel => $results]);
    }
    
    public function getDetailData($id,$postData,$properties){
    	return [];
    }
    
    public function history(Request $request){
    	$postData 				= $request->all();
    	$dcTable 				= $this->getWorkingTable($postData); 
     	$field 					= $postData['field'];
     	$rowData 				= $postData['rowData'];
    	$mdlName 				= $postData[config("constants.tabTable")];
		$mdl 					= "App\Models\\$mdlName";
     	$limit					= array_key_exists('limit',  $postData)?$postData['limit']:10;
     	$limit					= ($limit>=1 && $limit<=100)?$limit:10;
     	
     	$history				= $this->getHistory($mdl,$field,$rowData,$limit);
     	
        $results['history'] 	= $history;
        $results['$limit'] 		= $limit;
        $results['postData'] 	= $postData;
        /* return view ('partials.history',['history'	=>$history,
						        		'limit'		=>$limit,
						        		'postData'	=>$postData,
        ]); */
        
    	return response()->json($results);
    }
    
	public function getHistory($mdl,$field,$rowData,$limit){
		$dcTable		= $mdl::getTableName();
		$obj_name		= $this->getFieldTitle($dcTable,$field,$rowData);
		
		$row_id			= $rowData['ID'];
		$fieldName		= $this->getFieldLabel($field,$dcTable);
		
		$where			= $this->getHistoryConditions($dcTable,$rowData,$row_id);
		
		if ($where) {
			$history		= $this->getHistoryData($mdl, $field,$rowData,$where, $limit);
			
		}
		else $history = [];
		
		return ['name'		=> $obj_name,
				'dataSet'	=> $history,
				'fieldName'	=> $fieldName
		];
	}
	
	public function getHistoryData($mdl, $field,$rowData,$where, $limit){
		$row_id			= $rowData['ID'];
		$occur_date		= $row_id>0?$rowData['OCCUR_DATE']:Carbon::now();
		$history 		= $mdl::where($where)
								->whereDate('OCCUR_DATE', '<', $occur_date)
								->whereNotNull($field)
								->orderBy('OCCUR_DATE','desc')
								->skip(0)->take($limit)
								->select('OCCUR_DATE',
										"$field as VALUE"
										)
								->get();
		return $history;
	}
    
	public function getHistoryConditions($table,$rowData,$row_id){
		return null;
	}
	
    public function getWorkingTable($postData){
    	if (array_key_exists(config("constants.tabTable"), $postData)) {
	    	$mdlName = $postData[config("constants.tabTable")];
	    	$mdl = "App\Models\\$mdlName";
	    	return $mdl::getTableName();
    	}
    	return null;
    }
    
    public function getSecondaryData($postData,$dcTable,$facility_id,$occur_date,$results){
    	return null;
    }
    
    protected function getFieldLabel($field, $table) {
    	$row =  CfgFieldProps::where('TABLE_NAME', '=', $table)
    	->where('USE_FDC', '=', 1)
    	->where('COLUMN_NAME', '=', $field)
    	->select('LABEL')
    	->first();
    	return $row?$row->LABEL:"";
    }
    
    public function getFieldTitle($dcTable,$field,$rowData){
    	$obj_name		= $rowData[$dcTable];
    	return $obj_name;
    }
    
    
    public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
    	
    	$properties = $this->getOriginProperties($dcTable);
    	if($properties)	{
    		$lang			= session()->get('locale', "en");
    		$properties->each(function ($item, $key) use ($lang) {
    			if ($item&&$item instanceof Model) {
    				if ($item->title&&Lang::has("front/site.{$item->title}", $lang)) {
	    				$item->title	= trans("front/site.$item->title");
    				}
    			}
    		});
    	}
    	$firstProperty = $this->getFirstProperty($dcTable);
    	if ($firstProperty) {
	    	$properties->prepend($firstProperty);
    	}
    	
    	$locked = $this->isLocked($dcTable,$occur_date,$facility_id);
    	$uoms = $this->getUoms($properties,$facility_id,$dcTable,$locked);
    	
    	$results = ['properties'	=>$properties,
	    			'uoms'			=>$uoms,
	    			'locked'		=>$locked,
	    			'rights'		=>session('statut')];
    	return $results;
    }
    
    public function isLocked($dcTable,$occur_date,$facility_id){
    	if (!$occur_date||!$facility_id) return false;
    	$user = auth()->user();
    	$locked = 	$user->hasRight('DATA_READONLY')||
    				\Helper::checkLockedTable($dcTable,$occur_date,$facility_id)||
    				(\Helper::checkApproveTable($dcTable,$occur_date,$facility_id)&&
    						!$user->hasRight('ADMIN_APPROVE'))||
    				(\Helper::checkValidateTable($dcTable,$occur_date,$facility_id)&&
    						!$user->hasRight('ADMIN_APPROVE')&&
    						!$user->hasRight('ADMIN_VALIDATE'));
    	return $locked;
    }
    
    
    public function getOriginProperties($dcTable){
    	$properties = CfgFieldProps::where('TABLE_NAME', '=', $dcTable)
    	->where('USE_FDC', '=', 1)
    	->orderBy('FIELD_ORDER')
    	->get(['COLUMN_NAME as data',
    			'COLUMN_NAME as name',
     			'FDC_WIDTH as width',
    			'LABEL as title',
    			"DATA_METHOD",
    			"INPUT_ENABLE",
    			'INPUT_TYPE',
    			'VALUE_MAX',
    			'VALUE_MIN',
    			'VALUE_WARNING_MAX',
    			'VALUE_WARNING_MIN',
    			'RANGE_PERCENT',
    			'VALUE_FORMAT',
    			'ID',
    			'FIELD_ORDER',
    			'OBJECT_EXTENSION',
    	]);
    	return $properties;
    }
    
    public function getFirstProperty($dcTable){
    	return  ['data'=>$dcTable,'title'=>'Object name','width'=>230];
    }
    
	public function getDataSet($postData, $dcTable, $facility_id, $occur_date,$properties) {
		return [];
	}
	
	public function getExtraDataSet($dataSet, $bunde = []){
		$extraDataSet = [];
		if ($dataSet
			&&$dataSet->count()>0
			&&$this->extraDataSetColumns
			&&is_array($this->extraDataSetColumns)
			&&count($this->extraDataSetColumns)>0) {
				
			foreach($this->extraDataSetColumns as $column => $extraDataSetColumn){
				$extraDataSet[$column] = $this->getExtraEntriesBy($column,$extraDataSetColumn,$dataSet,$bunde);
			}
		}
		return $extraDataSet;
	}
	
	public function getModelName($mdlName,$postData) {
		return $mdlName;
	}
    
    public function save(Request $request){
//     	sleep(2);
//     	return response()->json('[]');
// 		throw new Exception("not Save");
    	$postData = $request->all();
    	 
    	if (!array_key_exists('editedData', $postData)&&!array_key_exists('deleteData', $postData)) {
    		return response()->json('no data 2 update!');
    	}
    	if (!array_key_exists('editedData', $postData)) {
    		$editedData = false;
    	}
    	else{
	    	$editedData = $postData['editedData'];
    	}
     	
     	$facility_id = null;
     	if (array_key_exists('Facility',  $postData)){
     		$facility_id = $postData['Facility'];
     	}
     	
     	$occur_date = null;
     	if (array_key_exists('date_begin',  $postData)){
     		$occur_date = $postData['date_begin'];
 			$occur_date 	= \Helper::parseDate($occur_date);
     	}
     	
     	$affectedIds = [];
     	$this->preSave($editedData,$affectedIds,$postData);
     	try
     	{
     		$resultTransaction = \DB::transaction(function () use ($postData,$editedData,$affectedIds,
													     		 $occur_date,$facility_id){
     			$this->deleteData($postData);
     			$objectIds	= array_key_exists('objectIds',  $postData)?$postData['objectIds']:[];
     			$objectIds	= $objectIds?$objectIds:[];
     			if(!$editedData&&count($objectIds)<=0) return [];
     			
     			$lockeds= [];
     			$ids = [];
     			$resultRecords = [];
     			
     			//      			\DB::enableQueryLog();
     			if ($editedData) {
	     			foreach($editedData as $mdlName => $mdlData ){
	     				if (!is_array($mdlData)) continue;
	     				$modelName = $this->getModelName($mdlName,$postData);
	 		     		$mdl = "App\Models\\".$modelName;
			     		if ($mdl::$ignorePostData) {
			     			unset($editedData[$mdlName]);
			     			continue;
			     		}
			     		$ids[$mdlName] = [];
			     		$resultRecords[$mdlName] = [];
			     		$tableName = $mdl::getTableName();
			     		$locked = \Helper::checkLockedTable($tableName,$occur_date,$facility_id);
			     		if ($locked) {
			     			$lockeds[$mdlName] = "Data of $modelName with facility $facility_id was locked on $occur_date ";
			     			unset($editedData[$mdlName]);
			     			continue;
			     		}
			     		foreach($mdlData as $key => $newData ){
			     			$columns 			= $mdl::getKeyColumns($newData,$occur_date,$postData);
			     			$originNewData		= $mdlData[$key];
	 		     			$mdlData[$key] 		= $newData;
			     			$returnRecord 		= $mdl::updateOrCreateWithCalculating($columns, $newData);
			     			if ($returnRecord) {
			     				$affectRecord 	= $returnRecord->updateDependRecords($occur_date,$originNewData,$postData);
			     				$returnRecord->updateAudit($columns,$newData,$postData);
				     			$ids[$mdlName][] = $returnRecord['ID'];
				     			$resultRecords[$mdlName][] = $returnRecord;
				     			if ($affectRecord) {
				     				$ids[$mdlName][] = $affectRecord['ID'];
				     				$resultRecords[$mdlName][] = $affectRecord;
				     			}
				     				 
			     			}
			     		}
			     		$editedData[$mdlName] = $mdlData;
	     			}
	// 		     	\Log::info(\DB::getQueryLog());
	// 		     	$objectIds = array_unique($objectIds);
			     	//doFormula in config table
			     	$affectColumns = [];
			     	foreach($editedData as $mdlName => $mdlData ){
	     				if (!is_array($mdlData)) continue;
			     		$modelName = $this->getModelName($mdlName,$postData);
			     		$cls  = \FormulaHelpers::doFormula($modelName,'ID',$ids[$mdlName]);
			     		if (is_array($cls)&&count($cls)>0) {
				     		$affectColumns[$mdlName] = $cls;
			     		}
			     	}
			     	
			     	foreach($resultRecords as $mdlName => $records ){
			     		foreach($records as $key => $returnRecord ){
			     			$returnRecord->afterSaving($postData);
			     		}
			     	}
			     	
			     	if ($this->isApplyFormulaAfterSaving) {
				     	//get affected object with id
			     		$objectWithformulas = [];
				     	foreach($editedData as $mdlName => $mdlData ){
				     		$mdl = "App\Models\\".$mdlName;
				     		foreach($mdlData as $key => $newData ){
				     			$columns = array_keys($newData);
				     			if (array_key_exists($mdlName, $affectColumns)) {
					     			$columns = array_merge($columns,$affectColumns[$mdlName]);
				     			}
			     				$uColumns = $mdl::getKeyColumns($newData,$occur_date,$postData);
				     			$columns = array_diff($columns, $uColumns);
				     			$aFormulas = $this->getAffectedObjects($mdlName,$columns,$newData);
				     			$objectWithformulas = array_merge($objectWithformulas,$aFormulas);
				     		}	     
				     	}
				     	$objectWithformulas = array_unique($objectWithformulas);
				     	
				     	//apply Formula in formula table
			     		$applieds = \FormulaHelpers::applyAffectedFormula($objectWithformulas,$occur_date);
			     		if ($applieds&&count($applieds)) {
					     	foreach($applieds as $apply ){
					     		$mdlName = $apply->modelName;
					     		if (!array_key_exists($mdlName, $ids)) {
					     			$ids[$mdlName] = [];
					     		}
					     		$ids[$mdlName][] = $apply->ID;
					     		$ids[$mdlName]  = array_unique($ids[$mdlName]);
			     				$resultRecords[$mdlName][] = $apply;
					     		$resultRecords[$mdlName]  = array_unique($resultRecords[$mdlName]);
					     	}
			     		}
			     	}
			     	
			     	$this->afterSave($resultRecords,$occur_date);
     			}
		     	
		     	$resultTransaction = [];
		     	if (count($lockeds)>0) {
			     	$resultTransaction['lockeds'] = $lockeds;
		     	}
	     		$resultTransaction['ids']	= $ids;
 		     	return $resultTransaction;
	     		
	      	});
     	}
     	catch (\Exception $e)
     	{
      		\Log::info("\n----------------------hehe--------------------------------------------------------------------------\nException wher run transation\n ");
//        		\Log::info($e->getTraceAsString());
// 			return response($e->getMessage(), 400);
			throw $e;
     	}
     	
     	if ($this->enableBatchRun(null,null,$postData)&&$editedData) {
     		$dataSets		= [];
     		foreach($editedData as $mdlName => $mdlData ){
     			$postData[config("constants.tabTable")]	= $mdlName;
	    		$dataSets[]	= $this->loadTableData($postData);
     		}
    		return response()->json(["dataSets"	=> $dataSets]);
     	}
     	else {
	     	//get updated data after apply formulas
	     	$updatedData = [];
	     	if (array_key_exists('ids', $resultTransaction)) {
		     	foreach($resultTransaction['ids'] as $mdlName => $updatedIds ){
		//      		$updatedData[$mdlName] = $mdl::findMany($objectIds);
			     	$modelName = $this->getModelName($mdlName,$postData);
		     		$mdl = "App\Models\\".$modelName;
		     		$updatedData[$mdlName] = $mdl::findManyWithConfig($updatedIds);
		     	}
	     	}
	//      	\Log::info(\DB::getQueryLog());
	    
	     	$results = ['updatedData'=>$updatedData,
	     				'postData'=>$postData];
	     	if (array_key_exists('lockeds', $resultTransaction)) {
		     	$results['lockeds'] = $resultTransaction['lockeds'];
	     	}
	    	return response()->json($results);
     	}
    }
    
    protected function deleteData($postData) {
    	if (array_key_exists ('deleteData', $postData )) {
    		$deleteData = $postData['deleteData'];
    		foreach($deleteData as $mdlName => $mdlData ){
    			$mdl = "App\Models\\".$mdlName;
    			$mdl::deleteWithConfig($mdlData);
    		}
    	}
    }
    
    
	protected function preSave(&$editedData, &$affectedIds, $postData) {
		if ($this->fdcModel&&$editedData&&array_key_exists ($this->fdcModel, $editedData )) {
			$this->preSaveModel ( $editedData, $affectedIds, $this->valueModel,$this->fdcModel);
			$this->preSaveModel ( $editedData, $affectedIds, $this->theorModel,$this->fdcModel);
			
			$new_value 	= $editedData[$this->fdcModel];
			unset($editedData[$this->fdcModel]);
			$editedData = [$this->fdcModel => $new_value] + $editedData;
		}
	}
	
    protected function afterSave($resultRecords,$occur_date) {
	}
	
	protected function getAffectedObjects($mdlName,$columns,$newData){
		$mdl = "App\Models\\".$mdlName;
		$idField = $mdl::$idField;
		$objectId = is_array($newData)? $newData [$idField]:$newData;
		
		/* $objectId = 0;
		if(is_array($newData)){
			$objectId = $newData [$idField];
		}
		else{
			$res = $mdl::where("ID","=",$newData)->select($idField)->first();
			if($res) $objectId = $res->$idField;
		}
		 */
		if(!$objectId) return [];
		//$objectId = is_array($newData)? $newData [$idField]:$newData;
//		\Log::info("objectId: $objectId");
// 		$flowPhase = $newData [config ( "constants.euFlowPhase" )];
		$flowPhase = $this->getFlowPhase($newData);
		$aFormulas = \FormulaHelpers::getAffects ( $mdlName, $columns, $objectId,$flowPhase);
		return $aFormulas;
	}
	
	protected function getFlowPhase($newData) {
		return false;
	}
	
    
    public function getUoms($properties = null,$facility_id,$dcTable=null,$locked = false)
    {
    	$uoms = [];
    	$model = null;
    	$withs = [];
    	$i = 0;
    	$selectData = false;
    	$rs = [];
    	 
    	foreach($properties as $property ){
    		$columnName = is_array($property)&&array_key_exists('data', $property)?$property['data']:$property->data;
    		switch ($columnName){
    			case 'PRESS_UOM' :
    				$withs[] = 'CodePressUom';
    				$uoms[] = ['id'=>'CodePressUom','targets'=>$i,'COLUMN_NAME'=>'PRESS_UOM'];
    				break;
    			case 'TEMP_UOM' :
    				$withs[] = 'CodeTempUom';
    				$uoms[] = ['id'=>'CodeTempUom','targets'=>$i,'COLUMN_NAME'=>'TEMP_UOM'];
    				break;
    			case 'FL_POWR_UOM' :
    			case 'EU_POWR_UOM' :
    				$withs[] = 'CodePowerUom';
    				$uoms[] = ['id'=>'CodePowerUom','targets'=>$i,'COLUMN_NAME'=>'FL_POWR_UOM'];
    				break;
    			case 'FL_ENGY_UOM' :
    			case 'EU_ENGY_UOM' :
	    			$withs[] = 'CodeEnergyUom';
	    			$uoms[] = ['id'=>'CodeEnergyUom','targets'=>$i,'COLUMN_NAME'=>'FL_ENGY_UOM'];
	    			break;
	    		case 'FL_MASS_UOM' :
	    		case 'EU_MASS_UOM' :
		    		$withs[] = 'CodeMassUom';
		    		$uoms[] = ['id'=>'CodeMassUom','targets'=>$i,'COLUMN_NAME'=>'FL_MASS_UOM'];
		    		break;
		    	case 'MMR_QTY_UOM' :
		    	case 'VOL_UOM' :
		    	case 'FL_VOL_UOM' :
		    	case 'EU_VOL_UOM' :
	    			$withs[] = 'CodeVolUom';
	    			$uoms[] = ['id'=>'CodeVolUom','targets'=>$i,'COLUMN_NAME'=>'FL_VOL_UOM'];
	    			break;
    			case 'EU_STATUS' :
    				$selectData = ['id'=>'EuStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
				/*
    				$selectData['data'] = collect([
													(object)['ID' =>	-1	,'NAME' => '(Auto)'    ],
													(object)['ID' =>	1	,'NAME' => 'Online'    ],
													(object)['ID' =>	0	,'NAME' => 'Offline'   ],
												]);
				*/
    				$selectData['data'] = CodeStatus::all();
    				$rs[] = $selectData;
    				break;
	    			
    			case 'ALLOC_TYPE' :
	    				$selectData = ['id'=>'CodeAllocType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    				$selectData['data'] = CodeAllocType::all();
	    				$rs[] = $selectData;
	    				break;
    			case 'TEST_METHOD' :
    					$selectData = ['id'=>'CodeTestingMethod','targets'=>$i,'COLUMN_NAME'=>$columnName];
    					$selectData['data'] = CodeTestingMethod::all();
    					$rs[] = $selectData;
    					break;
    			case 'TEST_USAGE' :
    					$selectData = ['id'=>'CodeTestingUsage','targets'=>$i,'COLUMN_NAME'=>$columnName];
    					$selectData['data'] = CodeTestingUsage::all();
    					$rs[] = $selectData;
    					break;
	    		case 'EVENT_TYPE' :
		    			$selectData = ['id'=>'CodeEventType','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    			$selectData['data'] = CodeEventType::all();
		    			$rs[] = $selectData;
		    			break;
    			case 'SRC_TYPE' :
	    				$selectData = ['id'=>'CodeQltySrcType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    				$selectData['data'] = CodeQltySrcType::all();
	    				$rs[] = $selectData;
	    				break;
		    	case 'PRODUCT_TYPE' :
		    	case 'PHASE_TYPE' :
		    		$selectData = ['id'=>'CodeProductType','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = CodeProductType::all();
		    		$rs[] = $selectData;
		    		break;
	    		case 'DEFER_REASON' :
	    			$selectData = ['id'=>'CodeDeferReason','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = CodeDeferReason::all();
	    			$rs[] = $selectData;
		    		break;
	    		case 'DEFER_STATUS' :
	    			$selectData = ['id'=>'CodeDeferStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = CodeDeferStatus::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'CODE1' :
    				$selectData = ['id'=>'CodeDeferCode1','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeDeferCode1::all();
    				$rs[] = $selectData;
    				break;
    			case 'DEFER_CATEGORY' :
    				$selectData = ['id'=>'CodeDeferCategory','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeDeferCategory::all();
    				$rs[] = $selectData;
    				break;
    			case 'DEFER_GROUP_TYPE' :
    				$selectData = ['id'=>'CodeDeferGroupType','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeDeferGroupType::all();
    				$rs[] = $selectData;
    				break;
    			case 'TICKET_TYPE' :
	    			$selectData = ['id'=>'CodeTicketType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = CodeTicketType::all();
	    			$rs[] = $selectData;
	    			break;
    			/* case 'TARGET_TANK' :
    				$selectData = ['id'=>'Tank','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = Tank::where('FACILITY_ID', $facility_id)->get();
    				$rs[] = $selectData;
    				break; */
    			case 'CARRIER_ID' :
    			case 'PD_TRANSIT_CARRIER_ID' :
    			case 'CONNECTING_CARRIER' :
    				if ($dcTable==\App\Models\PdCargoNomination::getTableName()&&!$locked) break;
    				$selectData = ['id'=>'PdTransitCarrier','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				if ($dcTable==\App\Models\RunTicketFdcValue::getTableName()
    						||$dcTable==\App\Models\RunTicketValue::getTableName()) 
    					$selectData['data'] = PdTransitCarrier::where('TRANSIT_TYPE',1)->get();
    				else
    					$selectData['data'] = PdTransitCarrier::all();
    				$rs[] = $selectData;
    				break;										
    			case 'BA_ID' :
    			case 'MMR_APPROVED_ID' :
    			case 'MMR_ORIGINATED_ID' :
    				if ($dcTable!=Personnel::getTableName()) {
	    				$selectData = ['id'=>'BaAddress','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    				$selectData['data'] = BaAddress::all();
	    				$rs[] = $selectData;
    				}
    				break;
    			case 'SEVERITY_ID' :
    				$selectData = ['id'=>'CodeSafetySeverity','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeSafetySeverity::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
    				$rs[] = $selectData;
    				break;
    			case 'STATUS' :
    				$selectData = ['id'=>'CodeCommentStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeCommentStatus::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
    				$rs[] = $selectData;
    				break;
    			case 'OFFLINE_REASON_CODE' :
    				$selectData = ['id'=>'CodeEqpOfflineReason','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeEqpOfflineReason::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
    				$rs[] = $selectData;
    				break;
	    		case 'EQP_FUEL_CONS_TYPE' :
	    			$selectData = ['id'=>'CodeEqpFuelConsType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = CodeEqpFuelConsType::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
	    			$rs[] = $selectData;
	    			break;
    			case 'EQP_GHG_REL_TYPE' :
    				$selectData = ['id'=>'CodeEqpGhgRelType','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeEqpGhgRelType::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
    				$rs[] = $selectData;
    				break;
    			case 'EQP_GHG_UOM' :
    			case 'EQP_CONS_UOM' :
    				$selectData = ['id'=>'CodeVolUom','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeVolUom::all();
    				$rs[] = $selectData;
    				break;
    			case 'TYPE' :
    				$selectData = ['id'=>'CodePersonnelType','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodePersonnelType::all();
    				$rs[] = $selectData;
    				break;
		    	case 'TITLE' :
		    		$selectData = ['id'=>'CodePersonnelTitle','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = CodePersonnelTitle::all();
		    		$rs[] = $selectData;
		    		break;
	    		case 'SYSTEM_ID' :
	    			$selectData = ['id'=>'IntSystem','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = IntSystem::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'FREQUENCY' :
    				$selectData = ['id'=>'CodeReadingFrequency','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeReadingFrequency::all();
    				$rs[] = $selectData;
    				break;
    			case 'ALLOW_OVERRIDE' :
    				$selectData = ['id'=>'CodeBoolean','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeBoolean::all();
    				$rs[] = $selectData;
    				break;
	    		case 'FLOW_PHASE' :
	    			$selectData = ['id'=>'CodeFlowPhase','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = CodeFlowPhase::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'REQUEST_UOM' 		:
    			case 'NOMINATION_UOM' 	:
    			case 'REQUEST_QTY_UOM' 	:
    			case 'SCHEDULE_UOM' 	:
    			case 'ATTRIBUTE_UOM' 	:
    			case 'LOAD_UOM' 		:
    			case 'QTY_UOM' 			:
    			case 'ITEM_UOM' 		:
    				$selectData = ['id'=>'PdCodeMeasUom','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeMeasUom::all();
    				$rs[] = $selectData;
    				break;
	    		case 'PRIORITY' :
	    			$selectData = ['id'=>'PdCodeCargoPriority','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeCargoPriority::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'QUANTITY_TYPE' :
    				$selectData = ['id'=>'PdCodeCargoQtyType','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeCargoQtyType::all();
    				$rs[] = $selectData;
    				break;
	    		case 'LIFTING_ACCT' :
	    		case 'LIFTING_ACCOUNT' :
	    			$selectData = ['id'=>'PdLiftingAccount','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdLiftingAccount::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'CONTRACT_ID' :
    				$selectData = ['id'=>'PdContract','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdContract::all();
    				$rs[] = $selectData;
    				break;
	    		case 'STORAGE_ID' :
	    			$selectData = ['id'=>'Storage','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\Storage::where('FACILITY_ID', $facility_id)->get();
	    			$rs[] = $selectData;
	    			break;
    			case 'REQUEST_TOLERANCE' :
    				$selectData = ['id'=>'PdCodeQtyAdj','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeQtyAdj::all();
    				$rs[] = $selectData;
    				break;
    			case 'ADJUSTABLE_TIME' :
    			case 'NOMINATION_ADJ_TIME' :
    				$selectData = ['id'=>'PdCodeTimeAdj','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeTimeAdj::all();
    				$rs[] = $selectData;
    				break;
		    	case 'INCOTERM' :
		    		$selectData = ['id'=>'PdCodeIncoterm','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = \App\Models\PdCodeIncoterm::all();
		    		$rs[] = $selectData;
		    		break;
	    		case 'TRANSIT_TYPE' :
	    			$selectData = ['id'=>'PdCodeTransitType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeTransitType::all();
	    			$rs[] = $selectData;
	    			break;
    			/* case 'ACTIVITY_NAME' :
    				$selectData = ['id'=>'ID','targets'=>$i,'COLUMN_NAME'=>'NAME'];
    				$sql = "";
    				$sql .= " SELECT ID, NAME FROM pd_code_load_activity a where exists (select 1 from PD_CARGO_LOAD b join TERMINAL_TIMESHEET_DATA  c ON ( b.ID = c.PARENT_ID AND c.IS_LOAD = 1 ) where c.ACTIVITY_ID = a.ID)";
					$sql .= " union all";
					$sql .= " SELECT ID, NAME FROM pd_code_load_activity a where exists (select 1 from PD_CARGO_UNLOAD b join TERMINAL_TIMESHEET_DATA  c ON ( b.ID = c.PARENT_ID AND c.IS_LOAD = 1 ) where c.ACTIVITY_ID = a.ID)"; 

					$tmp = \DB::select($sql);					
    				$selectData['data'] = $tmp;
    				$rs[] = $selectData;
    				break; */
    			case 'CARGO_ID' :
    				$selectData = ['id'=>'PdCargo','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCargo::all();
    				$rs[] = $selectData;
    				break;
	    		case 'BERTH_ID' :
	    			$selectData = ['id'=>'PdBerth','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdBerth::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'CARGO_STATUS' :
    				$selectData = ['id'=>'PdCodeCargoStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeCargoStatus::all();
    				$rs[] = $selectData;
    				break;
	    		case 'CONTRACT_TYPE' :
	    		case 'CONTACT_TYPE' :
	    			$selectData = ['id'=>'PdCodeContractType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeContractType::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'CONTRACT_PERIOD' :
    				$selectData = ['id'=>'PdCodeContractPeriod','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeContractPeriod::all();
    				$rs[] = $selectData;
    				break;
	    		case 'CONTRACT_EXPENDITURE' :
	    			$selectData = ['id'=>'PdContractExpenditure','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdContractExpenditure::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'CONTRACT_TEMPLATE' :
    				$selectData = ['id'=>'PdContractTemplate','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdContractTemplate::all();
    				$rs[] = $selectData;
    				break;
	    		case 'DEMURRAGE_EBO' :
	    			$selectData = ['id'=>'PdCodeDemurrageEbo','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeDemurrageEbo::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'SURVEYOR_BA_ID' :
    				$selectData = ['id'=>'BaAddress','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\BaAddress::where('SOURCE',15)->get();
    				$rs[] = $selectData;
    				break;
	    		case 'WITNESS_BA_ID1' :
		    	case 'WITNESS_BA_ID2' :
	    			$selectData = ['id'=>'BaAddress','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\BaAddress::where('SOURCE',4)->get();
	    			$rs[] = $selectData;
	    			break;
    			case 'ACTIVITY_ID' :
    			case 'ACTIVITY_NAME' :
    				$selectData = ['id'=>'PdCodeLoadActivity','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeLoadActivity::all();
    				$rs[] = $selectData;
    				break;
		    	case 'VOYAGE_ID' :
		    		$selectData = ['id'=>'PdVoyage','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = \App\Models\PdVoyage::all();
		    		$rs[] = $selectData;
		    		break;
	    		case 'DEPART_PORT' :
	    		case 'NEXT_DESTINATION_PORT' :
    			case 'PORT_ID' :
    			case 'ULLAGE_PORT' :
    			case 'ORIGIN_PORT' :
    			case 'DESTINATION_PORT' :
	    			$selectData = ['id'=>'PdPort','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdPort::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'FLOW_ID' :
    				$selectData = ['id'=>'Flow','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\Flow::where("FACILITY_ID",'=',$facility_id)->get();
    				$rs[] = $selectData;
    				break;
	    		case 'MEASURED_ITEM' :
	    			$selectData = ['id'=>'PdCodeMeasItem','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeMeasItem::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'FORMULA_ID' :
    				$selectData = ['id'=>'Formula','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\Formula::where("GROUP_ID",'=',7)->get();
    				$rs[] = $selectData;
    				break;
	    		case 'PROGRAM_TYPE' :
	    			$selectData = ['id'=>'PdCodeProgramType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeProgramType::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'RUN_FREQUENCY' :
    				$selectData = ['id'=>'PdCodeRunFrequency','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeRunFrequency::all();
    				$rs[] = $selectData;
    				break;
	    		case 'ADJUST_CODE' :
	    			$selectData = ['id'=>'PdCodeLiftAcctAdj','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeLiftAcctAdj::all();
	    			$rs[] = $selectData;
	    			break;
	    		case 'OBJECT_TYPE' :
	    			$selectData = ['id'=>'IntObjectType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\IntObjectType::all();
	    			$rs[] = $selectData;
	    			break;
	    		case 'MMR_STATUS' :
	    			$selectData = ['id'=>'CodeMmrStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrStatus::all();
	    			$rs[] = $selectData;
	    			break;
	    		case 'MMR_CLASS' :
	    			$selectData = ['id'=>'CodeMmrClass','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrClass::all();
	    			$rs[] = $selectData;
	    			break;
	    		case 'MMR_REASON' :
	    			$selectData = ['id'=>'CodeMmrReason','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrReason::all();
	    			$rs[] = $selectData;
	    			break;
	    		case 'MMR_ROOT_CAUSE' :
	    			$selectData = ['id'=>'CodeMmrRootCause','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrRootCause::all();
	    			$rs[] = $selectData;
	    			break;
	    		case 'MMR_CALC_METHOD_FORMULA':
	    			$selectData = ['id'=>'CodeMmrCalcMethod','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrCalcMethod::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'WO_ACTION' :
	    			$selectData = ['id'=>'CodeMmrWOAction','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrWOAction::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'COMPOSITION' :
    				$selectData = ['id'=>'QltyProductElementType','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\QltyProductElementType::where(["PRODUCT_TYPE"	=> 2,"ACTIVE"	=> 1])->orderBy("ORDER")->get();
    				$rs[] = $selectData;
    				break;
    			case 'task_group' :
		    		$selectData = ['id'=>'EbFunctions','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = \App\Models\EbFunctions::loadBy(null);
		    		$rs[] = $selectData;
		    		break;
	    		case 'runby' :
	    			$selectData = ['id'=>'runby','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = collect([
											(object)['ID' =>	TmTask::RUN_BY_SYSTEM	,'NAME' => 'System'  ],
											(object)['ID' =>	TmTask::RUN_BY_USER		,'NAME' => 'User'    ],
										]);
	    			$rs[] = $selectData;
	    			break;
    			case 'status' :
    				$selectData = ['id'=>'status','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = collect([
    						(object)['ID' =>	1	,'NAME' => 'Ready'   	],
    						(object)['ID' =>	2	,'NAME' => 'Stop'    	],
    						(object)['ID' =>	7	,'NAME' => 'Starting'   ],
    				]);
    				$rs[] = $selectData;
    				break;
	    		
    		}
    		$i++;
    	}
    	
    	if (count($withs)>0) {
    		$model = StandardUom::with($withs)->where('ID', $facility_id)->first();
	    	if ($model==null) {
		    	$model = Facility::with($withs)->where('ID', $facility_id)->first();
	    	}
    	}
//     	\DB::enableQueryLog();
    	if ($model!=null) {
	    	foreach($uoms as $key => $uom ){
	    		$uom['data'] = $model->$uom['id'];
	    		$uoms[$key] = $uom;
	    		$rs[] = $uom;
	    	}
    	}
    	return $rs;
//     	\Log::info(\DB::getQueryLog());
    }
    
    public function getUomType($uom_type = null,$facility_id)
    {
    	if ($uom_type==null) {
    		$uom_type = StandardUom::where('facility_id', $facility_id)->select('UOM_TYPE')->first();
    		if ($uom_type==null) {
	    		$uom_type = Facility::where('facility_id', $facility_id)->select('UOM_TYPE')->first();
    		}
    	}
    	return $uom_type;
    }
    
    
    public function preSaveModel(&$editedData,&$affectedIds,$model,$sourceModel) {
    	if ($model) {
	    	$fdcModel = $sourceModel;
	    	if (array_key_exists($fdcModel, $editedData)) {
	    		$idColumn = $this->idColumn;
// 	    		$phaseColumn = $this->phaseColumn;
	    
	    		if (!array_key_exists($model, $editedData)){
	    			$editedData[$model] = [];
	    		}
	    		foreach ($editedData[$fdcModel] as $element) {
	    			$notExist = $this->checkExistPostEntry($editedData,$model,$element,$idColumn);
	    			if ($notExist) {
	    				$autoElement = array_intersect_key($element, array_flip($this->keyColumns));
	    				$autoElement['auto'] = true;
	    				$editedData[$model][] =  $autoElement;
	    			}
	    			$affectedIds[]=$element[$idColumn];
	    		}
	    	}
    	}
    }
    
    public function checkExistPostEntry($editedData,$model,$element,$idColumn){
    	$key = array_search($element[$idColumn],array_column($editedData[$model],$idColumn));
    	return $key===FALSE;
    }
    
    public function getExtraEntriesBy($sourceColumn,$extraDataSetColumn,$dataSet,$bunde=[]){
    	$extraDataSet = null;
    	$subDataSets = $dataSet->groupBy($sourceColumn);
    	if ($subDataSets&&count($subDataSets)>0) {
    		$extraDataSet = [];
    		foreach($subDataSets as $key => $subData ){
    			$entry = $subData[0];
    			$sourceColumnValue = $entry->$sourceColumn;
    			$this->putExtraBundle($bunde,$sourceColumn,$entry);
    			$data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde);
    			if ($data) {
    				$extraDataSet[$sourceColumnValue] = $data;
    			}
    		}
    		$extraDataSet=count($extraDataSet)>0?$extraDataSet:null;
    	}
    	return $extraDataSet;
    }
    
    public function putExtraBundle(&$bunde,$sourceColumn,$entry){
    }
    
    public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde){
    	return null;
    }
    
    public function loadsrc(Request $request){
    	$postData = $request->all();
    	$sourceColumn = $postData['name'];
    	$sourceColumnValue = $postData['value'];
    	$dataSet = [];
    
    	$targets	= array_key_exists("target", $postData)?$postData["target"]:null;
    	$targets	= $targets&&is_array($targets)&&count($targets)>0?$targets:
    					($this->extraDataSetColumns&&array_key_exists($sourceColumn, $this->extraDataSetColumns)?
    							[$this->extraDataSetColumns[$sourceColumn]]:null);
    	if ($targets) {
		    foreach($targets as $extraDataSetColumn ){
			   	$targetColumn = is_array($extraDataSetColumn)?$extraDataSetColumn['column']:$extraDataSetColumn;
			   	$data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$postData);
			   	$dataSet[$targetColumn] = [	'data'			=>	$data,
			   			'ofId'			=>	$sourceColumnValue,
			   			'sourceColumn'	=>	$sourceColumn
			   	];
		    }
    	}
    	return response()->json(['dataSet'=>$dataSet,
    			'postData'=>$postData]);
    }
    
    public function help($name){
    	$help = EbFunctions::where("CODE",$name)->select("HELP")->first();
    	$help = $help?$help:"";
    	return response()->json($help);
    }
    
    public function getGroupFilter($postData){
    	$filterGroups = array('productionFilterGroup'	=> [],
    			'dateFilterGroup'			=> [],
    			'frequenceFilterGroup'		=> []
    	);
    	
    	return $filterGroups;
    }
    
    public function filter(Request $request){
    	$postData 		= $request->all();
    	$filterGroups	= $this->getGroupFilter($postData);
    	return view ( 'partials.editfilter',
    			['filters'			=> $filterGroups,
//     			'prefix'			=> "secondary_",
    			"currentData"		=> $postData
    	]);
    }
}
