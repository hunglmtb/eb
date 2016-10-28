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
use App\Models\UomConversion;
use App\Models\AdvChart;
use App\Models\Formula;
use App\Models\CfgFieldProps;

use DB;
use Carbon\Carbon;

class graphController extends Controller {
	
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	
	public function _index() {
		$codeFlowPhase	= ["name"		=>	"CodeFlowPhase",
							"source"	=>	"ObjectName" ];
		$filterGroups = array(	'productionFilterGroup'	=>[	['name'			=>'CodeProductType',
															'independent'	=>true,
															'extra'			=> ["Facility","CodeProductType","IntObjectType"],
															'dependences'	=>["ObjectName",
																				$codeFlowPhase]],
															['name'			=>'IntObjectType',
															'independent'	=>true,
															"getMethod"		=> "getGraphObjectType",
															'extra'			=> ["Facility","CodeProductType","IntObjectType"],
															'dependences'	=>[	"ObjectName",
																				["name"		=>	"ObjectDataSource"],
																				"ObjectTypeProperty",
																				$codeFlowPhase
																			]
															]
														],
								'frequenceFilterGroup'	=> [	["name"			=> "ObjectName",
																"getMethod"		=> "loadBy",
																'dependences'	=> ["CodeFlowPhase"],
																"source"		=> ['productionFilterGroup'=>["Facility","IntObjectType","CodeProductType"]]],
																["name"			=> "ObjectDataSource",
																"getMethod"		=> "loadBy",
																"filterName"	=>	"Data source",
																'dependences'	=> ["ObjectTypeProperty"],
																"source"		=> ['productionFilterGroup'=>["IntObjectType"]]],
																["name"			=> "ObjectTypeProperty",
																"getMethod"		=> "loadBy",
																"filterName"	=>	"Property",
																"source"		=>  ['frequenceFilterGroup'=>["ObjectDataSource"]]],
																["name"			=> "CodeFlowPhase",
																"getMethod"		=> "loadBy",
																"source"		=>  ['frequenceFilterGroup'=>["ObjectName"]]],
																"CodeAllocType",
																["name"			=>	"CodePlanType",
																 "filterName"	=>	"Plan type",
																],
																["name"			=>	"CodeForecastType",
																"filterName"	=>	"Forecast type",
																]
															],
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
								'enableButton'			=> false,
								'FacilityDependentMore'	=> ["ObjectName","CodeFlowPhase"],
								'extra' 				=> ['IntObjectType','CodeProductType']
		);
		
		return view ( 'front.graph',['filters'			=> $filterGroups]);
	}
	
	private function getDataSource($code){
		$result = null;
	
		$datasource = config("constants.tab");
		$result = $datasource[$code];
		
		return $result;
	}
	
	public function loadVizObjects(Request $request){
		$data = $request->all ();	
		
		$tmp = $this->loadObjectname($data);
		
		$tab = $this->getTab($data);
	
		return response ()->json ( ['result' => $tmp, 'tab'=>$tab] );
	}
	
	private function getTab($data){
		$object_type = $data['object_type'];
		
		$obj_types=explode("/",$object_type);
		
		$table_name=$obj_types[1];
		if($table_name == 'TANK')
			$table_name = 'STORAGE';
		
		return $this->getDataSource($table_name);
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
	
	public function getProperty(Request $request){
		$data = $request->all ();	
		$result = array ();
		$model = 'App\\Models\\' . $data['table'];
		$tableName = $model::getTableName ();
		
		$tmp  = CfgFieldProps::where(['USE_FDC'=>1, 'TABLE_NAME'=>$tableName])->get(['COLUMN_NAME AS CODE', 'LABEL AS NAME']);
		
		if(count($tmp) > 0){
			foreach ($tmp as $t){
				if($t->NAME == '' || is_null($t->NAME)){
					$t->NAME = $t->CODE;
				}
				array_push($result, $t);
			}
		}
		
		return response ()->json ( $result );
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
		$date_begin = Carbon::createFromFormat('m-d-Y',$date_begin)->format('Y-m-d');
		$date_end = Carbon::createFromFormat('m-d-Y',$date_end)->format('Y-m-d');
		
		$ss=explode(",",$input);
		$k=0;
		$maxV=0;
		$minV=PHP_INT_MAX;
		$strData = "";
		foreach($ss as $s)
		{
			$tmp = [];
			$phase_type = -1;
			
			$xs=explode(":",$s);
			$chart_name=$xs[5];
			$chart_type=$xs[4];
			$types=explode("~",$xs[3]);
			$vfield=$types[0];
		
			$datefield="OCCUR_DATE";
			$is_eutest=false;
			if(substr($xs[2], 0, strlen("EU_TEST")) === "EU_TEST")
			{
				$is_eutest=true;
				$datefield="EFFECTIVE_DATE";
			}
			if($xs[0]=="TANK")
				$obj_type_id_field="TANK_ID";
			else if($xs[0]=="STORAGE")
				$obj_type_id_field="STORAGE_ID";
			else if($xs[0]=="FLOW")
				$obj_type_id_field="FLOW_ID";
			else if($xs[0]=="ENERGY_UNIT"){
				$obj_type_id_field="EU_ID";
				$chart_type=$xs[5];
				$chart_name=$xs[6];
				$vfield=$xs[3];
				$types=explode("~",$xs[4]);
				$phase_type=$types[0];
			}
		
			$pos=strpos($xs[3],"@");
			if($pos>0)
			{
				$xs[3]=substr($xs[3],$pos+1);
			}
			
			$table_name = $xs[2];
			$entity = strtolower(str_replace('_', ' ', $table_name));
			$entity = ucwords($entity);
			$entity = str_replace(' ', '', $entity);
			
			$model = 'App\\Models\\' . $entity;
					
			$va = $xs[0];
			$pa1 = $xs[1];
			//\DB::enableQueryLog ();
			
			$tmp = $model::where([$obj_type_id_field=>$pa1])
			->where ( function ($q) use ($va, $is_eutest, $phase_type) {
				if ($va == "ENERGY_UNIT" && !$is_eutest) {
					$q->where ( [
							'FLOW_PHASE' => $phase_type
					] );
				}
			})
		 	->whereDate ( $datefield, '>=',  $date_begin ) 
			->whereDate ( $datefield, '<=',  $date_end )
			->orderBy ( $datefield )
			->take(300)
			->get([$vfield.' AS V', DB::raw('DATE_FORMAT('.$datefield.', "%Y,%m-1,%d") AS D')]);
			//\Log::info ( \DB::getQueryLog () );
			$i=0;
			
			if($k>0){
				$strData .=",{";
			}else{
				$strData .="{";
			}
			$strData .= "type: '".$chart_type."',\n";
			$strData .= "name: '".preg_replace('/\s+/', '_@', $chart_name)."',\n";
			
			//$strData .= "name: '".$chart_name."',";
			$strData .= "data: [";
			foreach ($tmp as $row)
			{
				if($row->V == "") $row ->V=0;
				if($row->V>$maxV)$maxV=$row->V;
				if($row->V<$minV)$minV=$row->V;
				if($i>0){
					$strData .= ",\r\n";
				}
							
				$strData .= "[Date.UTC(".$row->D."), ".$row->V."]";
				$i++;
			}
			$strData .="]}\r\n";
			$k++;
		}
		
		$min1=($minV<0?$minV:0);
		$div=5;
		if($isrange){
			$min1=$minvalue;
			$max1=$maxvalue;
		}
		else{
			$x=ceil($maxV);
			$xs=strval($x);
			$xl=strlen($xs)-1;
			$n=(int)$xs[0];
			$t=pow(10,$xl);
			$x=ceil(2*$maxV/$t)/2;
			$max1=$x*$t;
			if($max1/$div*($div-1)>$maxV){
				$max1 = $max1/$div*($div-1);
				$div -= 1;
			}
		}
		$tickInterval1=($max1-($min1>0?$min1:0))/$div;
		
		$tickInterval2=0;
		$min2=0;
		$max2 = 0;
		$x = $this->convertUOM($tickInterval1,'kL','m3');
		if(is_numeric($x)){
			$tickInterval2=$x;
			if($isrange)
				$min2 = $this->convertUOM($min1,'kL','m3');
		}
		if($tickInterval2>0){
			$max2=($min2<0?0:$min2)+$tickInterval2*$div;
		}
		
		return view('front.graph_loadchart', [
				'min1'=>$min1,
				'max1'=>$max1,
				'min2'=>$min2,
				'max2'=>$max2,
				'title'=>($title != "null")?$title:"",
				'series'=>$strData
		]);
	}
	
	private function convertUOM($value,$from_uom,$to_uom){
		if(is_numeric($value)){
			$uom = UomConversion::where(['CODE'=>$from_uom, 'TO_CODE'=>$to_uom])->select('MULTIPLY_BY', 'PLUS_TO')->first();
			
			$result = $uom->MULTIPLY_BY*$value + $uom->PLUS_TO;
			
			return $result;
		}
		return false;
	}
	
	public function getListCharts(){		
		$formula = Formula::where(['GROUP_ID'=>8])->orderBy('ID')->get(['ID', 'NAME']);
		
		return response ()->json ( ['adv_chart' => $this->getChart(), 'formula'=>$formula] ); 
	}
	
	public function deleteChart(Request $request){
		$data = $request->all();
		
		AdvChart::where(['ID'=>$data['ID']])->delete();
		
		return response ()->json ( ['adv_chart' => $this->getChart()] );
	}
	
	private function getChart(){
		
		$adv_chart = AdvChart::orderBy('TITLE')->get();
		
		return $adv_chart;
	}
	
	public function saveChart(Request $request){
		$data = $request->all();
		
		$id = $data['id'];		
		$title = addslashes($data["title"]);
		$config = addslashes($data["config"]);
		$minvalue = $data["minvalue"];
		$maxvalue = $data["maxvalue"];
		if(!is_numeric($minvalue)) $minvalue = "null";
		if(!is_numeric($maxvalue)) $maxvalue = "null";
		
		$user_name = '';
		if((auth()->user() != null)){
			$user_name = auth()->user()->username;
		}
		
		$now = Carbon::now('Europe/London');
		$time = date('Y-m-d H:i:s', strtotime($now));
		
		$adv_chart = AdvChart::where(['ID'=>$id])->get(['ID']);
		
		if(count($adv_chart) > 0){
			AdvChart::where(['ID'=>$id])->update(['TITLE'=>$title, 'CONFIG'=>$config, 'MAX_VALUE'=>$maxvalue, 'MIN_VALUE'=>$minvalue]);
		}else{
			$adv_chart = AdvChart::insert(['TITLE'=>$title, 'CONFIG'=>$config, 'MAX_VALUE'=>$maxvalue, 'MIN_VALUE'=>$minvalue, 'CREATE_BY'=>$user_name, 'CREATE_DATE'=>$time]);
			$id = $adv_chart['ID'];
		}
		
		return response ()->json ( "ok:$id" );
	}
	
	//=============
	public function _indexViewConfig() {
		return view ( 'front.viewconfig');
	}
	
	public function loadPlotObjects(Request $request){
		$data = $request->all ();	
		
		$tmp = $this->loadPlotObjectname($data);
		//\Log::info ( \DB::getQueryLog () );
	
		return response ()->json ( ['result' => $tmp] );
	}
	
	private function loadPlotObjectname($data){
	
		$facility_id = $data['facility_id'];
		$product_type = $data['product_type'];
		$object_type = $data['object_type'];
	
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
}