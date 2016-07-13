<?php
namespace App\Http\Controllers;
use App\Models\IntImportSetting;
use App\Models\IntImportLog;
use App\Models\IntTagMapping;
use App\Models\IntConnection;
use App\Models\IntTagSet;
use App\Models\IntTagTrans;

use Excel;
use Illuminate\Http\Request;
use Input;
use Carbon\Carbon;
use DB;
use Schema;

class InterfaceController extends Controller {
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	
	public function _index() {
		$int_import_setting = $this->loadImportSetting();
		return view ( 'front.importdata', ['int_import_setting'=>$int_import_setting]);
	}
	
	public function _indexDataloader() {
		$int_import_setting = $this->loadImportSetting();
		return view ( 'front.dataloader', ['int_import_setting'=>$int_import_setting]);
	}
	
	public function getImportSetting(Request $request) {
		$data = $request->all ();
		
		$int_import_setting = IntImportSetting::where(['ID'=>$data['id']])->select ('*')->first();
		
		return response ()->json ( $int_import_setting );
	}
	
	private function loadImportSetting(){
		$int_import_setting = IntImportSetting::all('ID', 'NAME');
		return  $int_import_setting;
	}
	
	public function _indexConfig() {
		$int_import_setting = IntImportSetting::all('ID', 'NAME');
		return view ( 'front.sourceconfig', ['int_import_setting'=>$int_import_setting]);
	}
	
	public function detailsConnection(Request $request) {
		$data = $request->all ();
	
		$dt = IntConnection::where(['ID'=>$data['id']])->select('*')->get();
		
		$int_tag_set = $this->getIntTagSet($data['id']);
		
		return response ()->json ( ['dt'=>$dt, 'int_tag_set'=>$int_tag_set] );
	}
	
	public function saveConn(Request $request) {
		$data = $request->all ();
	
		$condition = array (
				'ID' => $data ['id']
		);
		
		if (isset ( $data ['name'] ))
			$obj ['NAME'] = $data ['name'];
		
		if (isset ( $data ['server'] ))
			$obj ['SERVER'] = $data ['server'];
		
		if (isset ( $data ['username'] ))
			$obj ['USER_NAME'] = $data ['username'];
		
		if (isset ( $data ['password'] ))
			$obj ['PASSWORD'] = $data ['password'];
		
		if (isset ( $data ['id']) && $data ['id'] <= 0)
			$obj ['TYPE'] = $data ['type'];
		
		$result = IntConnection::updateOrCreate ( $condition, $obj );
		$id = $result->ID;
		
		return response ()->json (['id'=>$id, 'conn'=>$this->loadCon($data ['type'])]);
	}
	
	public function loadTagSets(Request $request) {
		$data = $request->all ();
		
		return response ()->json ($this->getIntTagSet($data['connection_id']));
	}
	
	public function renameTagSet(Request $request) {
		$data = $request->all ();
			
		IntTagSet::where(['ID'=>$data['id']])->update(['NAME'=>$data['name']]);
		return response ()->json ($data['id']);
	}
	
	public function deleteTagSet(Request $request) {
		$data = $request->all ();
			
		IntTagSet::where(['ID'=>$data['id']])->delete();
		return response ()->json ($data['id']);
	}
	
	private function getIntTagSet($conn_id) {
		$int_tag_set = IntTagSet::where(['CONNECTION_ID'=>$conn_id])->get(['ID', 'NAME']);
		return $int_tag_set;
	}
	
	public function renameConn(Request $request) {
		$data = $request->all ();
	
		IntConnection::where(['ID'=>$data['id']])->update(['NAME'=>$data['name']]);
		return response ()->json (['id'=>$data['id'], 'conn'=>$this->loadCon($data ['type'])]);
	}
	
	public function deleteConn(Request $request) {
		$data = $request->all ();
	
		IntConnection::where(['ID'=>$data['id']])->delete();
		return response ()->json (['conn'=>$this->loadCon($data ['type'])]);
	}
	
	public function loadIntServers(Request $request) {
		$data = $request->all ();
		
		$intconnection = $this->loadCon($data['type']);
		return response ()->json ( $intconnection );
	}
	
	public function loadTagSet(Request $request) {
		$data = $request->all ();
		
		$int_tag_set = IntTagSet::where(['ID'=>$data['set_id']])->select('TAGS')->first();
		
		return response ()->json ( $int_tag_set['TAGS'] );
	}
	
	public function saveTagSet(Request $request) {
		$data = $request->all ();
		
		$condition = array (
				'ID' => $data ['id']
		);
		
		if (isset ( $data ['name'] ))
			$obj ['NAME'] = addslashes($data ['name']);
		
		if (isset ( $data ['tags'] ))
			$obj ['TAGS'] = addslashes($data ['tags']);
		
		if ($data ['id'] <= 0)
			$obj ['CONNECTION_ID'] = $data ['conn_id'];
		
		$result = IntTagSet::updateOrCreate ( $condition, $obj );
		$id = $result->ID;
		
		return response ()->json ("ok:".$id);
	}
	
	public function loadCon($type) {
		$intconnection = IntConnection::where(['TYPE'=>$type])->get(['ID', 'NAME']);
		return  $intconnection ;
	}
	
	public function saveImportSetting(Request $request) {
		$data = $request->all ();
		
		$condition = array (
				'ID' => $data ['id']
		);
		
		if(isset($data ['name']))
			$obj ['NAME'] = $data ['name'];
		
		if(isset($data ['tab']))
			$obj ['TAB'] = $data ['tab'];
		
		if(isset($data ['col_tag']))
			$obj ['COL_TAG'] = $data ['col_tag'];
		
		if(isset($data ['col_time']))
			$obj ['COL_TIME'] = $data ['col_time'];
		
		if(isset($data ['col_value']))
			$obj ['COL_VALUE'] = $data ['col_value'];
		
		if(isset($data ['row_start']))
			$obj ['ROW_START'] = $data ['row_start'];
		
		if(isset($data ['row_finish']))
			$obj ['ROW_FINISH'] = $data ['row_finish'];
		
		if(isset($data ['cols_mapping']))
			$obj ['COLS_MAPPING'] = $data ['cols_mapping'];
		
		if(isset($data ['cols_mapping']))
			$obj ['COLS_MAPPING'] = $data ['cols_mapping'];
		
		$result = IntImportSetting::updateOrCreate ( $condition, $obj );
		$id = $result->ID;
		
		$int_import_setting = $this->loadImportSetting();
			
		return response ()->json (['int_import_setting'=>$int_import_setting, 'id'=>$id]);
	}
	
	public function deleteSetting(Request $request) {
		$data = $request->all ();
	
		IntImportSetting::where(['ID'=>$data['id']])->delete();
		
		$int_import_setting = $this->loadImportSetting();
	
		return response ()->json ( $int_import_setting );
	}
	
	public function renameSetting(Request $request) {
		$data = $request->all ();
	
		IntImportSetting::where(['ID'=>$data['id']])->update(['NAME'=>$data['name']]);
	
		$int_import_setting = $this->loadImportSetting();
	
		return response ()->json (['int_import_setting'=>$int_import_setting, 'id'=>$data['id']]);
	}
	
	public function doImport() {		
		$files = Input::all ();
		
		$tabIndex = $files['tabIndex'];
		$tagColumn = $files['tagColumn'];
		$timeColumn = $files['timeColumn'];
		$valueColumn = $files['valueColumn'];
		$rowStart = $files['rowStart'];
		$rowFinish = $files['rowFinish'];
		$cal_method = $files['cal_method'];
		$date_begin = $files['date_begin'];
		$date_end = $files['date_end'];
		$update_db = $files['update_db'];
		$path = "";
		$tmpFilePath = '/fileUpload/';
		$error = false;
		$str = "";
		
		if (count ( $files ) > 0) {
			$file = $files['file'];
			$tmpFileName = $file->getClientOriginalName ();
			$fileName = $tmpFileName;
			$v = explode ( '.', $tmpFileName );
			$tmpFileName = $v [0] . '_' . time () . '.' . $v [1];			
			$data = [];
			$file = $file->move ( public_path () . $tmpFilePath, $tmpFileName );
			if ($file) {				
				$path =  public_path () .$tmpFilePath . $tmpFileName;							
				$xxx = Excel::load($path, function($reader) 
						use ($data, $tagColumn, $timeColumn, $valueColumn, $tabIndex, $rowStart, $rowFinish, 
							$date_begin, $date_end, $fileName, $update_db, $cal_method, $str, $path) {
					$objExcel = $reader->getExcel(); 
					$sheet = $objExcel->getSheet(0);
					$highestRow = $sheet->getHighestRow();
					$highestColumn = $sheet->getHighestColumn();
					if($rowFinish > $highestRow) $rowFinish = $highestRow;
					
					$current_username = '';
					if((auth()->user() != null)){
						$current_username = auth()->user()->username;
					}
						
					$condition = array(
							'ID'=>-1
					);
					$begin_time = date('Y-m-d H:i:s');
					$obj['FILE_NAME'] = $fileName;
					$obj['FILE_SIZE'] = $highestRow;
					$obj['BEGIN_TIME'] = $begin_time;
					$obj['USER_NAME'] = $current_username;
					
					$int_import_log = IntImportLog::updateOrCreate($condition,$obj);
					$log_id=$int_import_log->ID;
					
					$tags_read=0;
					$tags_override=0;
					$tags_loaded=0;
					$tags_rejected=0;
					$tags_addnew = 0;
					
					$html = "";
					$datatype = "";
					if(!$datatype) $datatype="NUMBER";
					$db_schema = ENV('DB_DATABASE');
					for ($row = $rowStart; $row <= $rowFinish; $row++)
					{
						$arr = [];
						$tags_read++;
						$hasError=false;
						$err="";
						$statusCode="Y";
						try{
							$time = $sheet->rangeToArray($timeColumn.$row)[0][0];
							$date = $this->proDate($time);
							if(strtotime($date) >= strtotime($date_begin) && strtotime($date) <= strtotime($date_end)){
								$tagID = $sheet->rangeToArray($tagColumn.$row)[0][0];
								//$arr['tag'] = $tag[0][0];						
								$value = $sheet->rangeToArray($valueColumn.$row)[0][0];
								//$arr['value'] = $value;
								//$arr['time'] = $date;
								//array_push($data, $arr);
								
								if(!$tagID && ($value!=="" || $date!==""))
								{
									$hasError=true;
									$statusCode="NT";
									$err="No tag";
								}
								
								if($datatype=="NUMBER")
								{
									if(!is_numeric($value))
									{
										$hasError=true;
										$statusCode="NF";
										$err="Not a number: $value";
									}
								}
							//}
							
							if(!$hasError)
							{
								if(!$tagID)
								{
									$hasError=true;
									$statusCode="NTG";
									$err="No tag ID";
								}
							
								if(!$date)
								{
									$hasError=true;
									$statusCode="ND";
									$err="No date";
								}
								else
								{
									$time = strtotime($date);
									$Y = date('Y',$time);
									if($Y=="1970")
									{
										$hasError=true;
										$statusCode="NWD";
										$err="Wrong date";
									}
								}
							}
							
							$impSQL="";
							if(!$hasError)
							{
								$r_t = IntTagMapping::where(['TAG_ID'=>$tagID])->select('*')->get();
								if(count($r_t)<=0)
								{
									$hasError=true;
									$statusCode="NG";
									$err="Tag mapping not found";
								}
								else
								{
									foreach ($r_t as $r)
									{
										$table_name=strtoupper($r->TABLE_NAME);
										$column_name=strtoupper($r->COLUMN_NAME);
							
										$cc = DB::select("SELECT TABLE_NAME FROM `INFORMATION_SCHEMA`.`TABLES` WHERE TABLE_SCHEMA='$db_schema' and `TABLE_NAME`='$table_name'");
										if(!$cc)
										{
											$hasError=true;
											$statusCode="NT";
											$err="Table not found ($table_name)";
										}
										else
										{
											$cc = DB::select("SELECT COLUMN_NAME FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE TABLE_SCHEMA='$db_schema' and `TABLE_NAME`='$table_name' and COLUMN_NAME='$column_name'");
											if(!$cc)
											{
												$hasError=true;
												$statusCode="NC";
												$err="Column not found ($column_name)";
											}
										}
							
										if(!$hasError)
										{
											$objIDField=$this->getObjectIDFiledName($table_name);
											$sF="";
											$sV="";
											$sWhere="$objIDField=$r[OBJECT_ID] and OCCUR_DATE=DATE('$date')";
											if(substr($table_name,0,12)=="ENERGY_UNIT_")
											{
												$sWhere.=" and FLOW_PHASE=$r[FLOW_PHASE] and EVENT_TYPE=$r[EVENT_TYPE]";
												$sF.=",FLOW_PHASE";
												$sV.=",$r[FLOW_PHASE]";
												$sF.=",EVENT_TYPE";
												$sV.=",$r[EVENT_TYPE]";
											}
											if($table_name=="ENERGY_UNIT_DATA_ALLOC")
											{
												$sWhere.=" and ALLOC_TYPE=$r[ALLOC_TYPE]";
												$sF.=",ALLOC_TYPE";
												$sV.=",$r[ALLOC_TYPE]";
											}
											
											$tmp = DB::select("select ID from `$table_name` where $sWhere");
											if(count($tmp) > 0)
											{
												$rID = $tmp[0]->ID;
												$sSQL="update `$table_name` set `$column_name`='$value' where ID=$rID";
												$sSQL=str_replace("''","null",$sSQL);
												$impSQL.=($impSQL?"<bt>":"").$sSQL;
												if($update_db)
												{
													DB::update($sSQL) or $html.="<td>".mysql_error()."</td>";
													$tags_override++;
												}
											}
											else
											{
												$sSQL="insert into `$table_name`(`$objIDField`,OCCUR_DATE,`$column_name`$sF) values($r->OBJECT_ID,'$date','$value'$sV)";
												$sSQL=str_replace("''","null",$sSQL);
												$impSQL.=($impSQL?"<bt>":"").$sSQL;
												if($update_db)
												{
													DB::insert($sSQL) or $html.="<td>".mysql_error()."</td>";
													$tags_addnew++;
												}
											}
											$tags_loaded++;
										}
									}
								}
							}
							if($hasError)
							{
								$tags_rejected ++;
							}
							if($tagID!=="" && $value!=="" && $date!=="")
							{								
								$load_time=date('Y-m-d H:i:s');
								IntTagTrans::insert([
									'LOG_ID'=> $log_id,
									'TAG_ID'=>$tagID,
									'VALUE'=>$value,
									'DATE'=>$date,
									'DATA_TYPE'=>$datatype,
									'LOAD_TIME'=>$load_time,
									'STATUS_CODE'=>$err
								]);
							}
							
							$html.="<tr><td>$tagID</td><td>$value</td><td>$date</td><td>$statusCode</td><td>$err</td><td>$impSQL</td></tr>";
						
						}else{
							$hasError=true;
							$statusCode="DOR";
							$err="Date/time out of range";
						}
						
						}catch(Exception $e) {
							$hasError=true;
							$statusCode=$e->getMessage();
						}
					}
					
					$end_time=date('Y-m-d H:i:s');					
					IntImportLog::where(['ID'=>$log_id])
					->update([
						'END_TIME'=>$end_time, 
						'TAGS_READ'=>$tags_read, 
						'TAGS_LOADED'=>$tags_loaded, 
						'TAGS_REJECTED'=>$tags_rejected, 
						'TAGS_OVERRIDE'=>$tags_override
					]);
					
					$str .= "<h3>Import log</h3>";
					$str .= "<input type='button' style='display:none' value='Back' onclick=\"document.location.href='/doimport';\" />";
					$str .= "<table>";
					$str .= "<tr><td>Filename</td><td>: <b>".$fileName."</b></td><td> Filesize</td><td>: <b> " . $highestRow . "</b></td></tr>";
					$str .= "<tr><td>From date</td><td>: " . $date_begin . "</td></tr>";
					$str .= "<tr><td>To date</td><td>: " . $date_end . "</td></tr>";
					$str .= "<tr><td>Tab</td><td>: " . $tabIndex . "</td></tr>";
					$str .= "<tr><td>Tag Column</td><td>: " . $tagColumn . "</td></tr>";
					$str .= "<tr><td>Time column</td><td>: " . $timeColumn . "</td></tr>";
					$str .= "<tr><td>Value column</td><td>: " . $valueColumn . "</td></tr>";
					$str .= "<tr><td>Row start</td><td>: " . $rowStart . "</td></tr>";
					$str .= "<tr><td>Row finish</td><td>: " . $rowFinish . "</td></tr>";
					$str .= "<tr><td>Update database</td><td>: <b>" . ($update_db?"Yes":"No") . "</b></td></tr>";
					$str .= "<tr><td>Data method</td><td>: " . $cal_method . "</td></tr>";
					$str .= "<tr><td></td></tr>";
					$str .= "<tr><td>Tags read</td><td>: " . $tags_read . "</td></tr>";
					$str .= "<tr><td>Tags loaded</td><td>: " . $tags_loaded . "</td></tr>";
					$str .= "<tr><td>Tags rejected</td><td>: " . $tags_rejected . "</td></tr>";
					$str .= "<tr><td>Tags override</td><td>: " . $tags_override . "</td></tr>";
					$str .= "<tr><td>Tags added</td><td>: " . $tags_addnew . "</td></tr>";
					$str .= "<tr><td>Begin time</td><td>: " . $begin_time . "</td></tr>";
					$str .= "<tr><td>End time</td><td>: " . $end_time . "</td></tr>";
					$str .= "</table>";
					$str .= "<br>";
					$str .= "<table><tr>";
					$str .= "<td><b>Tag</b></td><td><b>Value</b></td><td><b>Date/time</b></td>";
					$str .= "<td><b>Code</b></td><td><b>Status</b></td><td><b>Command</b></td>";
					$str .= "</tr> " . $html . "	</table>";
					
					$reader->select(['str'=>$str])->first();	
				} );
			} 
		}
		
		if (file_exists($path)) { unlink ($path); }
		return response ()->json ($xxx);
	}
	
	private function getObjectIDFiledName($table)
	{
		if(substr($table,0,5)=="EQUIP")
			return "EQUIPMENT_ID";
		if(substr($table,0,5)=="FLOW_")
			return "FLOW_ID";
		if(substr($table,0,12)=="ENERGY_UNIT_")
			return "EU_ID";
		if(substr($table,0,8)=="STORAGE_")
			return "STORAGE_ID";
		if(substr($table,0,5)=="TANK_")
			return "TANK_ID";
		if(substr($table,0,3)=="EU_")
			return "EU_ID";
	} 
	
	private function proDate($date){
		$m; $d; $y;
		$ds=explode('-',$date);
		if(count($ds) == 1){
			$ds=explode('/',$date);
		}
		
		$m = $ds [0]; 
		$d = $ds [1]; 
		$y = $ds [2];
		
		if (strlen ( $m ) == 1)
			$m = "0" . $m;
		if (strlen ( $d ) == 1)
			$d = "0" . $d;
		if (strlen ( $y ) == 2)
			$y = "20" . $y;
		
		if (strlen ( $m ) == 2 && strlen ( $d ) == 2 && strlen ( $y ) == 4) {
			$date = $m . "/" . $d . "/" . $y;
		}
		$date = Carbon::createFromFormat('m/d/Y', $date)->format('m/d/Y');
		return $date;
	}
	
	public function pi(Request $request) {
		$data = $request->all ();
		
		$connection_id = $data['connection_id'];
		$tagset_id = $data['tagset_id'];
		$cal_method = $data['cal_method'];
		$date_begin = $data['date_begin'];
		$date_end = $data['date_end'];
		$update_db = $data['update_db'];
		
		$int_connection = IntConnection::where(['ID'=>$connection_id])->select('SERVER', 'USER_NAME', 'PASSWORD')->first();		
		$server = $int_connection->SERVER;
		$username = $int_connection->USER_NAME;
		$password = $int_connection->PASSWORD;
		
		$intTagSet = IntTagSet::where(['ID'=>$tagset_id])->select('TAGS')->first();
		$ptags = $intTagSet->TAGS;
		
		$str = "";
		
		$date_begin = Carbon::createFromFormat('m/d/Y  H:i', $date_begin)->format('Y-m-d H:i:s');
		$date_end = Carbon::createFromFormat('m/d/Y  H:i', $date_end )->format ( 'Y-m-d H:i:s' );
		
		if ($update_db && $cal_method == "all") {
			return response ()->json ( "<font color='red'>Not allow inport data with method '<b>All</b>'</font>" );
		}
		
		$tagcondition = "";
		$tags = explode ( "\n", $ptags );
		foreach ( $tags as $tag )
			if ($tag)
				$tagcondition .= ($tagcondition ? " or " : "") . "tag='$tag'";
		
		if ($cal_method == "max" || $cal_method == "min")
			$sql = "SELECT tt.tag,tt.TIME,tt.value
					FROM [piarchive].[picomp] tt
					inner join
					(
					SELECT tag tagx,$cal_method(value) mvalue
					FROM [piarchive].[picomp]
					WHERE ($tagcondition)
					AND time BETWEEN '$date_begin' AND '$date_end' group by tag
					) grouped on tt.tag=grouped.tagx and tt.value=grouped.mvalue
					WHERE ($tagcondition)
					AND value is not null
					AND time BETWEEN '$date_begin' AND '$date_end'";
		else if ($cal_method == "first" || $cal_method == "last") {
			$func = ($cal_method == "first" ? "min" : "max");
			$sql = "SELECT tt.tag,tt.TIME,tt.value
						FROM [piarchive].[picomp] tt
						inner join
						(
						SELECT tag tagx,$func(time) mtime
						FROM [piarchive].[picomp]
						WHERE ($tagcondition)
						AND time BETWEEN '$date_begin' AND '$date_end' group by tag
						) grouped on tt.tag=grouped.tagx and tt.time=grouped.mtime
						WHERE ($tagcondition)
						AND value is not null
						AND time BETWEEN '$date_begin' AND '$date_end'";
		} else if ($cal_method == "average") {
			$sql = "SELECT tag, max(TIME) TIME, avg(value) value
						FROM picomp
						WHERE ($tagcondition) AND value is not null AND time BETWEEN '$date_begin' AND '$date_end'
						group by tag";
					}
					else
						$sql="SELECT tag, TIME, value
						FROM picomp
						WHERE ($tagcondition) AND value is not null AND time BETWEEN '$date_begin' AND '$date_end'";
		
		$update_db = 'No';
		if($update_db)
			$supdate_db = 'Yes';
		
		$str .= " <b>Import PI data</b><br>";
		$str .= " Server: <b>".$server."</b><br>";
		$str .= " Data method: <b>".$cal_method."</b><br>";
		$str .= " Update database: <b>".$supdate_db."</b><br>";
		$str .= " From time: <b>".$data['date_begin']."</b><br>";
		$str .= " To time: <b>".$data['date_end']."</b><br><br>";
		
		$connection = new \COM("ADODB.Connection") or die("Cannot start ADO");
		$str .= " Open connection ".date('H:i:s')."<br>";
		
		$connection->Open("Initial Catalog=piarchive;
				Data Source='localhost';User ID =root;Password='';");
		$str .= " Begin command ".date('H:i:s')."<br>";
		
		$result_set = $connection->Execute($sql);
		$result_count = 0;
		$labels = array();
		
		$str .= " Begin fetch data ".date('H:i:s')."<br><br>";
		$str .= " <table><tr><td><b>Tag</b></td><td><b>Date/time</b></td><td><b>Value</b></td><td><b>Code</b></td><td><b>Status</b></td><td><b>Command</b></td></tr>";
		while (!$result_set->EOF)
		{
			$impSQL="";
			$hasError=false;
			$statusCode="Y";
			$err="";
			$tagID=$result_set->fields[0]->value;
			$date=$result_set->fields[1]->value;
			$value=$result_set->fields[2]->value;
			$r_t = int_tag_mapping::where(['TAG_ID'=>$tagID])->select('*')->first();
			if($update_db)
			{
				if(count($r_t)<=0)
				{
					$hasError=true;
					$statusCode="NG";
					$err="Tag mapping not found";
				}
				else
				{
					foreach ($r_t as $r)
					{
						$table_name=strtoupper($r[TABLE_NAME]);
						$column_name=strtoupper($r[COLUMN_NAME]);
		
						$cc = DB::statement ("SELECT TABLE_NAME FROM `INFORMATION_SCHEMA`.`TABLES` WHERE TABLE_SCHEMA='$db_schema' and `TABLE_NAME`='$table_name' limit 1");
						if(!$cc)
						{
							$hasError=true;
							$statusCode="NT";
							$err="Table not found ($table_name)";
						}
						else
						{
							$cc = DB::statement ("SELECT COLUMN_NAME FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE TABLE_SCHEMA='$db_schema' and `TABLE_NAME`='$table_name' and COLUMN_NAME='$column_name' limit 1");
							if(!$cc)
							{
								$hasError=true;
								$statusCode="NC";
								$err="Column not found ($column_name)";
							}
						}
		
						if(!$hasError)
						{
							$objIDField = $this->getObjectIDFiledName($table_name);
							$sF="";
							$sV="";
							$sWhere="$objIDField=$r[OBJECT_ID] and OCCUR_DATE=DATE('$date')";
							if(substr($table_name,0,12)=="ENERGY_UNIT_")
							{
								$sWhere.=" and FLOW_PHASE=$r[FLOW_PHASE]";
								$sF.=",FLOW_PHASE";
								$sV.=",$r[FLOW_PHASE]";
							}
							if($table_name=="ENERGY_UNIT_DATA_ALLOC")
							{
								$sWhere.=" and ALLOC_TYPE=$r[ALLOC_TYPE]";
								$sF.=",ALLOC_TYPE";
								$sV.=",$r[ALLOC_TYPE]";
							}
							$tmp = DB::statement ("select ID from `$table_name` where $sWhere limit 1");
							if($tmp)
							{
								$sSQL="update `$table_name` set `$column_name`='$value' where ID=$rID->ID";
								$sSQL=str_replace("''","null",$sSQL);
								$impSQL.=($impSQL?"<bt>":"").$sSQL;
								if($update_db)
								{
									DB::update($sSQL) or $html.="<td>".mysql_error()."</td>";
									$tags_override++;
								}
							}
							else
							{
								$sSQL="insert into `$table_name`(`$objIDField`,OCCUR_DATE,`$column_name`$sF) values($r[OBJECT_ID],'$date','$value'$sV)";
								$sSQL=str_replace("''","null",$sSQL);
								$impSQL.=($impSQL?"<bt>":"").$sSQL;
								if($update_db)
								{
									DB::insert($sSQL) or $html.="<td>".mysql_error()."</td>";
									$tags_addnew++;
								}
							}
							$tags_loaded++;
						}
					}
				}
			}
			$str .= " <tr><td>".$result_set->fields[0]->value.'</td><td>'.$result_set->fields[1]->value.'</td><td>'.$result_set->fields[2]->value."</td><td>$statusCode</td><td>$err</td><td>$impSQL</td></tr>";
			$result_count = $result_count +1;
			$result_set->MoveNext();
		}
		$str .= " </table><br>";
		$str .= " Close connection ".date('H:i:s')."<br>";
		
		$str .= " <br />The number of records retrieved is: ".$result_count."<br /><br />";
		
		$result_set->Close(); // optional
		$connection->Close(); // optional
		$str .= " Finished ".date('H:i:s')."<br>";
		
		return response ()->json ($str);
	}
	
	public function getTableFieldsAll(Request $request) {
		$data = $request->all ();
		$field_num = Schema::getColumnListing($data['table']);
		return response ()->json ($field_num);
	}

	public function doImportDataLoader(Request $request) {
		$data = $request->all ();
		
		$tab = $data ['tabIndex'];
		$tagColumn = $data ['tagColumn'];
		$timeColumn = $data ['timeColumn'];
		$valueColumn = $data ['valueColumn'];
		$rowStart = $data ['rowStart'];
		$rowFinish = $data ['rowFinish'];
		$cal_method = $data ['cal_method'];
		$date_begin = $data ['date_begin'];
		$date_end = $data ['date_end'];
		$update_db = $data ['update_db'];
		$override_data = $data ['cboOveride'];
		$table_name = addslashes ( $data ['txtTable'] );
		$mapping = addslashes ( $data ['txtMapping'] );
		
		$date_begin = date ( 'Y-m-d', strtotime ( $date_begin ) );
		$date_end = date ( 'Y-m-d', strtotime ( $date_end ) );
		
		$path = "";
		$tmpFilePath = '/fileUpload/';
		
		if (! ($rowStart > 0 && $rowStart <= $rowFinish))
			return response ()->json ( "Wrong rows range" );
		
		$file = $data ['file'];
		$tmpFileName = $file->getClientOriginalName ();
		$fileName = $tmpFileName;
		$v = explode ( '.', $tmpFileName );
		$tmpFileName = $v [0] . '_' . time () . '.' . $v [1];
		$data = [ ];
		$file = $file->move ( public_path () . $tmpFilePath, $tmpFileName );
		if ($file) {
			$path = public_path () . $tmpFilePath . $tmpFileName;
			$xxx = Excel::load ( $path, function ($reader) use ($data, $tagColumn, $timeColumn, $valueColumn, $tab, $rowStart, $rowFinish, $date_begin, $date_end, $fileName, $update_db, $cal_method, $path, $mapping, $table_name, $override_data) {
				$objExcel = $reader->getExcel ();
				$sheet = $objExcel->getSheet ( 0 );
				$highestRow = $sheet->getHighestRow ();
				$highestColumn = $sheet->getHighestColumn ();
				
				$current_username = '';
				if ((auth ()->user () != null)) {
					$current_username = auth ()->user ()->username;
				}
				
				$condition = array (
						'ID' => - 1 
				);
				$begin_time = date ( 'Y-m-d H:i:s' );
				$obj ['FILE_NAME'] = $fileName;
				$obj ['FILE_SIZE'] = $highestRow;
				$obj ['BEGIN_TIME'] = $begin_time;
				$obj ['USER_NAME'] = $current_username;
				
				$int_import_log = IntImportLog::updateOrCreate ( $condition, $obj );
				$log_id = $int_import_log->ID;
				
				$tags_rejected = 0;
				$tags_loaded = 0;
				$tags_read = 0;
				$tags_override = 0;
				$tags_addnew = 0;
				$str = "";
				$html = "";
				$datatype = "";
				if (! $datatype)
					$datatype = "NUMBER";
				
				$maps = explode ( PHP_EOL, $mapping );
				$sql = "";
				$keys_check = "";
				$F = "";
				$V = "";
				$X = "";
				$vars = array ();
				
				foreach ( $maps as $map ) {
					$str .= "map:$map<br>";
					$exps = explode ( '=', $map );
					if (count ( $exps ) == 2) {
						$field = trim ( $exps [0] );
						$exp = trim ( $exps [1] );
						$dateformat = "";
						$iskey = false;
						if (strpos ( $exp, '*' ) !== false) {
							$iskey = true;
							$exp = str_replace ( '*', '', $exp );
						}
						$k = strpos ( $exp, '{' );
						if ($k > 0) {
							$l = strpos ( $exp, '}', $k );
							if ($l > $k)
								$dateformat = substr ( $exp, $k + 1, $l - $k - 1 );
							$exp = substr ( $exp, 0, $k );
						}
						$value = "'$exp'";
						if (strlen ( $exp ) == 1 && ord ( strtolower ( $exp ) ) >= 97 && ord ( strtolower ( $exp ) ) <= 122) {
							$key = ord ( strtolower ( $exp ) ) - 96;
							$vars [$key] = $exp;
							$value = "'@VALUE_$key'";
						}
						if ($dateformat)
							$value = "STR_TO_DATE($value,'$dateformat')";
						if ($iskey) {
							$keys_check .= ($keys_check ? " and " : "") . "`$field`=$value";
						}
						$F .= ($F ? "," : "") . "`$field`";
						$V .= ($V ? "," : "") . $value;
						$X .= ($X ? "," : "") . "`$field`=$value";
					}
				}
				
				if ($F) {
					{
						if ($rowFinish > $highestRow)
							$rowFinish = $highestRow;
						
						for($row = $rowStart; $row <= $rowFinish; $row ++) {
							$html .= "<tr>";
							$tags_read ++;
							
							$keys_check_x = $keys_check;
							$V_x = $V;
							$X_x = $X;
							foreach ( $vars as $var => $vvv ) {
								$value = $sheet->rangeToArray ( $vvv . $row ) [0] [0];
								// $value=mysql_real_escape_string($sheet->getCell($vvv.$row)->getFormattedValue());//$data->sheets[$i][cells][$j][$var];
								if ($keys_check_x)
									$keys_check_x = str_replace ( "@VALUE_$var", $value, $keys_check_x );
								$V_x = str_replace ( "@VALUE_$var", $value, $V_x );
								$X_x = str_replace ( "@VALUE_$var", $value, $X_x );
							}
							$sSQL = "";
							$isInsert = true;
							if ($keys_check_x) {
								$tmp = DB::select("select ID from `$table_name` where $keys_check_x");
								if(count($tmp))
								{
									$id = $tmp[0]->ID;
									$sSQL = "update `$table_name` set $X_x where ID=$id";
									$isInsert = false;
								}
							}
							if (! $sSQL) {
								$sSQL = "insert into `$table_name`($F) values($V_x)";
							}
							if ($isInsert)
								$tags_addnew ++;
							else
								$tags_override ++;
							
							$sSQL = str_replace ( "''", "null", $sSQL );
							$status = "Display only";
							if ($update_db) {
								$status = "Executed";
								DB::statement ( $sSQL ) or $status = "Error: " . mysql_error ();
							}
							$tags_loaded ++;
							$html .= "<td>$sSQL</td><td>$status</td></tr>";
						}
					}
				}
				
				$end_time=date('Y-m-d H:i:s');
				$sSQL="update `int_import_log` set `END_TIME`='$end_time',TAGS_READ='$tags_read',TAGS_LOADED='$tags_loaded',TAGS_REJECTED='$tags_rejected',TAGS_OVERRIDE='$tags_override' where ID=$log_id";
				$sSQL=str_replace("''","null",$sSQL);
				DB:: update($sSQL) or die (mysql_error());
				
				$str .= " <h3>Loader log</h3> ";
				$str .= " <table>";
				$str .= " <tr><td>Table</td><td>:". $table_name."</td></tr>";
				$str .= " <tr><td>Tab</td><td>:". $tab."</td></tr>";
				$str .= " <tr><td>Row start</td><td>:". $rowStart."</td></tr>";
				$str .= " <tr><td>Row finish</td><td>:". $rowFinish."</td></tr>";
				$str .= " <tr><td>File size</td><td>:". $highestRow."</td></tr>";
				$str .= " <tr><td>Update database</td><td>: <b>".($update_db?"Yes":"No")."</b></td></tr>";
				$str .= " <tr><td>Override data</td><td>: <b>".($override_data?"Yes":"No")."</b></td></tr>";
				$str .= " <tr><td></td></tr>";
				$str .= " <tr><td>Tags read</td><td>:". $tags_read."</td></tr>";
				$str .= " <tr><td>Tags loaded</td><td>:". $tags_loaded."</td></tr>";
				$str .= " <tr><td>Tags rejected</td><td>:". $tags_rejected."</td></tr>";
				$str .= " <tr><td>Tags override</td><td>:". $tags_override."</td></tr>";
				$str .= " <tr><td>Tags added</td><td>:". $tags_addnew."</td></tr>";
				$str .= " <tr><td>Loader start</td><td>:". $begin_time."</td></tr>";
				$str .= " <tr><td>Loader finish</td><td>:". $end_time."</td></tr>";
				$str .= " </table>";
				$str .= " <br>";
				$str .= " <table border='1'>";
				$str .= " <tr>";
				$str .= " <td><b>Command</b></td>";
				$str .= " <td><b>Status</b></td>";
				$str .= " </tr>".$html;
				$str .= " </table>";
				
				$reader->select(['str'=>$str])->first();
			} );
		}
		
		if (file_exists($path)) { unlink ($path); }
		return response ()->json ($xxx);
	}
}