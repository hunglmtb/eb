<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\EuPhaseConfig;
use App\Models\UserWorkspace;
use App\Models\Facility;
use App\Models\LoArea;
use App\Models\LoProductionUnit;
use App\Models\CodeFlowPhase;
use App\Models\CodeAllocType;
use App\Models\CodePlanType;
use App\Models\CodeForecastType;

use DB;
use Carbon\Carbon;

class graphController extends Controller {
	
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	
	public function _index() {
		$workSpace = $this->getWorkSpaceInfo();
		
		$facility = Facility::select('ID')->first();
		$facility_id = $facility->ID;
		
		$dt = new \DateTime();
		$date_begin = $dt->format('m/d/Y');
		$date_end = $dt->format('m/d/Y');
		
		if(count($workSpace) > 0){
						
			if($workSpace->W_FACILITY_ID != null){
				$facility_id = $workSpace->W_FACILITY_ID;
			}
			
			if($workSpace->DATE_BEGIN != null){
				$date_begin = $workSpace->DATE_BEGIN;
			}
			
			if($workSpace->DATE_END != null){
				$date_end = $workSpace->DATE_END;
			}
			
			if($workSpace->PRODUCTION_UNIT_ID != null){
				$production_unit_id = $workSpace->PRODUCTION_UNIT_ID;
			}
			
			if($workSpace->AREA_ID != null){
				$area_id = $workSpace->AREA_ID;
			}
		}
		
		$data = [
			'facility_id'=>$facility_id,
			'product_type' => 0,
			'date_begin'=>$date_begin,
			'date_end'=>$date_end,
			'object_type'=>'FLOW'
		];
		
		$code_alloc_type = CodeAllocType::where(['ACTIVE'=>1])->get(['ID', 'NAME']);
		$code_plan_type = CodePlanType::where(['ACTIVE'=>1])->get(['ID', 'NAME']);
		$code_forecast_type = CodeForecastType::where(['ACTIVE'=>1])->get(['ID', 'NAME']);
		
		$tmp = $this->loadObjectname($data);
		
		return view ( 'front.graph',['result'=>$tmp, 'workSpace'=>$workSpace, 'code_alloc_type'=>$code_alloc_type, 
				'code_plan_type'=>$code_plan_type, 'code_forecast_type'=>$code_forecast_type]);
	}
	
	public function loadVizObjects(Request $request){
		$data = $request->all ();	
		
		$tmp = $this->loadObjectname($data);
		//\Log::info ( \DB::getQueryLog () );
	
		return response ()->json ( ['result' => $tmp] );
	}
	
	private function loadObjectname($data){
		
		$facility_id = $data['facility_id'];
		$product_type = $data['product_type'];
		$date_begin = $data['date_begin'];
		$date_end = $data['date_end'];
		$object_type = $data['object_type'];
		
		if($date_begin && $facility_id){
			$this->saveWorkSpaceInfo($date_begin, $date_end, $facility_id);
		}
		
		$obj_types=explode("/",$object_type);
		
		$table_name=$obj_types[0];
		$entity = strtolower(str_replace('_', ' ', $table_name));
		$entity = ucwords($entity);
		$entity = str_replace(' ', '', $entity);
		
		$tmp = [];
		$model = 'App\\Models\\' . $entity;
		//\DB::enableQueryLog ();
		switch ($table_name){
			case "TANK":
			case "STORAGE":
				$tmp = $model::where(['FACILITY_ID'=>$facility_id])
				->where ( function ($q) use ($product_type) {
					if ($product_type != 0) {
						$q->where ( [
								'PRODUCT' => $product_type
						] );
					}
				})->get(['ID', 'NAME']);
				break;
					
			case "FLOW":
				$tmp = $model::where(['FACILITY_ID'=>$facility_id])
				->where ( function ($q) use ($product_type) {
					if ($product_type != 0) {
						$q->where ( [
								'PHASE_ID' => $product_type
						] );
					}
				})->get(['ID', 'NAME']);
				break;
					
			case "ENERGY_UNIT":
				$tableName = $model::getTableName ();
				$euPhaseConfig = EuPhaseConfig::getTableName ();
				$tmp = DB::table($tableName.' AS a')
				->where(['FACILITY_ID'=>$facility_id])
				->whereNotExists(function($query) use ($euPhaseConfig, $product_type){
					$query->select(DB::raw('A.ID'))
					->from($euPhaseConfig.' AS b')
					->whereRaw('b.EU_ID = a.ID')
					->where(['b.FLOW_PHASE'=>$product_type]);
				})->get(['ID', 'NAME']);
				break;
		}
		//\Log::info ( \DB::getQueryLog () );
		
		return $tmp;
	}
	
	public function saveWorkSpaceInfo($date_begin, $date_end, $facility_id)
	{
		$user_name = '';
		if((auth()->user() != null)){
			$user_name = auth()->user()->username;
		}
		
		if(!$user_name) return;
		
		$date_begin = Carbon::createFromFormat('m/d/Y',$date_begin)->format('Y-m-d');
		$date_end = Carbon::createFromFormat('m/d/Y',$date_end)->format('Y-m-d');		
		
		$condition = array (
				'USER_NAME' => $user_name
		);
		
		$obj ['W_DATE_BEGIN'] = $date_begin;
		$obj ['W_DATE_END'] = $date_end;
		$obj ['W_FACILITY_ID'] = $facility_id;
		
		//\DB::enableQueryLog ();
		UserWorkspace::updateOrCreate ( $condition, $obj );
		//\Log::info ( \DB::getQueryLog () );		
	}
	
	private function getWorkSpaceInfo(){
	
		$user_name = '';
		if((auth()->user() != null)){
			$user_name = auth()->user()->username;
		}
	
		$user_workspace = UserWorkspace::getTableName();
		$facility = Facility::getTableName();
		$lo_area = LoArea::getTableName();
		$lo_production_unit = LoProductionUnit::getTableName();
	
		$workspace = DB::table($user_workspace.' AS a')
		->join($facility.' AS b', 'a.W_FACILITY_ID', '=', 'b.ID')
		->join($lo_area.' AS c', 'b.AREA_ID', '=', 'c.ID')
		->join($lo_production_unit.' AS d', 'c.PRODUCTION_UNIT_ID', '=', 'd.ID')
		->where(['a.USER_NAME'=>$user_name])
		->select('a.*',DB::raw('DATE_FORMAT(a.W_DATE_BEGIN, "%m/%d/%Y") as DATE_BEGIN'), DB::raw('DATE_FORMAT(a.W_DATE_END, "%m/%d/%Y") as DATE_END'),
				'b.AREA_ID', 'c.PRODUCTION_UNIT_ID' )
				->first();
	
		return $workspace;
	}
	
	public function loadEUPhase(Request $request){
		$data = $request->all ();		
		$eu_id=$data['eu_id'];
		
		$EuPhaseConfig = EuPhaseConfig::getTableName();
		$code_flow_phase = CodeFlowPhase::getTableName();
		
		//\DB::enableQueryLog ();
		$tmp = DB::table($EuPhaseConfig.' AS a')
		->join($code_flow_phase.' AS b', 'a.FLOW_PHASE', '=', 'b.ID')
		->where(['a.EU_ID'=>$eu_id])
		->get(['b.ID', 'b.NAME'] );
		//\Log::info ( \DB::getQueryLog () );
		return response ()->json ( ['result' => $tmp] );
	}
	
	public function loadChart($title, $minvalue, $maxvalue, $date_begin, $date_end, $input){
		$isrange=(is_numeric($minvalue) && $maxvalue>$minvalue);
	}
}