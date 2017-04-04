<?php
namespace App\Jobs;

class ScheduleJob extends Job {
	
	protected $tmTask;
	
	public function __construct($tmTask){
		$this->tmTask = $tmTask;
	}
	
 	public function handle() {
 		\Log::info("{$this->tmTask->ID}:{$this->tmTask->name}:{$this->tmTask->count_run}:{$this->tmTask->status}");
	    sleep(3);
    }
}
