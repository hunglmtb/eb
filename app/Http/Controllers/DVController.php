<?php

namespace App\Http\Controllers;

use App\Models\AllocJob;
use App\Models\CfgFieldProps;
use App\Models\CodeFlowPhase;
use App\Models\EbFunctions;
use App\Models\Facility;
use App\Models\FoGroup;
use App\Models\IntConnection;
use App\Models\IntObjectType;
use App\Models\IntTagMapping;
use App\Models\IntTagSet;
use App\Models\LoArea;
use App\Models\LoProductionUnit;
use App\Models\NetWork;
use App\Models\Params;
use App\Models\TmWorkflow;
use App\Models\TmWorkflowTask;
use App\Models\User;
use App\Models\Dashboard;
use App\Models\EnergyUnit;
use App\Models\Flow;
use Illuminate\Database\Eloquent\Collection;
use App\Jobs\runAllocation;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class DVController extends CodeController {
	
	public function __construct() {
		$this->isReservedName = config ( 'database.default' ) === 'oracle';
		$this->middleware ( 'auth' );
	}
	public function _indexDiagram() {
		$codeFlowPhase = CodeFlowPhase::all ( [ 
				'ID',
				'NAME' 
		] );
		
		$loProductionUnit = LoProductionUnit::all ( [ 
				'ID',
				'NAME' 
		] );
		
		$loArea = LoArea::where ( [ 
				'PRODUCTION_UNIT_ID' => $loProductionUnit [0]->ID 
		] )->get ( [ 
				'ID',
				'NAME' 
		] );
		
		$facility = Facility::where ( [ 
				'AREA_ID' => $loArea [0]->ID 
		] )->get ( [ 
				'ID',
				'NAME' 
		] );
		
		$intObjectType = IntObjectType::where ( [ 
				'DISPLAY_TYPE' => 1 
		] )->get ( [ 
				'CODE',
				'NAME' 
		] );
		
		$tmp = ucwords ( $intObjectType [0]->NAME );
		
		$mode = 'App\\Models\\' . str_replace ( ' ', '', $tmp );
		
		$type = $mode::where ( [ 
				'FACILITY_ID' => $facility [0]->ID 
		] )->get ( [ 
				'ID',
				'NAME' 
		] );
		
		return view ( 'front.diagram', [ 
				'codeFlowPhase' => $codeFlowPhase,
				'loProductionUnit' => $loProductionUnit,
				'loArea' => $loArea,
				'facility' => $facility,
				'intObjectType' => $intObjectType,
				'type' => $type 
		] );
	}
	public function onChangeObj(Request $request) {
		$data = $request->all ();
		
		$mode = 'App\\Models\\' . str_replace ( " ", "", $data ['TABLE'] );
		
		$result = $mode::where ( [ 
				$data ['keysearch'] => $data ['value'] 
		] )->get ( [ 
				'ID',
				'NAME' 
		] );
		
		return response ()->json ( $result );
	}
	public function getdiagram(Request $request) {
		$tmp = NetWork::where ( [ 
				'NETWORK_TYPE' => 2 
		] )->get ( [ 
				'ID',
				'NAME' 
		] );
		return response ()->json ( $tmp );
	}
	public function loaddiagram($id) {
		$tmp = NetWork::where ( [ 
				'ID' => $id 
		] )->select ( 'XML_CODE' )->first ();
		return response ( $tmp->XML_CODE );
	}
	

	public function loadNetworkModel(Request $request) {
		$postData       = $request->all();
		$diagram_id     = $postData['id'];
		$occur_date		= $postData["date"];
// 		$diagram_id		= 52;
		
		$tmp            = NetWork::where ( ['ID' => $diagram_id] )
								->select ( 'XML_CODE' )
								->first ();
		return view ( 'graph.networkmodel',['xml'			=> $tmp?$tmp->XML_CODE:null,
											'diagram_id'	=> $diagram_id,
											'occur_date'	=> $occur_date,
		]);
		
	}
	
	public function deletediagram(Request $request) {
		//\DB::enableQueryLog ();
		NetWork::where ( [ 
				'ID' => $request->ID 
		] )->delete ();
		//\Log::info ( \DB::getQueryLog () );
		$tmp = NetWork::where ( [ 
				'NETWORK_TYPE' => 2 
		] )->get ( [ 
				'ID',
				'NAME' 
		] );
		return response ()->json ( $tmp );
	}
	public function savediagram(Request $request) {
		$data = $request->all ();
		
		$condition = array (
				'ID' => $data ['ID'] 
		);
		
		$obj ['NAME'] = $data ['NAME'];
		$obj ['XML_CODE'] = urldecode ( $data ['KEY'] );
		$obj ['NETWORK_TYPE'] = 2;
		
		//\DB::enableQueryLog ();
		$result = NetWork::updateOrCreate ( $condition, $obj );
		//\Log::info ( \DB::getQueryLog () );
		
		return response ()->json ( $result->ID );
	}
	public function getSurveillanceSetting(Request $request) {
		$data = $request->all ();
		$cfgFieldProps = array ();
		$tags = array ();
		$surs = array ();
		$objType_id = - 100;
		$objectType = "";
		$tag_other = array ();
		$other = "";
		$strMessage = null;
		
		if (isset ( $data ['OBJECT_TYPE'] )) {
			$objectType = $data ['OBJECT_TYPE'];
		}
		
		if (isset ( $data ['OBJECT_ID'] )) {
			$objType_id = $data ['OBJECT_ID'];
		}
		
		if (isset ( $data ['SUR'] )) {
			$surs = explode ( '@', $data ['SUR'] );
		}
		
		$cfgFields = CfgFieldProps::where ( [ 
				'USE_DIAGRAM' => 1 
		] )->where ( 'TABLE_NAME', 'like', $objectType . '%' )->orderBy ( 'TABLE_NAME', 'COLUMN_NAME' )->get ( [ 
				'COLUMN_NAME',
				'TABLE_NAME',
				'LABEL' 
		] );
		
		if (count ( $cfgFields ) > 0) {
			foreach ( $cfgFields as $v ) {
				$value = $v->TABLE_NAME . "/" . $v->COLUMN_NAME;
				
				$checked = (in_array ( $value, $surs, TRUE ) ? "checked" : "");
				
				$v ['CHECK'] = $checked;
				
				array_push ( $cfgFieldProps, $v );
			}
		}
		
		$intConnection = IntConnection::all ( [ 
				'ID',
				'NAME' 
		] );
		
		$intTagMapping = IntTagMapping::getTableName ();
		$intObjectType = IntObjectType::getTableName ();
		
		//\DB::enableQueryLog ();
		$vTags = DB::table ( $intTagMapping . ' AS a' )->join ( $intObjectType . ' AS b', 'a.OBJECT_TYPE', '=', 'b.ID' )->where ( [ 
				'b.CODE' => $objectType 
		] )->where ( function ($q) use ($objType_id) {
			if ($objType_id != - 100) {
				$q->where ( [ 
						'a.OBJECT_ID' => $objType_id 
				] );
			}
		} )->distinct ()->orderBy ( 'a.TAG_ID' )->get ( [ 
				'a.TAG_ID',
				'a.TAG_ID AS CHECK' 
		] );
		//\Log::info ( \DB::getQueryLog () );
		
		if (count ( $vTags ) > 0) {
			foreach ( $vTags as $t ) {
				
				$checked = (in_array ( $t->TAG_ID, $surs, TRUE ) ? "checked" : "");
				
				$t->CHECK = $checked;
				
				array_push ( $tags, $t );
			}
		}
		
		if (count ( $vTags ) <= 0) {
			$names = IntObjectType::where ( [ 
					'CODE' => $objectType 
			] )->select ( 'NAME' )->first ();
			$sname = (count ( $names ) > 0 ? $names->NAME : "");
			$strMessage = '<br><br><br><br><center><font color="#88000">No tag configured for <b>' . $sname . '</b>.</font><br><br><input type="button" style="width:145px;" id="btnTagsMapping" value="Config Tag Mapping"></center>';
		}
		
		if ($objType_id == - 100) {
			$strMessage = '<br><br><br><br><center><font color="#880000">No tag displayed because object is not mapped.</font><br><br><input type="button" style="width:160px;" id="openSurveillanceSetting" value="Object Mapping"></center>';
		}
		
		return response ()->json ( [ 
				'cfgFieldProps' => $cfgFieldProps,
				'intConnection' => $intConnection,
				'tags' => $tags,
				'strMessage' => $strMessage 
		] );
	}
	public function getValueSurveillance(Request $request) {
		$data = $request->all ();
		$flow_phase = $data ['flow_phase'];
		$vparam = $data ['vparam'];
		$occur_date	= $data ['occur_date'];
		$occur_date = $occur_date&&$occur_date!=""?\Helper::parseDate($occur_date):Carbon::now();
// 		$occur_date = Carbon::createFromFormat('m/d/Y',$data ['occur_date'])->format('Y-m-d');
		$ret 		= "";
		
// 		$date_begin = $occur_date->startOfDay();
// 		$date_end 	= $occur_date->endOfDay();
		
		if (!$vparam || !is_array($vparam) ||count($vparam)<=0)  return response ()->json ( "empty param" );
		
		$cells_data = [];
		$fieldConfigs = [];
		$cellConfigs = [];
		
		foreach ( $vparam as $v ) {
			$cell_id 				= $v ['ID'];
			if (!array_key_exists('OBJECT_ID', $v)) continue;
			$object_type 			= $v ['OBJECT_TYPE'];
			$object_id 				= $v ['OBJECT_ID'];
			$conn_id 				= array_key_exists('CONN_ID', $v)?$v ['CONN_ID']:-1;
			$su 					= $v ['SU'];
			$cellConfigs[$cell_id]	= [];
				
			if ($object_type == 'ENERGY_UNIT' && array_key_exists('SUR_PHASE_CONFIG', $v)) {
				$phase_config 		= $v ['SUR_PHASE_CONFIG'];
				foreach ( $phase_config as $phaseObject ) {
					$flowPhase 		= $phaseObject ["phaseId"];
					$eventType 		= $phaseObject ["eventType"];
					$phase1 		= explode ( "/", $phaseObject ["dataField"] );
					$table 			= $phase1 [0];
					$field 			= $phase1 [1];
					if (! $field) $field = "EU_DATA_GRS_VOL";
					if (! $table) $table = "ENERGY_UNIT_DATA_VALUE";
					$model 			= \Helper::getModelName ( $table );

					// 					\DB::enableQueryLog ();
					$tmp 			= 	$model::where ( [ 'EU_ID' => $object_id ] )
												->whereDate ( 'OCCUR_DATE', '=', $occur_date)
												->where([	'FLOW_PHASE'	=> $flowPhase,
															'EVENT_TYPE'	=> $eventType])
												->select ( [
														$field . ' AS FIELD_VALUE',
														'FLOW_PHASE',
														'EVENT_TYPE'])
												->first();
					// 					\Log::info ( \DB::getQueryLog () );
					if ($tmp) {
						$value 					= $tmp->FIELD_VALUE;
						$value 					= is_numeric ( $value )?number_format ( $value, 2 ):"--";
						$phaseObject["value"]	= $value;
						$cellConfigs[$cell_id]	["%SF"][] 	= $phaseObject;
							
						$item	= new EnergyUnit;
						$item->{$item::$idField}	= $object_id;
						$item->DT_RowId				= $object_id;
						if (!array_key_exists($table, $fieldConfigs)) $fieldConfigs[$table] = [	"fields"	=> [$field	=> []],
																								"model"		=> $model	];
						$fieldConfigs[$table]["fields"][$field][$cell_id] = $item;
					}
				}
				/* 
				$phase_configs 		= explode ( "!!", $phase_config );
				
				if (!array_key_exists(1, $phase_configs)) continue;
				
				$phase0 			= explode ( "@@", $phase_configs [0] );
				$phase1 			= explode ( "/", $phase_configs [1] );
				
				$table = $phase1 [0];
				$field = $phase1 [1];
				if (! $field) $field = "EU_DATA_GRS_VOL";
				if (! $table) $table = "ENERGY_UNIT_DATA_VALUE";
				
				if (count ( $phase0 ) > 0) {
					$flow_phases = "-1";
					$datas = array ();
					foreach ( $phase0 as $a1 ) {
						$as2 = explode ( "^^", $a1 );
						if ($as2 [0]) {
							$flow_phases .= "," . $as2 [0];
							$datas [$as2 [0]] = $as2;
						}
					}
					$model = strtolower ( $table );
					$model = str_replace ( ' ', '', ucwords ( str_replace ( '_', ' ', $model ) ) );
					$model = 'App\\Models\\' . $model;
// 					\DB::enableQueryLog ();
					$conditions = explode ( ',', $flow_phases );
					$tmps = $model::where ( [ 'EU_ID' => $object_id ] )
							->whereDate ( 'OCCUR_DATE', '=', $occur_date)
							->whereIn ( 'FLOW_PHASE', $conditions )
							->get ( [ 
									$field . ' AS FIELD_VALUE',
									'FLOW_PHASE' 
							] );
// 					\Log::info ( \DB::getQueryLog () );
					$arr = array ();
					foreach ( $tmps as $tmp ) {
						$value = $tmp->FIELD_VALUE;
						if (is_numeric ( $value )) {
							$arr [$tmp->FLOW_PHASE] = number_format ( $value, 2 );
						} else {
							$arr [$tmp->FLOW_PHASE] = "--";
						}
					}
					$cells_data ["$cell_id"]["%SF"] 	= $arr;
 					$cellConfigs[$cell_id]	["%SF"] 	= [	"table"		=> $table,
								 							"field"		=> $field,
								 							"value"		=> $tmps,
								 							"OBJECT_ID"	=> $object_id];
					
					$item	= new EnergyUnit; 
					$item->{$item::$idField}	= $object_id;
					$item->DT_RowId				= $object_id;
					if (!array_key_exists($table, $fieldConfigs)) 
						$fieldConfigs[$table] = [	"fields"	=> [$field	=> []],
													"model"		=> $model,
												];
					$fieldConfigs[$table]["fields"][$field][$cell_id] = $item;
				} */
			}
			
			$field_tables = explode ( "@", $su );
			$label = "";
			$item	= null;
			foreach ( $field_tables as $field_table ) {
				if (strpos ( $field_table, "TAG:" ) !== FALSE) {
					if ($conn_id > 0) {
						$conn_objs ["$conn_id"] [] = "$cell_id~$field_table";
					}
				} else {
					if ($field_table) {
						$f = explode ( "/", $field_table );
						$table = $f [0];
						$field = $f [1];
						$label = CfgFieldProps::where ( [ 
								'TABLE_NAME' => $table,
								'COLUMN_NAME' => $field 
						] )->select ( 'LABEL' )->first ();
						if (count ( $label ) > 0) {
							$xlabel = $label->LABEL;
						} else {
							$xlabel = "$table/$field";
						}
						
						$model = strtolower ( $table );
						$model = str_replace ( ' ', '', ucwords ( str_replace ( '_', ' ', $model ) ) );
						$model = 'App\\Models\\' . $model;
						
						$condition = array ();
						$item		= null;
						if ($object_type == 'FLOW') {
							$value = "--";
							$condition ['FLOW_ID'] = $object_id;
							$item	= new Flow;
							$item->{$item::$idField}	= $object_id;
							$item->DT_RowId				= $object_id;
						} else {
							if ($object_type == 'TANK') {
								$value = "--";
								$condition ['TANK_ID'] = $object_id;
							} else {
								if ($object_type == 'STORAGE') {
									$value = "--";
									$condition ['STORAGE_ID'] = $object_id;
								} else {
									if ($object_type == 'EQUIPMENT') {
										$value = "--";
										$condition ['EQUIPMENT_ID'] = $object_id;
									} else {
										if ($object_type == 'ENERGY_UNIT') {
											$value = "--";
											$condition ['EU_ID'] = $object_id;
											$condition ['FLOW_PHASE'] = $flow_phase;
											$item	= new EnergyUnit;
											$item->{$item::$idField}	= $object_id;
											$item->DT_RowId				= $object_id;
										} else {
											$value = "--";
										}
									}
								}
							}
						}
						
// 						\DB::enableQueryLog ();
						$values = $model::where ( $condition )->whereDate ( 'OCCUR_DATE', '=', $occur_date)->SELECT ( [ 
								$field . ' AS FIELD_VALUE' 
						] )->first ();
// 						\Log::info ( \DB::getQueryLog () );
						
						if (count ( $values ) > 0) {
							$value = $values->FIELD_VALUE;
						}
						$rawValue	= $value;
						$value = (is_numeric ( $value ) ? number_format ( $value, 2 ) : $value);
						$cells_data ["$cell_id"] ["$xlabel"] 	= $value;
						$cellConfigs[$cell_id]["$table/$field"] = ["table"=>$table,"field"=>$field,"value"	=> $rawValue, "OBJECT_ID"	=> $object_id];
						
						if (!array_key_exists($table, $fieldConfigs))
							$fieldConfigs[$table] = [	"fields"	=> [$field	=> []],
														"model"		=> $model,
							];
						$fieldConfigs[$table]["fields"][$field][$cell_id] = $item;
						
					}
				}
			}
		}
		
		foreach ( $cells_data as $cell_id => $cell_data ) {
			$ret .= ($ret == "" ? "" : "#") . "$cell_id^";
			foreach ( $cell_data as $data_label => $data_value ) {
				if ($data_label == "%SF") {
					$sv = "";
					foreach ( $data_value as $flow_phase => $phase_value ) {
						$sv .= ($sv == "" ? "" : "%SV") . $flow_phase . "%SV" . $phase_value;
					}
					$ret .= "%SF^$sv" . "#" . "$cell_id^";
				} else
					$ret .= "$data_label: $data_value\n";
			}
		}
		
		$properties 	= $this->getExtendProperties($fieldConfigs,$occur_date,$cellConfigs);
		
// 		return response ()->json ( "ok$ret" );
		return response ()->json ([
									"data"			=> "ok$ret",
									"fieldConfigs"	=> 	$fieldConfigs,
									"cellConfigs"	=> 	$cellConfigs,
									'properties'	=>	$properties,
		] );
	}
	
	public function getExtendProperties($fieldConfigs,$occur_date,&$cellConfigs) {
		$properties 		= new Collection;
    	$rQueryList			= [];
    	$index				= 0;
		foreach ( $fieldConfigs as $table => $fields ) {
			$mdlName			= $fields["model"] ;
			foreach ( $fields["fields"] as $field => $fieldData) {
				$where	= [	'TABLE_NAME' 	=> $table,
							'COLUMN_NAME' 	=> $field,
				];
				$property	= CfgFieldProps::getOriginProperties($where,false)->first();
				if ($property&&$property instanceof CfgFieldProps) {
					foreach ( $fieldData as $cell_id => $item) {
						if ($item&&$property->shouldLoadLastValueOf($item)) {
							$column		= $property->data;
							$query		= $item->getLastValueOfColumn($mdlName,$column,$occur_date);
							if ($query) {
								if (!array_key_exists($column, $rQueryList)) $rQueryList[$column] = [];
								$rQueryList[$column][]	= $query;
							}
						}
						if (array_key_exists("$table/$field", $cellConfigs[$cell_id])) $cellConfigs[$cell_id]["$table/$field"]["index"] = $index;
					}
					$properties->push($property);
					$index++;
				}
			}
 			$fieldConfigs[$table] = $fields;
		}
		$this->updatePropertiesWithLastValue($properties,$rQueryList);
		return $properties;
	}
	
	public function uploadFile() {
		$files = Input::all ();
		$tmpFilePath = '/fileUpload/';
		$error = false;
		if (count ( $files ) > 0) {
			// foreach ($files as $file){
			$file = $files[1];
			$tmpFileName = $file->getClientOriginalName ();
			$v = explode ( '.', $tmpFileName );
			$tmpFileName = $v [0] . '_' . time () . '.' . $v [1];
			$file = $file->move ( public_path () . $tmpFilePath, $tmpFileName );
			if ($file) {
				$result = $tmpFilePath . $tmpFileName;
				$this->file = $result;
			} else {
				$error = true;
			}
			// }
			$data = ($error) ? [ 
					'error' => 'There was an error uploading your files' 
			] : [ 
					'files' => $result 
			];
		} else {
			$data = array (
					'success' => 'Form was submitted',
					'formData' => $_POST 
			);
		}
		return response ()->json ( $data );
	}
	
	public function uploadImg() {
		$files = Input::all ();
		$tmpFilePath = '/images/upload/';
		$error = false;
		if (count ( $files ) > 0) {
			// foreach ($files as $file){
			if(count ( $files ) == 1){
				$file = $files [0];
			}else{
				$file = $files [1];
			}
			$tmpFileName = $file->getClientOriginalName ();
			$v = explode ( '.', $tmpFileName );
			$tmpFileName = $v [0] . '_' . time () . '.' . $v [1];
			$file = $file->move ( public_path () . $tmpFilePath, $tmpFileName );
			if ($file) {
				$result = $tmpFilePath . $tmpFileName;
				$this->file = $result;
			} else {
				$error = true;
			}
			// }
			$data = ($error) ? [
					'error' => 'There was an error uploading your files'
			] : [
					'files' => $result
			];
		} else {
			$data = array (
					'success' => 'Form was submitted',
					'formData' => $_POST
			);
		}
		return response ()->json ( $data );
	}
	
	public function _indexTagsMapping() {
		return view ( 'front.tagsmapping' );
	}
	public function _indexWorkFlow() {
		$ebfunctions = EbFunctions::where('USE_FOR', 'like', '%TASK_GROUP%')
					->whereIn('LEVEL', [1,2])->get(['PARENT_CODE', 'CODE', 'NAME', 'PATH']);
		
		$result = array();
		foreach ($ebfunctions as $eb){
			$name = "";
			if(!is_null($eb->PARENT_CODE)){
				$name = "---";
			}
				
			$eb['FUNCTION_NAME'] = $name.$eb->NAME;
			$eb['FUNCTION_CODE'] = $eb->CODE;
			$eb['FUNCTION_URL'] = $eb->PATH;			
			array_push($result, $eb);
		}
		
		$users = array();
		$user = User::where(['ACTIVE'=>1])->get(['USERNAME', 'LAST_NAME', 'MIDDLE_NAME', 'FIRST_NAME']);		
		foreach ($user as $u) {
			$u['NAME'] = $u->LAST_NAME.' '.$u->MIDDLE_NAME.' '.$u->FIRST_NAME;
			
			array_push($users, $u);
		}
		
		$foGroup = FoGroup::all('ID', 'NAME');
		
		return view ( 'front.workflow',array ('ebfunctions'=>$result, 'user'=>$users, 'foGroup'=>$foGroup) );
	}
	public function getListWorkFlow() {
		$result = TmWorkflow::where ( [ 
				'STATUS' => 1 
		] )->get ( [ 
				'id',
				'name',
				'isrun' 
		] );
		
		return response ()->json ( [ 
				'result' => $result 
		] );
	}
	public function getXMLCodeWF(Request $request) {
		$param = $request->all ();
		
		$diagram_id = isset ( $param ['ID'] ) ? $param ['ID'] : 0;
		$readonly = isset ( $param ['readonly'] ) ? $param ['readonly'] : 0;
		
		$result = TmWorkflow::where ( [ 
				'ID' => $diagram_id 
		] )->select (  
				'DATA',
				'NAME',
				'INTRO',
				'ISRUN' 
		)->first();
		
		/* $result = array ();
		if (count ( $data ) > 0) {
			$result ['NAME'] = $data [0]->NAME;
			$result ['DATA'] = $data [0]->DATA;
			$result ['INTRO'] = $data [0]->INTRO;
			$result ['ISRUN'] = $data [0]->ISRUN;
		} */
		$xml = $result ['DATA'];
		if ($readonly) {
			$tmWorkflowTask = TmWorkflowTask::where ( [ 
					'WF_ID' => $diagram_id 
			] )->get ( [ 
					'ID',
					'TASK_CODE',
					'ISRUN',
					'RUNBY',
					'START_TIME',
					'FINISH_TIME',
					'LOG' 
			] );
			
			$flowTask = array ();
			foreach ( $tmWorkflowTask as $tm ) {
				if ($tm->LOG == "") {
					$tm ['LOG'] = 0;
				} else {
					$tm ['LOG'] = 1;
				}
				$xml = str_replace ( 'task_id="' . $tm->ID . '"', 'task_id="' . $tm->ID . '" has_log="' . $tm->LOG . '" start_time="' . $tm->START_TIME . '" finish_time="' . $tm->FINISH_TIME . '" task_code="' . $tm->TASK_CODE . '" isrun="' . $tm->ISRUN . '" autorun="' . ($tm->RUNBY == 1 ? 1 : 0) . '"', $xml );
				$result ['DATA'] = $xml;
			}
		}
		
		return response ()->json ( [ 
				'result' => $result 
		] );
	}
	public function workflowSave(Request $request) {
		$data = $request->all ();
		
		DB::beginTransaction ();
		try { 			
			$condition = array (
					'ID' => $data ['ID'] 
			);
			
			$objwf ['NAME'] = $data ['NAME'];
			$objwf ['INTRO'] = $data ['INTRO'];
			
			if($data ['ADD'] == 1){
				$objwf ['ISRUN'] = 'no';
			}
			$objwf ['DATA'] = $data ['KEY'];
			$objwf ['ID'] = $data ['ID'];
			$objwf ['STATUS'] = 1;
			
			//\DB::enableQueryLog ();
			$tmWorkflow = TmWorkflow::updateOrCreate ( $condition, $objwf );
			//\Log::info ( \DB::getQueryLog () );
			
			$dom_xml = simplexml_load_string ( $data ['KEY'] );
			$cells = $dom_xml->xpath ( '//mxCell[@vertex = 1]/parent::*' );
			$id_tasknew = array ();
			foreach ( $cells as $cell ) {
				$objwf_task = [];
				$task = $cell->attributes ();
				
				$objwf_task ['ID'] = ( int ) $task ['task_id'];
				
				$id_tasknew [] = $objwf_task ['ID'];
				
				$objwf_task ['WF_ID'] = $tmWorkflow->ID;
				if (isset ( $task ['isbegin'] )) {
					$objwf_task ['NAME'] = 'Begin';
					$objwf_task ['ISBEGIN'] = 1;
				}
				if (isset ( $task ['isend'] )) {
					$objwf_task ['NAME'] = 'End';
					$objwf_task ['ISBEGIN'] = - 1;
				}
				if (isset ( $task ['task_data'] ) && $task ['task_data']) {
					$param = json_decode ( $task ['task_data'] );
					$cell_style = $cell->children () [0]->attributes () ['style'];
					
					if (strpos ( $cell_style, 'style_plus' ) !== false)
						$param->task_code = 'NODE_COMBINE';
					else if (strpos ( $cell_style, 'rhombus' ) !== false)
							$param->task_code = 'NODE_CONDITION';
						
					$objwf_task ['NAME'] = addslashes ( $param->name );
					$objwf_task ['RUNBY'] = addslashes ( $param->runby );
					$objwf_task ['USER'] = addslashes ( $param->user );
					$objwf_task ['TASK_GROUP'] = addslashes ( $param->task_group );
					$objwf_task ['TASK_CODE'] = addslashes ( $param->task_code );
				}
				
				if (isset ( $task ['task_config'] )) 
				$objwf_task ['TASK_CONFIG'] = $task ['task_config'];
				$objwf_task ['NODE_CONFIG'] = '';
				
				if (isset ( $task ['next_task_config'] )) 
				$objwf_task ['NEXT_TASK_CONFIG'] = str_replace ( 'NaN,', '', $task ['next_task_config'] );
				
				if (isset ( $task ['prev_task_config'] )) 
				$objwf_task ['PREV_TASK_CONFIG'] = str_replace ( 'NaN,', '', $task ['prev_task_config'] );
				
				$conTask = array (
						'ID' => $objwf_task['ID'] 
				);
				
				//\DB::enableQueryLog ();
				TmWorkflowTask::updateOrCreate ( $conTask, $objwf_task );
				//\Log::info ( \DB::getQueryLog () );
			}
			
			$ids_task = $this->getIdTaskByWf($data ['ID']);	
			$m=count($ids_task);
			for($i=0;$i<$m;$i++){
				if(!in_array($ids_task[$i]['ID'],$id_tasknew)){
					//$objwf_task->Delete($ids_task[$i]);
					TmWorkflowTask::where(['ID'=>$ids_task[$i]['ID']])->delete();
				}
			}
			
 		} catch ( \Exception $e ) {
 			\Log::info ( $e->getMessage() );
			DB::rollback ();
		}
		 
		DB::commit ();
		
		return response ()->json ( $tmWorkflow->ID );
	}
	
	private  function getIdTaskByWf($wfid){		
		$ar = collect(TmWorkflowTask::where(['WF_ID'=>$wfid])->get(['ID']))->toArray();
		
		return $ar;
	}
	
	public function loadConfigTask(Request $request) {
		$data = $request->all ();
		
		$id=isset($data['taskid'])?$data['taskid']:0;
		
		if($id == -1){
			$id = $this->getkeyConfig();
		}
		
		$flowTask = TmWorkflowTask::where(['ID'=>$id])->first();
		
		return response ()->json ( ['flowTask'=>$flowTask] );
	}
	
	private function getkeyConfig(){
		$data = Params::where(['KEY'=>'WF_TASK_ID'])->select('NUMBER_VALUE')->first();
		$key = ($data->NUMBER_VALUE == "")?0:$data->NUMBER_VALUE + 1;
		
		Params::where(['KEY'=>'WF_TASK_ID'])->update(['NUMBER_VALUE'=>$key]);
		
		return $key;
	}
	
	public function getKey(){
		$key = $this->getkeyConfig();
		
		return response ()->json ($key);
	}
	
	public function changeRunTask(Request $request) {
		$data = $request->all ();
		
		$parent_code = $data['PARENT_CODE'];
		
		if($parent_code != 'workflow-fun'){
			$ebfunctions = EbFunctions::where('USE_FOR', 'like', '%TASK_FUNC%')
			->where(['PARENT_CODE'=>$parent_code])
			->get(['CODE AS FUNCTION_CODE', 'NAME AS FUNCTION_NAME', 'PATH AS FUNCTION_URL']);
		}else{
			$ebfunctions = TmWorkflow::all(['ID AS FUNCTION_CODE', 'NAME AS FUNCTION_NAME']);
		}
		
		return response ()->json ( array ('ebfunctions'=>$ebfunctions) );
	}
	
	public function loadFormSetting(Request $request) {
		$data = $request->all ();
		$result = array();
		$value = $data['value'];
		$task_id = isset($data['task_id'])?$data['task_id']:0;
		
		switch($value){
			case 'ALLOC_CHECK':
			case 'ALLOC_RUN':
				$network = Network::getTableName();
				$allocJob = AllocJob::getTableName();
					 
				$tm = [];
				$tm = DB::table($network.' AS a')
				->join($allocJob.' AS b', 'a.ID', '=', 'b.NETWORK_ID')
				->distinct ()
				->get(['a.ID', 'a.NAME']);
				
				$alloc_job = AllocJob::where(['NETWORK_ID'=>$tm[0]->ID])->get(['ID', 'NAME']);
				
				$result['network'] = $tm;
				$result['allocJob'] = $alloc_job;
				
				break;
			case 'VIS_REPORT':
				$result = Facility::all(['ID', 'NAME']);
				break;
			case 'FDC_EU':
				$models = ['Facility', 'EnergyUnitGroup', 'CodeReadingFrequency', 'CodeFlowPhase', 'CodeEventType', 'CodeAllocType', 'CodePlanType', 'CodeForecastType'];
				foreach ($models as $m){
					$tm = [];
					$model = 'App\\Models\\' .$m;
					$tm = $model::all(['ID', 'NAME']);
					$result[$m] = $tm;
				}				
				break;
			case 'FDC_FLOW':
				$models = ['Facility', 'CodeReadingFrequency', 'CodeFlowPhase'];
				foreach ($models as $m){
					$tm = [];
					$model = 'App\\Models\\' .$m;
					$tm = $model::all(['ID', 'NAME']);
					$result[$m] = $tm;
				}
				break;
			case 'FDC_EU_TEST':
				$models = ['Facility', 'EnergyUnit'];
				foreach ($models as $m){
					$tm = [];
					$model = 'App\\Models\\' .$m;
					$tm = $model::all(['ID', 'NAME']);
					$result[$m] = $tm;
				}
				break;
			case 'FDC_STORAGE':
				$models = ['Facility', 'CodeProductType',];
				foreach ($models as $m){
					$tm = [];
					$model = 'App\\Models\\' .$m;
					$tm = $model::all(['ID', 'NAME']);
					$result[$m] = $tm;
				}
				break;
			case 'INT_IMPORT_DATA':
				$tm = [];
				$tm = IntConnection::all(['ID', 'NAME']);
				
				$intTagSet = IntTagSet::where(['CONNECTION_ID'=>$tm[0]->ID])->get(['ID', 'NAME']);
				
				$result['IntConnection'] = $tm;
				$result['IntTagSet'] = $intTagSet;
				
				break;
			default:
				$result = [];
		}
		
		$task = TmWorkflowTask::where(['ID'=>$task_id])->get();
		$result['task'] = $task;
		$result['value'] = $value;
		
		return response ()->json ( array ('result'=>$result) );
	}
	
	public function getEntity(Request $request) {
		$data = $request->all ();		
		$value = $data['VALUE'];
		$key = $data['KEY'];
		$entity = $data['TABLE'];
		
		$model = 'App\\Models\\' .$entity;
		$result = $model::where([$key=>$value])->get(['ID','NAME']);
		
		return response ()->json ( array ('result'=>$result) );
	}
	
	public function workflowSaveTask(Request $request){
		$data = $request->all ();
		
		$type= isset($data['type'])?$data['type']:'';
		$tasks=isset($data['taskdata'])?$data['taskdata']:'';
		$wfid=$data['wfid'];
		$key=$data['key'];
		$objwf_task = [];
		if($tasks!=''){
			$tasks=json_decode($tasks);
			
			TmWorkflow::where(['ID'=>$wfid])->update(['DATA'=>$key]);
			
			$objwf_task['ID'] = 0;
			if(!empty($tasks->id)){
				$objwf_task['ID'] = (int)$tasks->id;
			}
			$objwf_task['WF_ID'] = $wfid;
			$objwf_task['NAME'] = addslashes($tasks->name);
			$objwf_task['RUNBY'] = addslashes($tasks->runby);
			$objwf_task['USER'] = addslashes($tasks->user);
			$objwf_task['TASK_GROUP'] = addslashes($tasks->task_group);
			$objwf_task['TASK_CODE'] = addslashes($tasks->task_code);
			
			if(isset($data['taskconfig'])){
				$objwf_task['TASK_CONFIG'] = $data['taskconfig'];
				
				$objwf_task['NEXT_TASK_CONFIG'] = addslashes(str_replace('NaN,','',$tasks->next_task_config));
				$objwf_task['PREV_TASK_CONFIG'] = addslashes(str_replace('NaN,','',$tasks->prev_task_config));
				
				$conTask = array (
						'ID' => $objwf_task['ID']
				);
					
				//\DB::enableQueryLog ();
				$tmp = TmWorkflowTask::updateOrCreate ( $conTask, $objwf_task);
				//\Log::info ( \DB::getQueryLog () );
				
			}
		}
		
		return response ()->json ( array ('result'=>$tmp) );
	}
	
	public function deleteWorkFlow(Request $request){
		$data = $request->all ();
		
		DB::beginTransaction ();
		try {		
			TmWorkflowTask::where(['WF_ID'=>$data['ID']])->delete();
			
			TmWorkflow::where(['ID'=>$data['ID']])->delete();		
		} catch ( \Exception $e ) {
			DB::rollback ();
		}
		
		DB::commit ();
		
		$result = $this->getTmWorkflow();
		
		return response ()->json ( [
				'result' => $result
		] );
	}
	
	public function stopWorkFlow(Request $request){
		$data = $request->all ();
				
		TmWorkflow::where(['ID'=>$data['ID']])->update(['ISRUN'=>'no']);
	
		$result = $this->getTmWorkflow();
	
		return response ()->json ( [
				'result' => $result
		] );
	}
	
	public function runWorkFlow(Request $request){
		$data = $request->all ();
	
		TmWorkflow::where(['ID'=>$data['ID']])->update(['ISRUN'=>'yes']);
		
		\DB::enableQueryLog ();
		$tmWorkflowTask = TmWorkflowTask::where(['WF_ID'=>$data['ID'], 'ISBEGIN'=>1])->first();		
		\Log::info ( \DB::getQueryLog () );
		
		if(count($tmWorkflowTask) > 0){				
			TmWorkflowTask::where(['WF_ID'=>$data['ID']])
			->where('ID', '<>', $tmWorkflowTask['id'])
			->update(['ISRUN'=>0]);
			
			$objRun = new WorkflowProcessController(null, $tmWorkflowTask);
			$objRun->runTask(null, $tmWorkflowTask);
		
			/* $job = (new runAllocation(null, $tmWorkflowTask));
			$this->dispatch($job); */
		}
		
		$result = $this->getTmWorkflow();
		return response ()->json ( [
				'result' => $result
		] );
	}
	
	private function getTmWorkflow(){
		$result = TmWorkflow::where ( ['STATUS' => 1] )
		->get ( ['id','name','isrun'] );
		
		return $result;
	}
	
	public function dashboard() {
		$filterGroups = array(	'productionFilterGroup'	=> [],
								'frequenceFilterGroup'	=> [],
								'dateFilterGroup'		=> array(	['id'=>'date_begin','name'=>'From date'],
																	['id'=>'date_end',	'name'=>'To date']),
								'enableButton'			=> true,
								'enableSaveButton'		=> false,
				
		);
		$current_username 	= auth()->user()->username;
		$cquery1 			= Dashboard::where("IS_DEFAULT",1)->where("USER_NAME",$current_username);
		$cquery2 			= Dashboard::where("USER_NAME",$current_username)->take(0,1);
		$cquery3 			= Dashboard::where("IS_DEFAULT",1)->where("TYPE",1)->take(0,1);
		$cquery4 			= Dashboard::where("TYPE",1)->take(0,1);
		$cquery0 			= Dashboard::where("IS_DEFAULT",1)->take(0,1);
		$query 				= $cquery0->union($cquery1)->union($cquery2)->union($cquery3)->union($cquery4);
		$dashboard_row 		= $query->first();

		return view ( 
				'front.dashboard',
				['filters'			=> $filterGroups,
				'dashboard_id'		=> $dashboard_row?$dashboard_row->ID:null,
				'dashboard_row'		=> $dashboard_row
		]);
	}
	
	public function dashboardConfig(Request $request) {
		$data 				= $request->all ();
		$dashboard_id		= array_key_exists("id", $data)?$data["id"]:0;
		$dashboard_row		= null;
		if ($dashboard_id>0) $dashboard_row = Dashboard::find($dashboard_id);
		return view (
					'config.dashboardconfig',	['dashboard_id'		=> $dashboard_row?$dashboard_row->ID:null,
												'dashboard_row'		=> $dashboard_row]
				);
	}
	
	public function editor() {
		return response()->file("/config/diagrameditor.xml");
	}
	
	public function taskman() {
		$filterGroups = array(
							  'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From Date'],['id'=>'date_end','name'=>'To Date']),
							'frequenceFilterGroup'		=> [
															["name"			=> "EbFunctions",
															"filterName"	=> "Run Task",
															"getMethod"		=> "loadBy",
															'dependences'	=> ["ExtensionEbFunctions"],
															"source"		=> [],
															'default'		=> ['ID'=>0,'NAME'=>'All']
															],
															["name"			=> "ExtensionEbFunctions",
															"getMethod"		=> "loadBy",
															"filterName"	=>	"Function Name",
															'default'		=> ['ID'=>0,'NAME'=>'All'],
															"source"		=> ['frequenceFilterGroup'=>["EbFunctions"]]],
							]
						);
		return view ( 'datavisualization.taskman',['filters'=>$filterGroups]);
	}
	
}