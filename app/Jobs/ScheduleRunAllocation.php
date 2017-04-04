<?php
namespace App\Jobs;

class ScheduleRunAllocation extends runAllocation {
	
	public function __construct($tmTask){
		$param = [
				'taskid'	=> 1,
				'act'		=> $alloc_act,
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
