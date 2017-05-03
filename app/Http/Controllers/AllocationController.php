<?php

namespace App\Http\Controllers;
use App\Jobs\runAllocation;
use App\Models\AllocJob;
use App\Models\CodeAllocType;
use App\Models\CodeAllocFromOption;
use App\Models\CodeAllocValueType;
use App\Models\CodeFlowPhase;
use App\Models\Facility;
use App\Models\Network;
use App\Models\AllocRunner;
use App\Models\AllocRunnerObjects;
use App\Models\Flow;
use App\Models\EnergyUnit;
use App\Models\Tank;
use App\Models\Storage;
use App\Models\AllocCondOut;
use App\Models\AllocCondition;
use App\Models\JobDiagram;

use DB;
use Illuminate\Http\Request;

class AllocationController extends Controller {
	
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	
	public function _index() {
		$network = Network::where(['NETWORK_TYPE'=>1])->get(['ID', 'NAME']);
		$result = [];
		foreach ($network as $n){
			$tmp = [];
			$count = AllocJob::where(['NETWORK_ID'=>$n->ID])->count();
			if($count > 0){
				$tmp['NAME'] = $n->NAME.'('.$count.')';
			}else{
				$tmp['NAME'] = $n->NAME;
			}
				
			$tmp['ID'] = $n->ID;
				
			array_push($result, $tmp);
		}
		
		return view ( 'front.allocrun', ['result'=>$result]);
	}
	
	public function getJobsRunAlloc(Request $request) {
		$data = $request->all ();
		
		$result = $this->getAllocJob($data['NETWORK_ID']);
						
		return response ()->json ( $result );
	}
	
	private function getAllocJob($network_id){
		
		$allocjob = AllocJob::getTableName ();
		$code_alloc_value_type = CodeAllocValueType::getTableName();
		
		$result = DB::table ( $allocjob)
		->join ( $code_alloc_value_type, "$allocjob.VALUE_TYPE", '=', "$code_alloc_value_type.ID" )
		->where ( ["$allocjob.NETWORK_ID" => $network_id])
		->orderBy("$allocjob.ID")->select("$allocjob.*", "$code_alloc_value_type.name AS VALUE_TYPE_NAME")->get();
		
		return $result;
	}
	
	public function run_runner(Request $request) {
		$data = $request->all ();
		
		$objRun = new runAllocation($data);
		return response ()->json ($objRun->handle());
	}
	
	public function _indexconfig() {
		$network = Network::where(['NETWORK_TYPE'=>1])->get(['ID', 'NAME']);
		$result = [];
		foreach ($network as $n){
			$tmp = [];
			$count = AllocJob::where(['NETWORK_ID'=>$n->ID])->count();
			if($count > 0){
				$tmp['NAME'] = $n->NAME.'('.$count.')';
			}else{
				$tmp['NAME'] = $n->NAME;
			}
	
			$tmp['ID'] = $n->ID;
	
			array_push($result, $tmp);
		}
		
		$code_alloc_value_type = CodeAllocValueType::all('ID', 'NAME');
		$facility = Facility::all('ID', 'NAME');
		$code_alloc_type = CodeAllocType::all('ID', 'NAME');
		$codeFlowPhase = CodeFlowPhase::all('ID', 'NAME');
		$codeAllocValueType = CodeAllocValueType::all('ID', 'NAME');
		$codeAllocFromOption = CodeAllocFromOption::all('ID', 'NAME');
	
		return view ( 'front.allocset', [
				'result'=>$result, 
				'CodeAllocValueType'=>$code_alloc_value_type,
				'facility'=>$facility,
				'codeAllocType'=>$code_alloc_type,
				'codeFlowPhase' =>$codeFlowPhase,
				'codeAllocFromOption' =>$codeAllocFromOption,
				'codeAllocValueType' =>$codeAllocValueType
		]);
	}
	
	public function addJob (Request $request) {
		$data = $request->all ();
		
		AllocJob::insert($data);
		
		return response ()->json ('OK');
	}
	
	public function addrunner(Request $request) {
		$data = $request->all ();
		
		$job_id = $data ['job_id'];
		$runner_name = $data ['runner_name'];
		$order = $data ['order'];
		$alloc_type = $data ['alloc_type'];
		$theor_value_type = $data ['theor_value_type'];
		$theor_phase = $data ['theor_phase'];
		$from_option = $data ['from_option'];
		$obj_froms = explode ( ',', $data ['obj_from'] );
		$obj_tos = explode ( ',', $data ['obj_to'] );
		
		$param1 = [
			'NAME' =>$runner_name,
			'JOB_ID' =>	$job_id,
			'ORDER'=>$order,
			'ALLOC_TYPE'=>$alloc_type,
			'THEOR_VALUE_TYPE'=>$theor_value_type,
			'THEOR_PHASE'=>$theor_phase,
			'FROM_OPTION'=>$from_option
		];
		
		$condition = array (
				'ID' => -1
		);
		
		$runner = AllocRunner::updateOrCreate ( $condition, $param1 ); //AllocRunner::insert($param1);
		
		$runner_id = $runner->ID;
		if(!$runner_name && $runner_id>0)
		{
			AllocRunner::where(['ID'=>$runner_id])->update(['NAME'=>'R'.$runner_id]);
		}
		
		foreach($obj_froms as $obj_from)
		{
			$xs=explode(':',$obj_from);
			if($xs[1]=="")continue;		//Added by Q
			AllocRunnerObjects::insert(['RUNNER_ID'=>$runner_id, 'OBJECT_TYPE'=>$xs[1], 'OBJECT_ID'=>$xs[0], 'DIRECTION'=>1]); //'MINUS'=>$xs[2]
		}
		foreach($obj_tos as $obj_to)
		{
			$xs=explode(':',$obj_to);
			if($xs[1]=="")continue;		//Added by Q
			AllocRunnerObjects::insert(['RUNNER_ID'=>$runner_id, 'OBJECT_TYPE'=>$xs[1], 'OBJECT_ID'=>$xs[0], 'DIRECTION'=>0, 'FIXED'=>$xs[2]]);
		}
		
		return response ()->json ('OK');
	}
	
	public function getrunnerslist(Request $request) {
		$data = $request->all ();
		
		$alloc_runner = AllocRunner::getTableName();
		$code_alloc_value_type = CodeAllocValueType::getTableName();
		$code_alloc_type = CodeAllocType::getTableName();
		$code_flow_phase = CodeFlowPhase::getTableName();
		$code_alloc_from_option = CodeAllocFromOption::getTableName();
		
		$result = DB::table ( $alloc_runner . ' AS a' )
		->leftjoin ( $code_alloc_value_type . ' AS t', 'a.theor_value_type', '=', 't.ID' )
		->leftjoin ( $code_alloc_type . ' AS b', 'a.alloc_type', '=', 'b.ID' )
		->leftjoin ( $code_flow_phase . ' AS c', 'a.theor_phase', '=', 'c.ID' )
		->leftjoin ( $code_alloc_from_option . ' AS d', 'a.from_option', '=', 'd.ID' )
		->where ( ['a.JOB_ID' => $data['job_id']])
		->orderBy('a.ORDER')->select('a.*', 't.NAME AS THEOR_VALUE_TYPE_NAME', 'b.NAME AS ALLOC_TYPE_NAME','c.NAME AS THEOR_PHASE_NAME','d.NAME as ALLOC_FROM_SOURCE')->get();
		$i=0;
		$str = "";
		$runner_options="";
		foreach ($result as $row){	
			$runner_options .= "<option value='$row->ID'>$row->NAME</option>";
			$allocrunnerobjects = AllocRunnerObjects::where(['RUNNER_ID'=>$row->ID])->get();
			if(count($allocrunnerobjects) > 0){
				$o_in="";$o_out="";
				$o_in_x="";
				$o_out_x="";
				$count_in=0;
				$count_out=0;				
				$s = [];
				foreach($allocrunnerobjects as $ro){
					 $vname = '';
					 if($ro->OBJECT_TYPE == 1){
						 $f = Flow::where(['ID'=>$ro->OBJECT_ID])->select('NAME')->first();
						 $vname = $f->NAME;
					 }elseif($ro->OBJECT_TYPE == 2){
						 $f = EnergyUnit::where(['ID'=>$ro->OBJECT_ID])->select('NAME')->first();
						 $vname = $f->NAME;
					 }elseif($ro->OBJECT_TYPE == 3){
						 $f = Tank::where(['ID'=>$ro->OBJECT_ID])->select('NAME')->first();
						 $vname = $f->NAME;
					 }elseif($ro->OBJECT_TYPE == 4){
						 $f = Storage::where(['ID'=>$ro->OBJECT_ID])->select('NAME')->first();
						 $vname = $f->NAME;
					 }
					 	
					 $ro['OBJECT_NAME'] = $vname;
					 
					if($ro->DIRECTION == 1) //in
					{
						$o_in.="<span id='".$ro->ID."' o_type='".$ro->OBJECT_TYPE."' o_id='".$ro->OBJECT_ID."' minus='.$ro->MINUS.' style='display:block'>".($ro->MINUS==1? '<font color="red">->- </font>': '').$ro->OBJECT_NAME."</span>";
						if($count_in == 3)
						{
							$o_in_x="<span id='Q_I_$row->ID'>$o_in_x"."<br>... <span style='cursor:pointer;color:blue;text-decoration: underline;font-size:8pt' onclick='$(\"#Qobjectfrom_$row->ID\").show();$(\"#Q_I_$row->ID\").hide();'>Show all {objects}</span></span>";
						}
						else if($count_in<3)
						{
							$o_in_x.=($o_in_x?"<br>":"").$ro->OBJECT_NAME;
						}
						$count_in++;
					}
					else
					{
						$o_out.= "<span id='".$ro->ID."' o_type='".$ro->OBJECT_TYPE."' o_id='".$ro->OBJECT_ID."' fixed='".$ro->FIXED."' style='display:block;'>".$ro->OBJECT_NAME." ".($ro->FIXED==1? '<font color="#609CB9">->fixed</font>': '')."</span>";
						if($count_out==3)
						{
							$o_out_x="<span id='Q_O_$row->ID'>$o_out_x"."<br>... <span style='cursor:pointer;color:blue;text-decoration: underline;font-size:8pt' onclick='$(\"#Qobjectto_$row->ID\").show();$(\"#Q_O_$row->ID\").hide();'>Show all {objects}</span></span>";
						}
						else if($count_out<3)
						{
							$o_out_x.=($o_out_x?"<br>":"").$ro->OBJECT_NAME;
						}
						$count_out++;
					}
				}
				$i++;
				if ($i % 2 == 0)
					$bgcolor = "#eeeeee";
				else
					$bgcolor = "#f8f8f8";
				$str .= "<tr bgcolor='$bgcolor' class='runner_item' id='runner_item" . $row->ID . "' data-from_option='" . $row->FROM_OPTION . "'>";
				$str .= "<td style=\"cursor:pointer\" onclick=\"\"><span id='Qorder_" . $row->ID . "'>$row->ORDER</span></td>";
				$str .= "<td><span id='Qrunner_name_" . $row->ID . "'>$row->NAME</span></td>";
				$str .= "<td><span style='display:none' id='alloc_type_" . $row->ID . "'>$row->ALLOC_TYPE</span><span style='display:none' id='theor_value_type_" . $row->ID . "'>$row->THEOR_VALUE_TYPE</span><span style='display:none' id='theor_phase_" . $row->ID . "'>$row->THEOR_PHASE</span>$row->ALLOC_TYPE_NAME" . ($row->THEOR_PHASE_NAME ? "<br><font size=1 color=green>(Theor phase: $row->THEOR_PHASE_NAME)</font>" : "") . ($row->THEOR_VALUE_TYPE_NAME ? "<br><font size=1 color=green>(Theor value type: $row->THEOR_VALUE_TYPE_NAME)</font>" : "") . "</td>";
				$in_option = "";
				if($row->ALLOC_FROM_SOURCE){
					$in_option = "<font color='green'>&gt;&gt; $row->ALLOC_FROM_SOURCE</font><br>";
				}
				if ($count_in > 5)
					$str .= "<td>$in_option" . str_replace ( "{objects}", "$count_in objects", $o_in_x ) . "<span id='Qobjectfrom_" . $row->ID . "' style='display:none'>$o_in</span></td>";
				else
					$str .= "<td>$in_option<span id='Qobjectfrom_" . $row->ID . "'>$o_in</span></td>";
				if ($count_out > 5)
					$str .= "<td>" . str_replace ( "{objects}", "$count_out objects", $o_out_x ) . "<span id='Qobjectto_" . $row->ID . "' style='display:none'>$o_out</span></td>";
				else
					$str .= "<td><span id='Qobjectto_" . $row->ID . "'>$o_out</span></td>";
				
				$str .= "<td width='170' style='font-size:8pt'>&nbsp;";
				$str .= "<a href=\"javascript:checkRunner($row->ID)\">Simulate</a> |";
				$str .= "<a href=\"javascript:deleteRunner($row->ID)\">Delete</a> |";
				$str .= "<a href=\"javascript:editRunner($row->ID)\">Edit</a> |";
				$str .= "<a href=\"javascript:runRunner($row->ID)\">Run</a> |";
				$str .= "<a href=\"javascript:clearAllocData($row->ID)\">Clear</a></td>";
				$str .= "</tr>";
			}
			
			$result = $str."#$%".$runner_options;
		}
		
		return response ()->json ($result);
	}
	
	public function getconditionslist(Request $request) {
		$data = $request->all ();
		
		$alloc_condition = AllocCondition::getTableName();
		$alloc_runner = AllocRunner::getTableName();
		$alloc_cond_out = AllocCondOut::getTableName();
		
		$result = DB::table ( $alloc_condition . ' AS a' )
		->leftjoin ( $alloc_runner . ' AS b', 'a.RUNNER_TO_ID', '=', 'b.ID' )
		->join ( $alloc_runner . ' AS c', 'a.RUNNER_FROM_ID', '=', 'c.ID' )
		->where ( ['c.job_id'=>$data['job_id']])
		->select('a.*', 'b.NAME AS RUNNER_TO_NAME', 'c.NAME AS RUNNER_FROM_NAME')->get();		
		
		$str = "";
		$i = 0;
		
		foreach ($result as $row){
			$tmp = DB::table ( $alloc_cond_out . ' AS a' )
			->join ( $alloc_runner . ' AS b', 'a.RUNNER_TO_ID', '=', 'b.ID' )
			->where ( ['a.CONDITION_ID'=>$row->ID])
			->select('a.*', 'b.NAME AS RUNNER_TO_NAME')->get();
			
			$r_out="";
			$r_out_x="";
			
			foreach ($tmp as $ro){
				$r_out.=($r_out==""?"":", ")."$ro->VALUE: $ro->RUNNER_TO_NAME";
				$r_out_x.=($r_out_x==""?"":",")."$ro->VALUE:$ro->RUNNER_TO_ID:$ro->RUNNER_TO_NAME";
			}
			
			if($r_out=="")
			{
				$r_out="default:$row->RUNNER_TO_NAME";
				$r_out_x="default:$row->RUNNER_TO_ID:$row->RUNNER_TO_NAME";
			}
			
			$i++;
			if($i % 2==0) $bgcolor="#eeeeee"; else $bgcolor="#f8f8f8";
			$str .= "<tr bgcolor='$bgcolor'>";
			$str .= "<td><span id='Qcondition_name_".$row->ID."'>$row->NAME</span></td>";
			$str .= "<td><span id='Qcondition_out_".$row->ID."' style='display:none'>$r_out_x</span><input type='hidden' id='RUNNER_FROM_ID_".$row->ID."' value='$row->RUNNER_FROM_ID'><input type='hidden' id='RUNNER_TO_ID_".$row->ID."' value='$row->RUNNER_TO_ID'><span style='display:none' id='EXPRESSION_".$row->ID."'>$row->EXPRESSION</span>".substr($row->EXPRESSION,0,20).(strlen($row->EXPRESSION)>20?"...":"")."</td>";
			$str .= "	<td><span id='Qcondition_from_".$row->ID."'>$row->RUNNER_FROM_NAME</span></td>";
			$str .= "				<td>$r_out</td>";
			$str .= "				<td style='font-size:8pt'>&nbsp;";
			$str .= "				<a href=\"javascript:deleteCondtion($row->ID)\">Delete</a> |";
			$str .= "				<a href=\"javascript:editCondition($row->ID)\">Edit</a>";
			$str .= "				</td>";
			$str .= "				</tr>";
		}
		
		return response ()->json ($str);
	}
	
	public function deletejob(Request $request) {
		$data = $request->all ();
		
		AllocJob::where(['ID'=>$data['job_id']])->delete();
		
		return response ()->json ('ok');
	}
	
	public function deleterunner(Request $request) {
		$data = $request->all ();
	
		AllocRunnerObjects::where(['RUNNER_ID'=>$data['runner_id']])->delete();
		AllocRunner::where(['ID'=>$data['runner_id']])->delete();
	
		return response ()->json ('ok');
	}
	
	public function savecondition(Request $request) {
		$data = $request->all ();
		$job_id = $data['job_id'];
		$condition_id = $data['condition_id'];
		$condition = $data['condition'];
		$name = $data['name'];
		$expression = $data['expression'];
		$from_runner_id = $data['from_runner_id'];
		if ($condition_id > 0)
		{
			if(!$name) $name="C$condition_id";
			AllocCondition::where(['ID'=>$condition_id])->update(['NAME'=>$name, 'EXPRESSION'=>$expression, 'RUNNER_FROM_ID'=>$from_runner_id]);
		}
		else
		{
			/* $sql="insert into alloc_condition(NAME,EXPRESSION,RUNNER_FROM_ID) values('$name','$expression','$from_runner_id')";
			$re=mysql_query($sql) or die("Error: ".mysql_error());
			$condition_id=mysql_insert_id(); */
			
			$where = array (
					'ID' => -1
			);
			
			$allocCondition = AllocCondition::updateOrCreate ( $where, ['NAME'=>$name, 'EXPRESSION'=>$expression, 'RUNNER_FROM_ID'=>$from_runner_id] ); //AllocRunner::insert($param1);
			$condition_id = $allocCondition->ID;
			if(!$name)
			{
				$name="C$condition_id";
				/* $sql="UPDATE `alloc_condition` SET `NAME`='$name' WHERE `ID`='".$condition_id."';";
				$re=mysql_query($sql) or die("Error: ".mysql_error()); */
				
				AllocCondition::where(['ID'=>$condition_id])->update(['NAME'=>$name]);
			}
		}
		/* $sql="delete from alloc_cond_out where CONDITION_ID='$condition_id'";
		$re=mysql_query($sql) or die("Error: ".mysql_error()); */
		
		AllocCondOut::where(['CONDITION_ID'=>$condition_id]);
		
		$ss=explode(',',$condition);
		$xx=explode(':',$ss[0]);
		if(count($ss)>1 || $xx[0]!="")
		{
			foreach($ss as $s)
			{
				$xx=explode(':',$s);
				/* $s_f="INSERT INTO alloc_cond_out (CONDITION_ID, RUNNER_TO_ID, VALUE) VALUES('".$condition_id."', '".$xx[1]."', '".c."')";
				$re=mysql_query($s_f) or die("Error: ".mysql_error()); */
				
				AllocCondOut::insert(['CONDITION_ID'=>$condition_id, 'RUNNER_TO_ID'=>$xx[1], 'VALUE'=>'c']);
			}
		}
		else
		{
			/* $sql="UPDATE `alloc_condition` SET `RUNNER_TO_ID`='$xx[1]' WHERE `ID`='".$condition_id."';";
			$re=mysql_query($sql) or die("Error: ".mysql_error()); */
			
			AllocCondition::where(['ID'=>$condition_id])->update(['RUNNER_TO_ID'=>$xx[1]]);
		}
		
		return response ()->json ('ok');
	}
	
	public function clonenetwork(Request $request) {
		$data = $request->all ();
		
		$network_id=$data['network_id'];
		$network_name=addslashes($data['network_name']);
		$message = "Clone allocation group successfully";
		$success = true;
		$new_network_id = -1;
		if($network_id>0 && $network_name){			
			$condition = array (
					'ID' => -1
			);
			$tmp = NetWork::updateOrCreate ( $condition, ['NAME'=>$network_name, 'NETWORK_TYPE'=>1] );
			$new_network_id = $tmp->ID;
			
			if($new_network_id>0){
				$result_job = AllocJob::where(['NETWORK_ID' => $network_id])
					->select('ID' ,'CODE', 'NAME', 'NETWORK_ID', 'VALUE_TYPE', 'LAST_RUN', 'ALLOC_OIL', 'ALLOC_GAS', 'ALLOC_WATER', 'ALLOC_COMP', 'ALLOC_GASLIFT', 'ALLOC_CONDENSATE')
					->get();
				foreach ($result_job as $row_job)
				{
					$allocjob = $row_job->toArray();
					$allocjob['ID'] = null;
					$allocjob['NETWORK_ID'] = $new_network_id;
					
					$condition = array (
							'ID' => -1
					);
					$tmp = AllocJob::updateOrCreate ( $condition, $allocjob );
					$new_job_id = $tmp->ID;
					if($new_job_id>0){
						$result_runner = AllocRunner::where(['JOB_ID'=>$row_job->ID])->select('ID', 'CODE', 'NAME', 'JOB_ID', 'ORDER', 'ALLOC_TYPE', 'THEOR_PHASE', 'THEOR_VALUE_TYPE', 'LAST_RUN')->get();
						foreach ($result_runner as $row_runner)
						{
							$allocRunner = $row_runner->toArray();
							$allocRunner["ID"] = null;
							$allocRunner["JOB_ID"] = $new_job_id;
							$condition = array (
									'ID' => -1
							);
							$tmp = AllocRunner::updateOrCreate ( $condition, $allocRunner );
							$new_runner_id = $tmp->ID;
							if($new_runner_id>0){
								$result_objs = AllocRunnerObjects::where(['RUNNER_ID'=>$row_runner->ID])->select('RUNNER_ID', 'OBJECT_TYPE', 'OBJECT_ID', 'DIRECTION', 'FIXED', 'MINUS')->get();
								foreach ($result_objs as $row_objs)
								{									
									$allocRunnerObjects = $row_objs->toArray();
									$allocRunnerObjects["ID"] = null;
									$allocRunnerObjects["RUNNER_ID"] = $new_runner_id;
									AllocRunnerObjects::insert($allocRunnerObjects);
								}
							}
						}
					}
				}
			}
			else{
				$success = false;
				$message = "Can not add new network";
			}
		}
		else{
			$success = false;
			$message = "Incorect input data";
		}
		return response ()->json ( ["success" => $success, "message" => $message, "new_network_id" => $new_network_id ] );
	}
	public function jobdiagram($job_id) {
		return view ( 'front.jobdiagram', [ 
				'job_id' => $job_id 
		] );
	}
	public function loaddiagram($job_id) {
		$tmp = JobDiagram::where ( [ 
				'JOB_ID' => $job_id 
		] )->select ( 'DIAGRAM_CODE' )->first ();
		return response ()->json ( $tmp ['DIAGRAM_CODE'] );
	}
	public function editJob(Request $request) {
		$data = $request->all ();
		$job_id = $data ['id'];
		$job_name = $data ['name'];
		$value_type = $data ['value_type'];
		$gas = $data ['alloc_gas'];
		$oil = $data ['alloc_oil'];
		$water = $data ['alloc_water'];
		$comp = $data ['alloc_comp'];
		$gaslift = $data ['alloc_gaslift'];
		$condensate = $data ['alloc_condensate'];
		$daybyday = $data ['alloc_daybyday'];
		if ($gas == 0)
			$comp = 0;		
		
		if ($data ['clone'] == 1) {
			$r = AllocJob::where(['ID'=>$job_id])->select('NETWORK_ID')->first();
			$network_id = $r->NETWORK_ID;
			
			$condition = array (
					'ID' => -1
			);
			
			$allocjob = [
				'NAME'=>$job_name,
				'NETWORK_ID'=>$network_id,
				'VALUE_TYPE'=>$value_type,
				'ALLOC_GAS'=>$gas,
				'ALLOC_OIL'=>$oil,
				'ALLOC_WATER'=>$water,
				'ALLOC_COMP'=>$comp,
				'ALLOC_GASLIFT'=>$gaslift,
				'ALLOC_CONDENSATE'=>$condensate,
				'DAY_BY_DAY'=>$daybyday
			];
			
			$tmp = AllocJob::updateOrCreate ( $condition, $allocjob );
			$new_job_id = $tmp->ID;			
			$result = AllocRunner::where(['JOB_ID'=>$job_id])->select('*')->get();
			foreach ($result as $row)
			{
				$old_runner_id=$row->ID;				
				$allocRunner = AllocRunner::where(['ID'=>$old_runner_id])
				->get(['JOB_ID', 'ORDER', 'ALLOC_TYPE', 'THEOR_PHASE']);
				$allocRunner->JOB_ID = $new_job_id;
					
				$allocRunner = json_decode(json_encode($allocRunner), true);
				$condition = array (
						'ID' => -1
				);
				$tmp = AllocRunner::updateOrCreate ( $condition, $allocRunner );
				$new_runner_id = $tmp->ID;
				
				
				$alloc_runner_objects = AllocRunnerObjects::where(['RUNNER_ID'=>$old_runner_id])->select(DB::raw($new_runner_id.' AS RUNNER_ID') , 'OBJECT_TYPE', 'OBJECT_ID', 'DIRECTION', 'FIXED', 'MINUS')->get();
				
				$condition = array (
						'ID' => -1
				);
				$tmp = AllocRunner::updateOrCreate ( $condition, $alloc_runner_objects );
			}
		}
		else
		{
			$param = [
				'NAME'=>$job_name,
				'VALUE_TYPE'=>$value_type,
				'ALLOC_GAS'=>$gas,
				'ALLOC_OIL'=>$oil,
				'ALLOC_WATER'=>$water,
				'ALLOC_COMP'=>$comp,
				'ALLOC_GASLIFT'=>$gaslift,
				'ALLOC_CONDENSATE'=>$condensate,
				'DAY_BY_DAY'=>$daybyday
			];
			
			AllocJob::where(['ID'=>$job_id])->update($param);
		}
		
		return response ()->json ('ok');
	}
	
	public function saveEditRunner(Request $request) {
		$data = $request->all ();
		
		$runner_id = $data ['runner_id'];
		$runner_name = $data ['runner_name'];
		if (! $runner_name)
			$runner_name = "R$runner_id";
		$obj_froms = explode ( ',', $data ['obj_from'] );
		$obj_tos = explode ( ',', $data ['obj_to'] );
		$order = $data ['order'];
		$alloc_type = $data ['alloc_type'];
		$theor_value_type = $data ['theor_value_type'];
		$theor_phase = $data ['theor_phase'];
		$from_option = $data ['from_option'];
		//Update order
		AllocRunner::where(['ID'=>$runner_id])->update(['NAME'=>$runner_name, 'ORDER'=>$order, 'ALLOC_TYPE'=>$alloc_type, 'THEOR_VALUE_TYPE'=>$theor_value_type, 'THEOR_PHASE'=>$theor_phase, 'FROM_OPTION'=>$from_option]);
		
		//Delete all Object in runner
		AllocRunnerObjects::where(['RUNNER_ID'=>$runner_id])->delete();
		
		//Add again
		foreach($obj_froms as $obj_from)
		{
			$xs_f=explode(':',$obj_from);
			if($xs_f[1]=="")continue;
			AllocRunnerObjects::insert(['RUNNER_ID'=>$runner_id, 'OBJECT_TYPE'=>$xs_f[1], 'OBJECT_ID'=>$xs_f[0], 'DIRECTION'=>1, 'MINUS'=>$xs_f[2]]);
		}
		
		foreach($obj_tos as $obj_to)
		{
			$xs_t=explode(':',$obj_to);
			if($xs_t[1]=="")continue;
			AllocRunnerObjects::insert(['RUNNER_ID'=>$runner_id, 'OBJECT_TYPE'=>$xs_t[1], 'OBJECT_ID'=>$xs_t[0], 'DIRECTION'=>0, 'MINUS'=>$xs_t[2]]);
		}
		
		return response ()->json ('ok');
	}
}