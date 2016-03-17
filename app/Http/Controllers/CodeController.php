<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\ViewComposers\ProductionGroupComposer;
use Illuminate\Http\Response;
use App\Models\CfgFieldProps;
use App\Models\FlowDataFdcValue;
use App\Models\Flow;
use App\Models\CodeFlowPhase;
use Carbon\Carbon;


class CodeController extends EBController {
	 
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
			$eCollection = $unit->$model(['ID', 'NAME'])->getResults();
			if (count ( $eCollection ) > 0) {
				$unit = ProductionGroupComposer::getCurrentSelect ( $eCollection );
				$results [] = ProductionGroupComposer::getFilterArray ( $model, $eCollection, $unit );
			}
			else break;
		}
		
		return response($results, 200) // 200 Status Code: Standard response for successful HTTP request
			->header('Content-Type', 'application/json');
    }
    
    function getEUQuery($dcTable)
    {
    	global $facility_id,$record_freq,$phase_type,$event_type,$occur_date;
    	$sSQL="select a.FL_NAME,a.PHASE_NAME,a.X_FL_ID, a.FL_FLOW_PHASE,x.*
				from
				(
					select a.name FL_NAME, a.ID X_FL_ID, a.phase_id FL_FLOW_PHASE, c.name PHASE_NAME
					from
					FLOW a,
					CODE_FLOW_PHASE c
					where
					a.phase_id=c.id
					".($record_freq>0?" and a.RECORD_FREQUENCY=$record_freq":"")."
					".($phase_type>0?" and a.phase_id=$phase_type":"")."
				    and a.FDC_DISPLAY='1'
				    and a.EFFECTIVE_DATE<=STR_TO_DATE('$occur_date', '%m/%d/%Y')
				    and a.facility_id='$facility_id'
			    ) a
			    left join $dcTable x on
			    x.flow_id=a.X_FL_ID
			    and x.OCCUR_DATE=STR_TO_DATE('$occur_date', '%m/%d/%Y')
			    order by FL_NAME, FL_FLOW_PHASE";
				    	return $sSQL;
    }
    
    public function load(Request $request)
    {
    	
//      	$options = $request->only('type','value', 'dependences');
//      	$tableName = FlowDataFdcValue::getTable();
    	$input = $request->all();
     	$dcTable = "FLOW_DATA_VALUE";
     	$record_freq = $input['CodeReadingFrequency'];
     	$phase_type = $input['CodeFlowPhase'];
     	$facility_id = $input['Facility'];
     	$occur_date = $input['date_begin'];
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
 				     	->select("$flow.name as FL_NAME", "$flow.ID as X_FL_ID","$flow.phase_id as FL_FLOW_PHASE", "$codeFlowPhase.name as PHASE_NAME","$dcTable.*")
 				     	->orderBy('FL_NAME')
 						->orderBy('FL_FLOW_PHASE')
 						->get();
//  				     	->get();

 				    /* $wp = Flow::join(CodeFlowPhase::getTableName(), $this->table.'.ID', '=', 'USER_WORKSPACE.USER_ID')
 				     	->join('FACILITY', 'USER_WORKSPACE.W_FACILITY_ID', '=', 'FACILITY.ID')
 				     	->join('LO_AREA', 'FACILITY.AREA_ID', '=', 'LO_AREA.ID')
 				     	->join('LO_PRODUCTION_UNIT', 'LO_AREA.PRODUCTION_UNIT_ID', '=', 'LO_PRODUCTION_UNIT.ID')
 				     	->where( $this->table.'.ID', '=', $this->ID)
 				     	->select('USER_WORKSPACE.*', 'USER_WORKSPACE.W_DATE_BEGIN','USER_WORKSPACE.W_DATE_END', 'FACILITY.AREA_ID', 'LO_AREA.PRODUCTION_UNIT_ID')
 				     	->get()->first(); */
				     	
     	
//      	FLOW_DATA_VALUE
//      	FLOW_DATA_FDC_VALUE
     	
    	/* $mdl = 'App\Models\\'.$options['type'];
    	$unit = $mdl::find($options['value']);
    	// 		->value('email');all(['ID', 'NAME']);
    	$results = [];
    
    	foreach($options['dependences'] as $model ){
    		$eCollection = $unit->$model(['ID', 'NAME'])->getResults();
    		if (count ( $eCollection ) > 0) {
    			$unit = ProductionGroupComposer::getCurrentSelect ( $eCollection );
    			$results [] = ProductionGroupComposer::getFilterArray ( $model, $eCollection, $unit );
    		}
    		else break;
    	} */
    	
    	$properties = CfgFieldProps::where('TABLE_NAME', '=', $dcTable)
            ->where('USE_FDC', '=', 1)
            ->orderBy('FIELD_ORDER')
            ->get(['COLUMN_NAME as data', 'FDC_WIDTH','LABEL as title']);
    	
        $properties->prepend(['data'=>'FL_NAME','title'=>'Object name']);
            
    	return response()->json(['properties' => $properties,'dataSet'=>$dataSet]);
    }
    
}
