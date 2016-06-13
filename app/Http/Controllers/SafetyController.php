<?php

namespace App\Http\Controllers;
use App\Models\CodeSafetyCategory;
use App\Models\FacilitySafetyCategory;


class SafetyController extends CodeController {
    
	/* public function __construct() {
		parent::__construct();
		$this->fdcModel = "FlowDataFdcValue";
		$this->idColumn = config("constants.flowId");
		$this->phaseColumn = config("constants.flFlowPhase");
	
		$this->valueModel = "FlowDataValue";
		$this->theorModel = "FlowDataTheor";
		$this->isApplyFormulaAfterSaving = true;
		$this->keyColumns = [$this->idColumn,$this->phaseColumn];
	} */
	
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'Category','width'=>230];
	}
    public function getDataSet($postData,$safetyTable,$facility_id,$occur_date,$properties){

    	$codeSafetyCategory 	= CodeSafetyCategory::getTableName();
    	$facilitySafetyCategory = FacilitySafetyCategory::getTableName();
    	
    	//      	\DB::enableQueryLog();
    	$dataSet = CodeSafetyCategory::leftJoin($safetyTable, function($join) use ($codeSafetyCategory,$safetyTable,$occur_date,$facility_id){
				    		$join->on("$codeSafetyCategory.ID", '=', "$safetyTable.CATEGORY_ID");
				    		$join->where("$safetyTable.FACILITY_ID",'=',$facility_id);
				    		$join->where("$safetyTable.CREATED_DATE",'=',$occur_date);
				    	})
				    	->join($facilitySafetyCategory, function($join) use ($codeSafetyCategory,$facilitySafetyCategory,$facility_id){
				    		$join->on("$codeSafetyCategory.ID", '=', "$facilitySafetyCategory.SAFETY_CATEGORY_ID");
				    		$join->where("$facilitySafetyCategory.FACILITY_ID",'=',$facility_id);
				    	})
				    	->where("$codeSafetyCategory.active","=",1)
				    	->select(
				    			"$codeSafetyCategory.ID as X_CATEGORY_ID",
				    			"$codeSafetyCategory.ID as DT_RowId",
				    			"$codeSafetyCategory.NAME as $safetyTable",
				    			"$safetyTable.*"
				    			)
// 		    			->orderBy($safetyTable)
		    			->get();
    	//  		\Log::info(\DB::getQueryLog());
    	
    	return ['dataSet'=>$dataSet];
    }
    

	public function loadSafety(Request $request){
	
		$facility_id = $request->input('Facility');
		$created_date = $request->input('date_begin');
			
		// get severity
		$severity = CodeSafetySeverity::where('active', '=', 1)->get();
			
		$obj = new CommonController();
		$cfgFieldProps = $obj->getField('SAFETY')[0];
			
		// search data
		$listData = DB::table('code_safety_category AS a')
		->join('facility_safety_category AS b', function ($join) use($facility_id){
			$join->on('a.id', '=', 'b.safety_category_id')
			->where('b.facility_id', '=', [$facility_id]);
		})
		->leftjoin('safety AS c', function ($ljoin) use ($facility_id, $created_date){
			$ljoin->on('a.id', '=', 'c.category_id')
			->where('c.facility_id', '=', [$facility_id])
			->where('c.created_date','=', [date('Y-m-d',strtotime($created_date))]);
		})
		->select($cfgFieldProps['listColumn'])
		->where('a.active',1)
		->get();
			
		// return
		$result = array(
				[
						'severity' => $severity,
						'search' => $listData,
						'thead' => $cfgFieldProps['listLabel'],
						'totalWidth' => $cfgFieldProps['totalWidth'],
				]
		);
			
		return response()->json($result);
	}
	
	public function saveSafety(Request $request){
		$data = $request->input('_sData');
		$success = 1;
	
		DB::beginTransaction();
	
		try {
			foreach ($data as $obj){
	
				$obj['CATEGORY_ID'] = $obj['XID'];
				unset($obj['XID']);
				$obj['CREATED_DATE'] = date('Y-m-d',strtotime($obj['CREATED_DATE']));
				if(!isset($obj['SEVERITY_ID']) && empty($obj['SEVERITY_ID'])){
					$obj['SEVERITY_ID'] = null;
				}
	
				$condition = array(
						'CATEGORY_ID'=>$obj['CATEGORY_ID'],
						'FACILITY_ID'=>$obj['FACILITY_ID']
				);
				\DB::enableQueryLog();
				Safety::updateOrCreate($condition,$obj);
				\Log::info(\DB::getQueryLog());
			}
				
		} catch(\Exception $e)
		{
			DB::rollback();
			$success = 0;
			return response()->json($success);
		}
	
		DB::commit();
	
		return response()->json($success);
	}
}
