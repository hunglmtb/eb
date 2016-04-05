<?php

namespace App\Http\Controllers;
use App\Http\ViewComposers\ProductionGroupComposer;
use App\Models\CfgFieldProps;
use App\Models\CodeFlowPhase;
use App\Models\CodePressUom;
use App\Models\Facility;
use App\Models\Flow;
use App\Models\StandardUom;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class CodeController extends EBController {
	 
	
	/* public function __construct()
	{
		parent::__construct();
		$this->middleware('saveWorkspace');
	} */
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
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
				$results [] = ProductionGroupComposer::getFilterArray ( $model, $eCollection, $unit );
			}
			else break;
		}
		
		return response($results, 200) // 200 Status Code: Standard response for successful HTTP request
			->header('Content-Type', 'application/json');
    }
    
    public function load(Request $request)
    {
//     	sleep(2);
    	$postData = $request->all();
    	$mdl = "App\Models\\".($postData[config("constants.tabTable")]);
     	$dcTable = $mdl::getTableName();//"FLOW_DATA_VALUE";
     	$record_freq = $postData['CodeReadingFrequency'];
     	$phase_type = $postData['CodeFlowPhase'];
     	$facility_id = $postData['Facility'];
     	$occur_date = $postData['date_begin'];
     	$occur_date = Carbon::parse($occur_date);
     	
     	$flow = Flow::getTableName();
     	$codeFlowPhase = CodeFlowPhase::getTableName();
     	
     	$where = ['facility_id' => $facility_id, 'FDC_DISPLAY' => 1];
     	if ($record_freq>0) {
     		$where["$flow.record_frequency"]= $record_freq;
     	}
     	if ($phase_type>0) {
     		$where['phase_id']= $phase_type;
     	}
     	
     	
     	$dataSet = Flow::join($codeFlowPhase,'PHASE_ID', '=', "$codeFlowPhase.ID")
     					->where($where)
     					->where('EFFECTIVE_DATE', '<=', $occur_date)
     					->where('OCCUR_DATE', '=', $occur_date)
     					->leftJoin($dcTable, "$flow.ID", '=', "$dcTable.flow_id")
 				     	->select("$flow.name as FL_NAME",
								"$flow.ID as DT_RowId",
 				     			"$flow.ID as X_FL_ID",
 				     			"$flow.phase_id as FL_FLOW_PHASE",
 				     			"$codeFlowPhase.name as PHASE_NAME",
//  				     			"$dcTable.ID as DT_RowId",
 				     			"$dcTable.*"
 				     			)
 				     	->orderBy('FL_NAME')
 						->orderBy('FL_FLOW_PHASE')
 						->get();
    	
    	$properties = CfgFieldProps::where('TABLE_NAME', '=', $dcTable)
            ->where('USE_FDC', '=', 1)
            ->orderBy('FIELD_ORDER')
            ->get(['COLUMN_NAME as data','COLUMN_NAME as name', 'FDC_WIDTH as width','LABEL as title']);
    	
        $properties->prepend(['data'=>'FL_NAME','title'=>'Object name','width'=>230]);
        
        $uoms = $this->getUoms($properties,$facility_id);
		
        $dswk = $dataSet->keyBy('DT_RowId');
        $objectIds = $dswk->keys();
        
    	return response()->json(['properties' => $properties,
    							'dataSet'=>$dataSet,
    							'uoms'=>$uoms,
     							'objectIds'=>$objectIds,
    							'postData'=>$postData]);
    }
    
    
    public function save(Request $request)
    {
//     	sleep(2);
    	$postData = $request->all();
    	$editedData = $postData['editedData'];
     	$record_freq = $postData['CodeReadingFrequency'];
     	$phase_type = $postData['CodeFlowPhase'];
     	$facility_id = $postData['Facility'];
     	$occur_date = $postData['date_begin'];
     	$occur_date = Carbon::parse($occur_date);
     	$objectIds = $postData['objectIds'];
     	
     	$flow = Flow::getTableName();
     	$updatedData = [];
     	$ids = [];
//      	\DB::enableQueryLog();
     	foreach($editedData as $mdlName => $mdlData ){
     		$mdl = "App\Models\\".$mdlName;
//      		$updatedData[$mdlName] = [];
     		$ids[$mdlName] = [];
     		foreach($mdlData as $key => $newData ){
     			$columns = ['FLOW_ID'=>$newData['FLOW_ID'],'OCCUR_DATE'=>$occur_date];
      			$newData['OCCUR_DATE']=$occur_date;
     			$returnRecord = $mdl::updateOrCreate($columns, $newData);
     			$ids[$mdlName][] = $returnRecord['ID'];
//      			$updatedData[$mdlName][] = $returnRecord;
//      			$objectIds[]=$newData['FLOW_ID'];
     		}
     	}
     	
     	$objectIds = array_unique($objectIds);
     	//doFormula in config table
     	$affectColumns = [];
     	foreach($editedData as $mdlName => $mdlData ){
     		$cls  = \FormulaHelpers::doFormula($mdlName,'ID',$ids[$mdlName]);
     		if (is_array($cls)&&count($cls)>0) {
	     		$affectColumns[$mdlName] = $cls;
     		}
     	}
     	
     	//get affected ids
     	$affectedIds = [];
     	foreach($editedData as $mdlName => $mdlData ){
     		foreach($mdlData as $key => $newData ){
     			$columns = array_keys($newData);
     			if (array_key_exists($mdlName, $affectColumns)) {
	     			$columns = array_merge($columns,$affectColumns[$mdlName]);
     			}
				$columns = array_diff($columns, ['FLOW_ID']);
     			
	     		$aIds = \FormulaHelpers::getAffects($mdlName,$columns,$newData['FLOW_ID'],'FLOW');
	     		$affectedIds = array_merge($affectedIds,$aIds);
     		}	     
     	}
     	$affectedIds = array_unique($affectedIds);
     	
     	//apply Formula in formula table
     	foreach($editedData as $mdlName => $mdlData ){
     		$upids = \FormulaHelpers::applyFormula($mdlName,$affectedIds,$occur_date,'FLOW',true);
     		$ids[$mdlName] = array_merge($ids[$mdlName], $upids);
//      		$ids[$mdlName] = array_intersect($ids[$mdlName], $objectIds);
     		$ids[$mdlName]  = array_unique($ids[$mdlName]);
     	}
     	
     	//get updated data after apply formulas
     	foreach($ids as $mdlName => $updatedIds ){
//      		$updatedData[$mdlName] = $mdl::findMany($objectIds);
     		$mdl = "App\Models\\".$mdlName;
     		$updatedData[$mdlName] = $mdl::findMany($updatedIds);
     	}
//      	\Log::info(\DB::getQueryLog());
    
    	return response()->json([
    			/* 'properties' => $properties,
    			'uoms'=>$uoms, */
    			'updatedData'=>$updatedData,
    			'postData'=>$postData]);
    }
    
    public function getUoms($properties = null,$facility_id)
    {
    	$uoms = [];
    	$model = null;
    	$withs = [];
    	$i = 0;
    	
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
    				$withs[] = 'CodePowerUom';
    				$uoms[] = ['id'=>'CodePowerUom','targets'=>$i,'COLUMN_NAME'=>'FL_POWR_UOM'];
    				break;
    			case 'FL_ENGY_UOM' :
	    			$withs[] = 'CodeEnergyUom';
	    			$uoms[] = ['id'=>'CodeEnergyUom','targets'=>$i,'COLUMN_NAME'=>'FL_ENGY_UOM'];
	    			break;
	    		case 'FL_MASS_UOM' :
		    		$withs[] = 'CodeMassUom';
		    		$uoms[] = ['id'=>'CodeMassUom','targets'=>$i,'COLUMN_NAME'=>'FL_MASS_UOM'];
		    		break;
		    	case 'FL_VOL_UOM' :
	    			$withs[] = 'CodeVolUom';
	    			$uoms[] = ['id'=>'CodeVolUom','targets'=>$i,'COLUMN_NAME'=>'FL_VOL_UOM'];
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
	    	}
	    	return $uoms;
    	}
    	return [];
    	 
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
}
