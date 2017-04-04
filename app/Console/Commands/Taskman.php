<?php namespace App\Console\Commands;

use App\Models\TmTask;
use Illuminate\Console\Command;
use App\Jobs\ScheduleRunAllocation;
use App\Jobs\ScheduleTestJob;

class Taskman extends Command {

	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'taskman';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'task manager';

	protected $scheduleJobs = [];
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		\Log::info('task manager');
		$tmTasks	= TmTask::where("status",">",0)->get();
		if ($tmTasks) {
 			\Log::info("id:name:count_run:status");
			$tmTasks->each(function ($tmTask, $key){
				$scheduleJob = $this->initScheduleJob($tmTask);
				if ($scheduleJob) {
					\Log::info("check task ".$tmTask->name);
					$tmTask->preRunTask($scheduleJob);
					try {
						$result = $scheduleJob->handle();
					} catch (Exception $e) {
						$result = $e;
						\Log::info($e->getMessage());
						\Log::info($e->getTraceAsString());
					}
					$tmTask->afterRunTask($scheduleJob,$result);
				}
    		});
		}
	}
	
	public function initScheduleJob($tmTask) {
		$validated		= $tmTask->validateTaskCondition();
		$scheduleJob	= null;
		if ($validated){
			$scheduleJob = $this->getScheduleJob($tmTask);
			if (!$scheduleJob){
				switch ($tmTask->task_code) {
					case "ALLOC_RUN":
// 						$scheduleJob = new ScheduleRunAllocation($tmTask);
					break;
					
					default:
						$scheduleJob = new ScheduleTestJob($tmTask);
					break;
				}
			}
		}
		if ($scheduleJob) $this->scheduleJobs[$tmTask->ID]	= $scheduleJob;
		return $scheduleJob;
	}
	
	public function getScheduleJob($tmTask) {
		if (array_key_exists($tmTask->ID, $this->scheduleJobs)) 
			return $this->scheduleJobs[$tmTask->ID];
		return null;
	}
}
