<?php
namespace App\Trail;

trait ScheduleJobTrail {
	
	protected $tmTask;
	
	public function __construct($tmTask){
		$this->tmTask = $tmTask;
	}
	
	public function setTask($tmTask){
		$this->tmTask = $tmTask;
	}
	
 	/* public function handle() {
 		\Log::info("{$this->tmTask->ID}:{$this->tmTask->name}:{$this->tmTask->count_run}:{$this->tmTask->status}");
    } */
    
    public function stop(){
    }
    
    public function shouldRun(){
//     	\Log::info("name {$this->tmTask->name} shouldRun true ");
    	return true;
    }
}
