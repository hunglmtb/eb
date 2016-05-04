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


class CodeController extends EBController {
	 
	protected $fdcModel;
	protected $idColumn;
	protected $phaseColumn;
	protected $valueModel ;
	protected $theorModel ;
	protected $isApplyFormulaAfterSaving = true;
	
	
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
     	
      	$data = $this->getDataSet($postData,$dcTable,$facility_id,$occur_date);
 		$results = $this->getProperties($dcTable,$facility_id,$occur_date);
        $results['postData'] = $postData;
        $results = array_merge($results, $data);
    	return response()->json($results);
    }
    
    public function getProperties($dcTable,$facility_id,$occur_date){
     	
    	$properties = CfgFieldProps::where('TABLE_NAME', '=', $dcTable)
									    	->where('USE_FDC', '=', 1)
									    	->orderBy('FIELD_ORDER')
									    	->get(['COLUMN_NAME as data','COLUMN_NAME as name', 'FDC_WIDTH as width','LABEL as title',"DATA_METHOD"]);
    	$properties->prepend(['data'=>$dcTable,'title'=>'Object name','width'=>230]);
    	
    	$uoms = $this->getUoms($properties,$facility_id);
    	$locked = \Helper::checkLockedTable($dcTable,$occur_date,$facility_id);
    	$results = ['properties'	=>$properties,
	    			'uoms'			=>$uoms,
	    			'locked'		=>$locked,
	    			'rights'		=>session('statut')];
    	return $results;
    }
    
    
	public function getDataSet($postData, $dcTable, $facility_id, $occur_date) {
		return [];
	}
    
    
    
    public function save(Request $request){
//     	sleep(2);
    	$postData = $request->all();
    	if (!array_key_exists('editedData', $postData)) {
    		return response()->json('no data 2 update!');
    	}
    	$editedData = $postData['editedData'];
     	$record_freq = $postData['CodeReadingFrequency'];
     	$phase_type = $postData['CodeFlowPhase'];
     	$facility_id = $postData['Facility'];
     	$occur_date = $postData['date_begin'];
     	$occur_date = Carbon::parse($occur_date);
     	$objectIds = array_key_exists('objectIds', $postData)?$postData['objectIds']:[];
     	
     	$affectedIds = [];
     	$this->preSave($editedData,$affectedIds,$postData);
     	try
     	{
     		$resultTransaction = \DB::transaction(function () use ($postData,$editedData,$objectIds,$affectedIds,
													     		 $occur_date,$phase_type,$facility_id){
     			$lockeds= [];
     			$ids = [];
     			$resultRecords = [];
     			//      			\DB::enableQueryLog();
     			foreach($editedData as $mdlName => $mdlData ){
		     		$ids[$mdlName] = [];
		     		$resultRecords[$mdlName] = [];
		     		$mdl = "App\Models\\".$mdlName;
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
      		\Log::info("\n------------------------------------------------------------------------------------------------\nException wher run transation\n ");
     		throw $e;
     	}
     	
     	//get updated data after apply formulas
     	$updatedData = [];
     	foreach($resultTransaction['ids'] as $mdlName => $updatedIds ){
//      		$updatedData[$mdlName] = $mdl::findMany($objectIds);
     		$mdl = "App\Models\\".$mdlName;
     		$updatedData[$mdlName] = $mdl::findManyWithConfig($updatedIds);
     	}
//      	\Log::info(\DB::getQueryLog());
    
     	$results = ['updatedData'=>$updatedData,'postData'=>$postData];
     	if (array_key_exists('lockeds', $resultTransaction)) {
	     	$results['lockeds'] = $resultTransaction['lockeds'];
     	}
    	return response()->json($results);
    }
    
    
	protected function preSave(&$editedData, &$affectedIds, $postData) {
		if (array_key_exists ($this->fdcModel, $editedData )) {
			$this->preSaveModel ( $editedData, $affectedIds, $this->valueModel);
			$this->preSaveModel ( $editedData, $affectedIds, $this->theorModel);
		}
	}
	
    protected function afterSave($resultRecords,$occur_date) {
	}
	
	protected function getAffectedObjects($mdlName,$columns,$objectId){
	}
	
    
    public function getUoms($properties = null,$facility_id)
    {
    	$uoms = [];
    	$model = null;
    	$withs = [];
    	$i = 0;
    	$allocTypes = false;
    	
    	foreach($properties as $property ){
    		switch ($property['data']){
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
// 	    				$withs[] = 'CodeAllocType';
	    				$allocTypes = ['id'=>'CodeAllocType','targets'=>$i,'COLUMN_NAME'=>'ALLOC_TYPE'];
	    				$allocTypes['data'] = CodeAllocType::all();
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
    	$rs = [];
    	if ($model!=null) {
	    	foreach($uoms as $key => $uom ){
	    		$uom['data'] = $model->$uom['id'];
	    		$uoms[$key] = $uom;
	    	}
	    	$rs = $uoms;
    	}
    	if ($allocTypes) {
    		$rs[] = $allocTypes;
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
    	$fdcModel = $this->fdcModel;
    	if (array_key_exists($fdcModel, $editedData)) {
    		$idColumn = $this->idColumn;
    		$phaseColumn = $this->phaseColumn;
    
    		if (!array_key_exists($model, $editedData)){
    			$editedData[$model] = [];
    		}
    		foreach ($editedData[$fdcModel] as $element) {
    			$key = array_search($element[$idColumn],array_column($editedData[$model],$idColumn));
    			if ($key===FALSE) {
    				$editedData[$model][] =  array_intersect_key($element, array_flip(array($idColumn,$phaseColumn)));
    			}
    			$affectedIds[]=$element[$idColumn];
    		}
    	}
    }
}
