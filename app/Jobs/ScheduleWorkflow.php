<?php
namespace App\Jobs;

class ScheduleWorkflow extends ScheduleJob {

	public function handle() {
		$workflowProcessController = new WorkflowProcessController;
		$workflowProcessController->runTask($this->tmTask->ID,null);
	}
}
