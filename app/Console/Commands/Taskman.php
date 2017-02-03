<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use App\Models\TmTask;

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

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		\Log::info('task manager');
		$tmTasks	= TmTask::where()->get();
		if ($tmTasks) {
			$tmTasks->each(function ($tmTask, $key){
				$tmTask->handleScheduleJob();
    		});
		}
		//$this->comment(PHP_EOL.Inspiring::quote().PHP_EOL);
	}

}
