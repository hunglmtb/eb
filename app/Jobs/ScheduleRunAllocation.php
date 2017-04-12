<?php
namespace App\Jobs;

class ScheduleRunAllocation extends runAllocation {
	
	public function __construct($tmTask){
		$taskConfig			= $tmTask->task_config;
		$job_id				= $taskConfig->AllocJob;
		$email				= $taskConfig->SENDLOG;
		$type				= $taskConfig->STARTTIME["type"];
		$from_date			= $taskConfig->STARTTIME["value"];
		$to_date			= $taskConfig->ENDTIME["value"];
		$param = [
				'taskid'	=> 1,
				'act'		=> "run",
				'job_id'	=> $job_id,
				'type'		=> $type,
				'from_date'	=> $from_date,
				'to_date'	=> $to_date,
				'email'		=> $email
		];
		$this->param = $param;
	}
	
    public function finalizeTask($task_id,$status,$log,$email){
    }
}
