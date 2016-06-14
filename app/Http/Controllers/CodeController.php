<?php

namespace App\Http\Controllers;
use App\Http\ViewComposers\ProductionGroupComposer;
use App\Models\CfgFieldProps;
use App\Models\CodeAllocType;
use App\Models\CodeFlowPhase;
use App\Models\CodePressUom;
use App\Models\Facility;
use App\Models\StandardUom;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\CodeTestingMethod;
use App\Models\CodeTestingUsage;
use App\Models\CodeQltySrcType;
use App\Models\CodeProductType;
use App\Models\CodeDeferReason;
use App\Models\CodeDeferStatus;
use App\Models\CodeDeferCategory;
use App\Models\CodeDeferGroupType;
use App\Models\CodeDeferCode1;
use App\Models\CodeTicketType;
use App\Models\PdTransitCarrier;
use App\Models\BaAddress;
use App\Models\Tank;
use App\Models\CodeSafetySeverity;
use App\Models\CodeCommentStatus;
use App\Models\CodeEqpOfflineReason;
use App\Models\CodeEqpFuelConsType;
use App\Models\CodeVolUom;
use App\Models\CodeEqpGhgRelType;

class CodeController extends EBController {
	 
	protected $fdcModel;
	protected $idColumn;
	protected $phaseColumn;
	protected $valueModel ;
	protected $keyColumns ;
	protected $theorModel ;
	protected $isApplyFormulaAfterSaving;
	
	
	 public function __construct() {
		parent::__construct();
		$this->isApplyFormulaAfterSaving = false;
	}
	
	public function getCodes(Request $request)
    {
		$options = $request->only('type','value', 'dependences');
		
		$mdl = 'App\Models\\'.$options['type'];
		$unit = $mdl::find($options['value']);
// 		->value('email');all(['ID', 'NAME']);
		$results = [];
		
		foreach($options['dependences'] as $model ){
			if ($unit!=null) {
				$eCollection = $unit->$model(['ID', 'NAME'])->getResults();
			}
			else  break;
			if (count ( $eCollection ) > 0) {
				$unit = ProductionGroupComposer::getCurrentSelect ( $eCollection );
				$filterArray = ProductionGroupComposer::getFilterArray ( $model, $eCollection, $unit );
				if (array_key_exists($model,  config("constants.subProductFilterMapping"))&&
						array_key_exists('default',  config("constants.subProductFilterMapping")[$model])) {
					$eCollection[] = config("constants.subProductFilterMapping")[$model]['default'];
				}
				$results [] = $filterArray;
			}
			else break;
		}
		
		return response($results, 200) // 200 Status Code: Standard response for successful HTTP request
			->header('Content-Type', 'application/json');
    }
    
    public function load(Request $request){
//     	sleep(2);
    	$postData = $request->all();
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
     	$dcTable = $mdl::getTableName();
     	
     	$facility_id = $postData['Facility'];
     	$occur_date = $postData['date_begin'];
     	$occur_date = Carbon::parse($occur_date);
     	
 		$results = $this->getProperties($dcTable,$facility_id,$occur_date);
      	$data = $this->getDataSet($postData,$dcTable,$facility_id,$occur_date,$results);
        $results['postData'] = $postData;
        $results = array_merge($results, $data);
    	return response()->json($results);
    }
    
    public function getProperties($dcTable,$facility_id,$occur_date){
    	
    	$properties = $this->getOriginProperties($dcTable);
    	$firstProperty = $this->getFirstProperty($dcTable);
    	if ($firstProperty) {
	    	$properties->prepend($firstProperty);
    	}
    	$uoms = $this->getUoms($properties,$facility_id);
    	$locked = $this->isLocked($dcTable,$occur_date,$facility_id);
    	
    	$results = ['properties'	=>$properties,
	    			'uoms'			=>$uoms,
	    			'locked'		=>$locked,
	    			'rights'		=>session('statut')];
    	return $results;
    }
    
    public function isLocked($dcTable,$occur_date,$facility_id){
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
    			'VALUE_MIN',
    			'VALUE_FORMAT',
    			'ID',
    			'FIELD_ORDER',
    			'VALUE_MAX']);
    	return $properties;
    }
    
    public function getFirstProperty($dcTable){
    	return  ['data'=>$dcTable,'title'=>'Object name','width'=>230];
    }
    
	public function getDataSet($postData, $dcTable, $facility_id, $occur_date,$properties) {
		return [];
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
//      	$record_freq = $postData['CodeReadingFrequency'];
//      	$phase_type = $postData['CodeFlowPhase'];
     	$facility_id = $postData['Facility'];
     	$occur_date = $postData['date_begin'];
     	$occur_date = Carbon::parse($occur_date);
     	$objectIds = array_key_exists('objectIds', $postData)?$postData['objectIds']:[];
     	
     	$affectedIds = [];
     	$this->preSave($editedData,$affectedIds,$postData);
     	try
     	{
     		$resultTransaction = \DB::transaction(function () use ($postData,$editedData,$objectIds,$affectedIds,
													     		 $occur_date,$facility_id){
     			$this->deleteData($postData);
     			
     			if(!$editedData) return [];
     			
     			$lockeds= [];
     			$ids = [];
     			$resultRecords = [];
     			
     			//      			\DB::enableQueryLog();
     			foreach($editedData as $mdlName => $mdlData ){
		     		$mdl = "App\Models\\".$mdlName;
		     		if ($mdl::$ignorePostData) {
		     			unset($editedData[$mdlName]);
		     			continue;
		     		}
		     		$ids[$mdlName] = [];
		     		$resultRecords[$mdlName] = [];
		     		$locked = \Helper::checkLockedTable($mdlName,$occur_date,$facility_id);
		     		if ($locked) {
		     			$lockeds[$mdlName] = "Data of $mdlName with facility $facility_id was locked on $occur_date ";
		     			unset($editedData[$mdlName]);
		     			continue;
		     		}
		     		foreach($mdlData as $key => $newData ){
		     			$columns = $mdl::getKeyColumns($newData,$occur_date,$postData);
 		     			$mdlData[$key] = $newData;
		     			$returnRecord = $mdl::updateOrCreateWithCalculating($columns, $newData);
		     			if ($returnRecord) {
		     				$returnRecord->updateAudit($columns,$newData,$postData);
			     			$ids[$mdlName][] = $returnRecord['ID'];
			     			$resultRecords[$mdlName][] = $returnRecord;
		     			}
		     		}
		     		$editedData[$mdlName] = $mdlData;
     			}
// 		     	\Log::info(\DB::getQueryLog());
		     	
		     	$objectIds = array_unique($objectIds);
		     	//doFormula in config table
		     	$affectColumns = [];
		     	foreach($editedData as $mdlName => $mdlData ){
		     		$cls  = \FormulaHelpers::doFormula($mdlName,'ID',$ids[$mdlName]);
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
		     	
		     	$resultTransaction = [];
		     	if (count($lockeds)>0) {
			     	$resultTransaction['lockeds'] = $lockeds;
		     	}
		     	$resultTransaction['ids']=$ids;
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
     	
     	//get updated data after apply formulas
     	$updatedData = [];
     	if (array_key_exists('ids', $resultTransaction)) {
	     	foreach($resultTransaction['ids'] as $mdlName => $updatedIds ){
	//      		$updatedData[$mdlName] = $mdl::findMany($objectIds);
	     		$mdl = "App\Models\\".$mdlName;
	     		$updatedData[$mdlName] = $mdl::findManyWithConfig($updatedIds);
	     	}
     	}
//      	\Log::info(\DB::getQueryLog());
    
     	$results = ['updatedData'=>$updatedData,'postData'=>$postData];
     	if (array_key_exists('lockeds', $resultTransaction)) {
	     	$results['lockeds'] = $resultTransaction['lockeds'];
     	}
    	return response()->json($results);
    }
    
    protected function deleteData($postData) {
    	if (array_key_exists ('deleteData', $postData )) {
    		$deleteData = $postData['deleteData'];
    		foreach($deleteData as $mdlName => $mdlData ){
    			$mdl = "App\Models\\".$mdlName;
    			$mdl::whereIn('ID', array_values($mdlData))->delete();
    		}
    	}
    }
    
    
	protected function preSave(&$editedData, &$affectedIds, $postData) {
		if ($editedData&&array_key_exists ($this->fdcModel, $editedData )) {
			$this->preSaveModel ( $editedData, $affectedIds, $this->valueModel);
			$this->preSaveModel ( $editedData, $affectedIds, $this->theorModel);
		}
	}
	
    protected function afterSave($resultRecords,$occur_date) {
	}
	
	protected function getAffectedObjects($mdlName,$columns,$newData){
		$mdl = "App\Models\\".$mdlName;
		$idField = $mdl::$idField;
		$objectId = $newData [$idField];
// 		$flowPhase = $newData [config ( "constants.euFlowPhase" )];
		$flowPhase = $this->getFlowPhase($newData);
		$aFormulas = \FormulaHelpers::getAffects ( $mdlName, $columns, $objectId,$flowPhase);
		return $aFormulas;
	}
	
	protected function getFlowPhase($newData) {
		return false;
	}
	
    
    public function getUoms($properties = null,$facility_id)
    {
    	$uoms = [];
    	$model = null;
    	$withs = [];
    	$i = 0;
    	$selectData = false;
    	$rs = [];
    	 
    	foreach($properties as $property ){
    		$columnName = $property['data'];
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
		    	case 'VOL_UOM' :
		    	case 'FL_VOL_UOM' :
		    	case 'EU_VOL_UOM' :
	    			$withs[] = 'CodeVolUom';
	    			$uoms[] = ['id'=>'CodeVolUom','targets'=>$i,'COLUMN_NAME'=>'FL_VOL_UOM'];
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
		    		$selectData = ['id'=>'CodeProductType','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = CodeProductType::all();
		    		$rs[] = $selectData;
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
    			case 'TARGET_TANK' :
    				$selectData = ['id'=>'Tank','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = Tank::where('FACILITY_ID', $facility_id)->get();
    				$rs[] = $selectData;
    				break;
    			case 'CARRIER_ID' :
    				$selectData = ['id'=>'PdTransitCarrier','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = PdTransitCarrier::where('TRANSIT_TYPE',1)->get();
    				$rs[] = $selectData;
    				break;
    			case 'BA_ID' :
    				$selectData = ['id'=>'BaAddress','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = BaAddress::all();
    				$rs[] = $selectData;
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
    
    
    public function preSaveModel(&$editedData,&$affectedIds,$model) {
    	if ($model) {
	    	$fdcModel = $this->fdcModel;
	    	if (array_key_exists($fdcModel, $editedData)) {
	    		$idColumn = $this->idColumn;
	    		$phaseColumn = $this->phaseColumn;
	    
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
    
}
