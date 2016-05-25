<?php 
namespace App\Console\Commands;

use App\Models\TmWorkflow;
use App\Models\TmWorkflowTask;

use \Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

use Symfony\Component\Process\Process;

class RunProcess {
	
	public function __construct() {		
	}	
	
	public function finalizeTask($task_id,$status,$log,$email){
		if($task_id>0){
			TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>$status, 'FINISH_TIME'=>$time, 'LOG'=>addslashes($log)]);
				
			if($status==1){
				//task finish, check next task
				$this->processNextTask($task_id);
			}
		}
	}
	
	private function processNextTask($id){
		$r = TmWorkflowTask::where(['ID'=>$id])->select('next_task_config')->first();
	
		$config_next_task=str_replace('NaN,','',$r->next_task_config).'0';
		$ids=explode(',',$config_next_task);
		if($config_next_task == '') return -1;
	
		if(count($ids) >= 1){
			$tmps = TmWorkflowTask::whereIn('ID', $ids)->get();
			foreach ($tmps as $tmp){
				$this->runTask(null,$tmp);
			}
		}
	}
	
	public function sysRunTask($r){
		$taskname=$r['task_code'];
		$taskid=$r['id'];
		$taskconfig=json_decode($r['task_config']);

		if($taskname=='ALLOC_RUN'){
			$job_id=$taskconfig->jobid;
			$type=$taskconfig->type;
			if($taskconfig->from){
				$from=explode('-',$taskconfig->from);
				$from_date=$from[1].'-'.$from[2].'-'.$from[0];
			}
			else{
				$from_date="null";
			}
			if($taskconfig->to){
				$to=explode('-',$taskconfig->to);
				$to_date=$to[1].'-'.$to[2].'-'.$to[0];
			}
			else{
				$to_date="null";
			}
			$email=$taskconfig->email;
				
			$alloc_act = 'run';
			
			$commandline = "php artisan runAllo {".$taskid."} {".$job_id."} {".$alloc_act."} {".$type."} {".$from_date."} {".$to_date."} {".$email."}";
			$this->dispatch($this->execInBackground($commandline));
			
			
			
			/* $param = [
					'taskid'=> $taskid,
					'alloc_act'=> $alloc_act,
					'job_id'=> $job_id,
					'type'=> $type,
					'from_date'=> $from_date,
					'to_date'=> $to_date,
					'email'=> $email
			];
			Artisan::call('runAllo', $param); */
					
		}
		else if($taskname=='ALLOC_CHECK'){
			$job_id=$taskconfig->jobid;
			$type=$taskconfig->type;
			$from=explode('-',$taskconfig->from);
			$from_date=$from[1].'-'.$from[2].'-'.$from[0];
			$to=explode('-',$taskconfig->to);
			$to_date=$to[1].'-'.$to[2].'-'.$to[0];
			$email=$taskconfig->email;
			$alloc_act = 'check';
			
			/* $commandline = "D:\\xampp\\php\\php D:\xampp\htdocs\eblara\app\Console\Commands\Run.php {".$taskid."} {".$job_id."} {".$alloc_act."} {".$type."} {".$from_date."} {".$to_date."} {".$email."}";
			$this->execInBackground("D:\\xampp\\htdocs\\eblara\\app\\Helpers\\FormulaHelpers.php"); */
			
			$commandline = "php artisan runAllo {".$taskid."} {".$job_id."} {".$alloc_act."} {".$type."} {".$from_date."} {".$to_date."} {".$email."}";
			$this->dispatch($this->execInBackground($commandline));
			
			/* $param = [
				'taskid'=> $taskid,
				'alloc_act'=> $alloc_act,
				'job_id'=> $job_id,
				'type'=> $type,
				'from_date'=> $from_date,
				'to_date'=> $to_date,
				'email'=> $email
			];			
			Artisan::call('runAllo', $param); */
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
			execInBackground("..\\..\\report\\export.php $taskid $report_id $facility_id $type $from_date $to_date $email");
			//header('location:'."../../report/export.php?report_id={$id}&type=PDF&date_from={$from_date}&date_to={$to_date}&facility_id={$facility}&email={$email}");
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
		else if($taskconfig->formula_id>0){
		}
		else{
	
		}
	}
	
	private function execInBackground($commandline) {
		
		/* //pclose(popen("start /B \"workflow-task\" ".'"D:\\xampp\\php\\php" '.($commandline), "r"));
		
		 $move_to_project = "start /MIN D:\\xampp\\php\\php";
		*/
		$move_to_project = "D:\xampp\htdocs\eblara";
		$process = new Process($commandline);
		$process->setWorkingDirectory($move_to_project);
		$process->run();
	}
	
	private function finishWorkflow($task_id){
		$r = TmWorkflowTask::where(['ID'=>$task_id])->select('wf_id')->first();
		
		if(count($r->wf_id) >0 ){
			TmWorkflow::where(['ID'=>$r->wf_id])->update(['ISRUN'=>'no']);
		}
	}
	
	public function runTask($task_id,$r){
	
		if($task_id>0){
			$r = TmWorkflowTask::where(['ID'=>$task_id])->first();
		}
	
		if(count($r) <= 0) return;
	
		$task_id=$r['id'];
		$now = Carbon::now('Europe/London');
		$time = date('Y-m-d H:i:s', strtotime($now));
	
		if($r->isbegin == 1){
			//BEGIN node
			TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>1, 'START_TIME'=>$time, 'FINISH_TIME'=>$time]);
			$this->processNextTask($task_id);
		}
		else if($r->isbegin == -1){
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
			$from=explode('-',$taskconfig->from);
			$from_date=$from[1].'-'.$from[2].'-'.$from[0];
			$to=explode('-',$taskconfig->to);
			$to_date=$to[1].'-'.$to[2].'-'.$to[0];
			$email=$taskconfig->email;
	
			$r=1;
			//$r=evalFormula($formula_id,false);
	
			$conditions=$taskconfig->condition; //array
			foreach ($conditions as $cond_item) {
				$b=false;
				$exp=str_replace("=","==",$cond_item->condition);
					
				echo '$b=('.$exp.');';
				eval('$b=('.$exp.');');
				if($b===true){
					echo "*************";
					TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>1, 'FINISH_TIME'=>$time]);
	
					$target_task_id=$cond_item->target_task_id;
					if($target_task_id>0){
						$this->runTask($target_task_id);
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
	
	private function check_prev_finish($prev_id){
		$ids=explode(',',$prev_id);
		if(count($ids) == 1) return true;
	
		$tmp = TmWorkflowTask::whereIn('ID', $ids)
		->where('ISRUN', '<>', 1)
		->where('TASK_CODE', '<>', 'CONDITION_BLOCK')
		->where('TASK_CODE', '<>', '')
		->whereNotNull('TASK_CODE');
	
		if(count($tmp) > 0){
			return false;
		}else{
			return true;
		}
	}
}
