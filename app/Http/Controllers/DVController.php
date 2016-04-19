<?php

namespace App\Http\Controllers;
use App\Models\CodeFlowPhase;
use App\Models\NetWork;

use App\Models\LoArea;
use App\Models\LoProductionUnit;
use App\Models\Facility;
use App\Models\IntObjectType;
use App\Models\CfgFieldProps;
use App\Models\IntConnection;
use App\Models\IntTagMapping;
use DB;
use Carbon\Carbon;

use Illuminate\Http\Request;

class DVController extends Controller {
	
	
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	
	public function _indexDiagram() {
		
		$codeFlowPhase = CodeFlowPhase :: all(['ID', 'NAME']);
		
		$loProductionUnit = LoProductionUnit::all(['ID', 'NAME']);
		
		$loArea = LoArea::where(['PRODUCTION_UNIT_ID'=>$loProductionUnit[0]->ID])->get(['ID', 'NAME']);
		
		$facility = Facility::where(['AREA_ID'=>$loArea[0]->ID])->get(['ID', 'NAME']);
		
		$intObjectType = IntObjectType::where(['DISPLAY_TYPE'=>1])->get(['CODE', 'NAME']);
		
		$tmp = ucwords($intObjectType[0]->NAME);
		
		$mode = 'App\\Models\\' .str_replace(' ','' , $tmp);
		
		$type = $mode::where(['FACILITY_ID'=>$facility[0]->ID])->get(['ID', 'NAME']);
		
		return view ( 'front.diagram', ['codeFlowPhase'=>$codeFlowPhase,
										'loProductionUnit'=>$loProductionUnit,
										'loArea'=>$loArea,
										'facility'=>$facility,
										'intObjectType'=>$intObjectType,
										'type'=>$type
									   ]);
	}
	
	public function onChangeObj(Request $request){
		$data = $request->all();		

		$mode = 'App\\Models\\' .str_replace(" ", "", $data['TABLE']);
	
		$result = $mode::where([$data['keysearch']=>$data['value']])->get(['ID', 'NAME']);

		return response()->json($result);
	}
	
	public function getdiagram(Request $request){
		
		$tmp = NetWork::where(['NETWORK_TYPE'=>2])->get(['ID', 'NAME']);
		return response()->json($tmp);
	}
	
	public function loaddiagram($id){		
		$tmp = NetWork::where(['ID'=>$id])->select('XML_CODE')->first();
		return response($tmp->XML_CODE);
	}
	
	public function deletediagram(Request $request){
		\DB::enableQueryLog();
		NetWork::where(['ID'=>$request->ID])->delete();
		\Log::info(\DB::getQueryLog());
		$tmp = NetWork::where(['NETWORK_TYPE'=>2])->get(['ID', 'NAME']);
		return response()->json($tmp);
	}
	
	public function savediagram(Request $request){
		$data = $request->all();
		
		$condition = array(
				'ID'=>$data['ID']
		);		
			
		$obj['NAME'] = $data['NAME'];
		$obj['XML_CODE'] = urldecode($data['KEY']);
		$obj['NETWORK_TYPE'] = 2;
			
		\DB::enableQueryLog();
		$result = NetWork::updateOrCreate($condition,$obj);
		\Log::info(\DB::getQueryLog());
	
		return response()->json($result->ID);
	}
	
	public function getSurveillanceSetting(Request $request){
		$data = $request->all();
		$cfgFieldProps = array();
		$tags = array();
		$surs= array();
		$objType_id= -100;
		$objectType= "";
		$tag_other = array();
		$other = "";
		$strMessage = null;
		
		if(isset($data['OBJECT_TYPE'])){
			$objectType= $data['OBJECT_TYPE'];
		}
		
		if(isset($data['OBJECT_ID'])){
			$objType_id= $data['OBJECT_ID'];
		}
		
		if(isset($data['SUR'])){
			$surs=explode('@',$data['SUR']);
		}
		
		$cfgFields = CfgFieldProps::where(['USE_DIAGRAM'=>1])
						->where('TABLE_NAME', 'like', $objectType.'%')
						->orderBy('TABLE_NAME', 'COLUMN_NAME')
						->get(['COLUMN_NAME', 'TABLE_NAME', 'LABEL']);
		
		if(count($cfgFields) > 0){
			foreach ($cfgFields as $v){
				$value = $v->TABLE_NAME.".".$v->COLUMN_NAME;
				
				$checked=(in_array($value, $surs, TRUE)?"checked":"");
				
				$v['CHECK'] = $checked;
				
				array_push($cfgFieldProps, $v);
			}
		}
		
		$intConnection = IntConnection::all(['ID', 'NAME']);
		
		$intTagMapping = IntTagMapping::getTableName();
		$intObjectType = IntObjectType::getTableName();
		
		\DB::enableQueryLog();
		$vTags = DB::table($intTagMapping.' AS a')
		->join($intObjectType.' AS b', 'a.OBJECT_TYPE', '=', 'b.ID')
		->where(['b.CODE'=>$objectType])		
		->where(function($q) use ($objType_id) {
			if($objType_id != -100){
				$q->where(['a.OBJECT_ID' => $objType_id]);
			}
		})		
		->distinct()
		->orderBy('a.TAG_ID')
		->get(['a.TAG_ID', 'a.TAG_ID AS CHECK']);		
		\Log::info(\DB::getQueryLog());
		
		if(count($vTags) > 0){
			foreach ($vTags as $t){
			
				$checked=(in_array($t->TAG_ID, $surs, TRUE)?"checked":"");
			
				$t->CHECK = $checked;
			
				array_push($tags, $t);
			}
		}
		
		if(count($vTags) <= 0){
			$names = IntObjectType::where(['CODE'=>$objectType])->select('NAME')->first();
			$sname = (count($names)>0?$names->NAME:"");
			$strMessage = '<br><br><br><br><center><font color="#88000">No tag configured for <b>'.$sname.'</b>.</font><br><br><input type="button" onclick=\"window.open("../config/tagsmapping.php", "_blank");\" value="Config Tag Mapping"></center>';
		}
			
		if($objType_id == -100){
			$strMessage = '<br><br><br><br><center><font color="#880000">No tag displayed because object is not mapped.</font><br><br><input type="button" style="width:160px;" id="openSurveillanceSetting" value="Object Mapping"></center>';
		}
		
		
		return response()->json(['cfgFieldProps'=>$cfgFieldProps,
								 'intConnection'=>$intConnection,
								 'tags'=>$tags,
								 'strMessage'=>$strMessage
		]);
	}
	
	public function getValueSurveillance(Request $request){
		$data = $request->all();
		$flow_phase = $data['flow_phase'];
		$vparam = $data['vparam'];
		$occur_date = $data['occur_date'];
		
		$date_begin = Carbon::parse($occur_date)->format('Y-m-d 00:00:00');
		$date_end = Carbon::parse($occur_date)->format('Y-m-d 23:59:59');
		
		foreach ($vparam as $v){
			$cell_id = $v['ID'];
			$object_type = $v['OBJECT_TYPE'];
			$object_id = $v['OBJECT_ID'];
			$conn_id = $v['CONN_ID'];
			$phase_config = $v['SUR_PHASE_CONFIG'];
			$su = $v['SU'];
			
			if($object_type == 'ENERGY_UNIT'){
				$phase_configs = explode("!!",$phase_config);
				
				$phase0 = explode("@@",$phase_configs[0]);
				$phase1 = explode("/",$phase_configs[1]);
				
				$table = $phase1[0];
				$field = $phase1[1];				
				if(!$field) $field="EU_DAY_GRS_VOL";
				if(!$table) $table="ENERGY_UNIT_DAY_VALUE";
				
				if(count($phase0) > 0){
					$flow_phases = "-1";
					$datas = array();
					foreach($phase0 as $a1){
						$as2 = explode("^^",$a1);
						if($as2[0]){
							$flow_phases.=",".$as2[0];
							$datas[$as2[0]]=$as2;
						}
					}
					$table = strtolower($table);
					$table = str_replace(' ','',ucwords(str_replace('_', ' ', $table)));
					$model = 'App\\Models\\' . $table;
					\DB::enableQueryLog();
					$conditions = explode(',', $flow_phases);
					$tmps = $model::where(['EU_ID'=>$object_id])
									->whereDate('OCCUR_DATE', '=', Carbon::parse($occur_date))
									->whereIn('FLOW_PHASE', $conditions)
									->get([$field.' AS FIELD_VALUE', 'FLOW_PHASE']);
					\Log::info(\DB::getQueryLog());
					$arr = array();
					foreach ($tmps as $tmp){
						$value = $tmp->FIELD_VALUE;
						if(is_numeric($value)){
							$arr[$tmp->FLOW_PHASE] = number_format($value,2);
						}else{
							$arr[$tmp->FLOW_PHASE]="--";
						}
					}
					$cells_data["$cell_id"]["%SF"]=$arr;					
				}
			}
			
			$field_tables=explode("@",$su);
			$label="";
			
			foreach($field_tables as $field_table){
				if(strpos($field_table,"@TAG:")!==FALSE){
					if($conn_id>0){
						$conn_objs["$conn_id"][]="$cell_id~$field_table";
					}
				}else{
					if($field_table){
						$f=explode(".", $field_table);
						$table=$f[0];
						$field=$f[1];
						$label = CfgFieldProps::where(['TABLE_NAME'=>$table, 'COLUMN_NAME'=>$field])->select('LABEL')->first();
						if(count($label) > 0){
							$xlabel = $label->LABEL;
						}else{
							$xlabel = "$table.$field";
						}
						
						$table = strtolower($table);
						$table = str_replace(' ','',ucwords(str_replace('_', ' ', $table)));
						$model = 'App\\Models\\' . $table;
						
						$condition = array();
						if($object_type == 'FLOW'){
							$value="--";
							$condition['FLOW_ID'] = $object_id;
						}else{						
							if($object_type == 'TANK'){
								$value="--";
								$condition['TANK_ID'] = $object_id;
							}else{
								if($object_type == 'STORAGE'){
									$value="--";
									$condition['STORAGE_ID'] = $object_id;
								}else{
									if($object_type == 'EQUIPMENT'){
										$value="--";
										$condition['EQUIPMENT_ID'] = $object_id;
									}else{
										if($object_type == 'ENERGY_UNIT'){
											$value="--";
											$condition['EU_ID'] = $object_id;
											$condition['FLOW_PHASE'] = $flow_phase;
										}else{
											$value="--";
										}
									}
								}
							}
						}
							
						\DB::enableQueryLog();
						$values = $model::where($condition)
										->whereDate('OCCUR_DATE', '=', Carbon::parse($occur_date)->format('Y-m-d'))
										->SELECT([$field.' AS FIELD_VALUE'])
										->first();
						\Log::info(\DB::getQueryLog());
							
						if(count($values) > 0){
							$value = $values->FIELD_VALUE;
						}
						
						$cells_data["$cell_id"]["$xlabel"]= (is_numeric($value)?number_format($value,2):$value);
					}
				}
			}
		} 
		
		return response()->json();
	}	
	
	
}