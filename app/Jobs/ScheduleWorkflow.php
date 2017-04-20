<?php
namespace App\Jobs;
use App\Http\Controllers\DVController;
use App\Models\TmWorkflow;
use App\Trail\ScheduleJobTrail;

class ScheduleWorkflow extends Job {

	use ScheduleJobTrail;
	
	public function shouldRun(){
		$should			= false;
		$tmWorkflowId	= $this->getTmWorkflowId();
		if ($tmWorkflowId) {
			$tmWorkflow	= TmWorkflow::find($tmWorkflowId);
			if ($tmWorkflow) {
				$should	= $tmWorkflow->isrun=='no';
// 				\Log::info("tmWorkflowId $tmWorkflowId tmWorkflow->ID $tmWorkflow->id ISRUN ".$tmWorkflow->isrun." should $should");
			}
		}
		return $should;
	}
	
	public function handle() {
		$tmWorkflowId	= $this->getTmWorkflowId();
		if ($tmWorkflowId) {
			$dvController = new DVController;
			$dvController->runWorkFlowId($tmWorkflowId);
			return "handle success";
		}
		return "handle nothing : TmWorkflow id is not specified";
	}
	
	public function stop(){
		$tmWorkflowId	= $this->getTmWorkflowId();
		if ($tmWorkflowId) {
			$dvController = new DVController;
			$dvController->stopWorkFlowId($tmWorkflowId);
			return "stop success";
		}
		return "stop nothing : TmWorkflow id is not specified";
	}
	
	public function getTmWorkflowId(){
		$tmWorkflowId	= null;
		$taskConfig		= $this->tmTask->task_config;
		if ($taskConfig&&is_array($taskConfig)&&array_key_exists("TmWorkflow", $taskConfig)) {
			$tmWorkflowId 	= $taskConfig["TmWorkflow"];
		}
		return $tmWorkflowId;
	}
}
