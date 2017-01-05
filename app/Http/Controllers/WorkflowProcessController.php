<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Carbon\Carbon;
use Mail;

use App\Models\TmWorkflow;
use App\Models\TmWorkflowTask;
use App\Jobs\runAllocation;
use App\Jobs\export;

class WorkflowProcessController extends Controller {
	protected $tmworkflowtask=[], $task_id;
	
	public function __construct($task_id, $tmworkflowtask) {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
		
		$this->tmworkflowtask = $tmworkflowtask;
		$this->task_id = $task_id;
	}
	
	public function runTask($task_id,$r){
		if($task_id>0){
			$r = TmWorkflowTask::where(['ID'=>$task_id])->first();
		}
	
		if(count($r) <= 0) return;
	
		$task_id=$r['id'];
		$now = Carbon::now('Europe/London');
		$time = date('Y-m-d H:i:s', strtotime($now));
		 
		if($r['isbegin'] == 1){
			\Log::info('BEGIN');
			//BEGIN node
			TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>1, 'START_TIME'=>$time, 'FINISH_TIME'=>$time]);
			$this->processNextTask($task_id);
		}
		else if($r['isbegin'] == -1){
			\Log::info('END');
			//END node
			TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>1, 'START_TIME'=>$time, 'FINISH_TIME'=>$time]);
			$this->finishWorkflow($task_id);
		}
		else if(strpos($r->task_config,'formula_id') !== false){
			//CONDITION node
			$taskconfig=json_decode($r->task_config);
	
			TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>2, 'START_TIME'=>$time]);
	
			$formula_id=$taskconfig->formula_id;
			$type=$taskconfig->type;
			
			if(isset($taskconfig->from)){
				$from=explode('-',$taskconfig->from);
				$from_date=$from[1].'-'.$from[2].'-'.$from[0];
			}else {
				$from_date = null;
			}
			
			if(isset($taskconfig->to)){
				$to=explode('-',$taskconfig->to);
				$to_date=$to[1].'-'.$to[2].'-'.$to[0];
			}else{
				$to_date = null;
			}
			$r=1;
			//$r=evalFormula($formula_id,false);
	
			$conditions=$taskconfig->condition; //array
			foreach ($conditions as $cond_item) {
				$b=false;
				$exp=str_replace("=","==",$cond_item->condition);
	
				//echo '$b=('.$exp.');';
				eval('$b=('.$exp.');');
				if($b === $r){
					//echo "*************";
					TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>1, 'FINISH_TIME'=>$time]);
	
					$target_task_id=$cond_item->target_task_id;
					if($target_task_id>0){
						$this->runTask($target_task_id, null);
						return;
					}
				}
			}
		}
		else if($r->task_code == 'NODE_COMBINE' || (!$r->task_code && !$r->task_config)){
			//COMBINE node
			$prev_id=str_replace('NaN,','',$r->prev_task_config).'0';
			if($this->check_prev_finish($prev_id)){
				TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>1, 'FINISH_TIME'=>$time]);
				$this->processNextTask($task_id);
			}
		}
		else{
			//task node
			TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>2, 'FINISH_TIME'=>$time]);
			if($r->runby == 1){
				$this->sysRunTask($r);
			}
		}
	}
	
	public function processNextTask($id){
		\Log::info('processNextTask  :'.$id);
		$r = TmWorkflowTask::where(['ID'=>$id])->select('next_task_config')->first();
	
		$config_next_task=str_replace('NaN,','',$r['next_task_config']).'0';
		$ids=explode(',',$config_next_task);
		if($config_next_task == '') return -1;
	
		if(count($ids) >= 1){
			$tmps = TmWorkflowTask::whereIn('ID', $ids)->get();
			foreach ($tmps as $tmp){
				$this->runTask(null,$tmp);
			}
		}
	}
	
	public function finishWorkflow($task_id){
		$r = TmWorkflowTask::where(['ID'=>$task_id])->select('wf_id')->first();
		if(count($r) >0 ){
			TmWorkflow::where(['ID'=>$r['wf_id']])->update(['ISRUN'=>'no']);
		}
	}
	
	private function check_prev_finish($prev_id){
		$ids=explode(',',$prev_id);
		if(count($ids) == 1) return true;
	
		$tmp = TmWorkflowTask::whereIn('ID', $ids)
		->where('ISRUN', '<>', 1)
		->where('TASK_CODE', '<>', 'CONDITION_BLOCK')
		->where('TASK_CODE', '<>', '')
		->whereNotNull('TASK_CODE')->get();
	
		if(count($tmp) > 0){
			return false;
		}else{
			return true;
		}
	}
	
	public function sysRunTask($r){
		$taskname=$r['task_code'];
		$taskid=$r['id'];
		$taskconfig=json_decode($r['task_config']);
	
		if($taskname == 'ALLOC_RUN'){
			$job_id=$taskconfig->jobid;
			$type=$taskconfig->type;
			if(isset($taskconfig->from)){
				//$from=explode('-',$taskconfig->from);
				//$from_date=$from[1].'-'.$from[2].'-'.$from[0];
				$from_date = $taskconfig->from;
			}
			else{
				$from_date="null";
			}
			if(isset($taskconfig->to)){
				//$to=explode('-',$taskconfig->to);
				//$to_date=$to[1].'-'.$to[2].'-'.$to[0];
				$to_date = $taskconfig->to;
			}
			else{
				$to_date="null";
			}
			$email=$taskconfig->email;
			$alloc_act = 'run';
			 
			$param = [
					'taskid'=> $taskid,
					'act'=> $alloc_act,
					'job_id'=> $job_id,
					'type'=> $type,
					'from_date'=> $from_date,
					'to_date'=> $to_date,
					'email'=> $email
			];
	
			/* $obj = new run($param);
			 $obj->handle(); */
	
			$job =(new runAllocation($param));
			$this->dispatch($job);
		}
		else if($taskname=='ALLOC_CHECK'){
			$job_id=$taskconfig->jobid;
			$type=$taskconfig->type;
			if(isset($taskconfig->from)){
				//$from=explode('-',$taskconfig->from);
				//$from_date=$from[1].'-'.$from[2].'-'.$from[0];
				$from_date = $taskconfig->from;
			}
			else{
				$from_date="null";
			}
			if(isset($taskconfig->to)){
				//$to=explode('-',$taskconfig->to);
				//$to_date=$to[1].'-'.$to[2].'-'.$to[0];
				$to_date = $taskconfig->to;
			}
			else{
				$to_date="null";
			}

			$email=$taskconfig->email;
			$alloc_act = 'check';
			 
			$param = [
					'taskid'=> $taskid,
					'act'=> $alloc_act,
					'job_id'=> $job_id,
					'type'=> $type,
					'from_date'=> $from_date,
					'to_date'=> $to_date,
					'email'=> $email
			];
	
			$job = (new runAllocation($param));
			$this->dispatch($job);
		}
		else if($taskname=='VIS_REPORT'){
			$report_id=$taskconfig->reportid;
			$facility_id=$taskconfig->facility;
			$type=$taskconfig->type;
			$from=explode('-',$taskconfig->from);
			$from_date=$from[1].'-'.$from[2].'-'.$from[0];
			$to=explode('-',$taskconfig->to);
			$to_date=$to[1].'-'.$to[2].'-'.$to[0];
			$email=$taskconfig->email;
	
			$param = [
					'task_id'=> $taskid,
					'report_id'=> $report_id,
					'facility_id'=> $facility_id,
					'date_type'=> $type,
					'from_date'=> $from_date,
					'to_date'=> $to_date,
					'email'=> $email
			];
	
			/* $obj = new export($param);
			$obj->handle($param); */
			
			$job = (new export($param));
			$this->dispatch($job);
		}
		else if($taskname=='INT_IMPORT_DATA'){
			$conn_id=$taskconfig->conn_id;
			$tagset_id=$taskconfig->tagset_id;
			$type=$taskconfig->type;
			$from=explode('-',$taskconfig->from);
			$from_date=$from[1].'-'.$from[2].'-'.$from[0];
			$to=explode('-',$taskconfig->to);
			$to_date=$to[1].'-'.$to[2].'-'.$to[0];
			$email=$taskconfig->email;
			execInBackground("..\\..\\interface\\pi.php $taskid $conn_id $tagset_id $type $from_date $to_date $email");
			//header('location:'."../../report/export.php?report_id={$id}&type=PDF&date_from={$from_date}&date_to={$to_date}&facility_id={$facility}&email={$email}");
		}
		else if($taskname == 'EMAIL'){
			$from = $taskconfig->from;
			$to = $taskconfig->to;
			$subject = $taskconfig->subject;
			$content = $taskconfig->content;
	
			if (filter_var($from, FILTER_VALIDATE_EMAIL) && filter_var($to, FILTER_VALIDATE_EMAIL)) {
				try
				{
					$mailFrom = env('MAIL_USERNAME');
					$data = ['content' => $content];
					$ret = Mail::send('front.sendmail',$data, function ($message) use ($from, $subject, $to) {
						$message->from($from, 'Your Application');
						$message->to($to)->subject($subject);
					});
				}catch (\Exception $e)
				{
					\Log::info($e->getMessage());
				}
			}
		}
	}
	
}