<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
// 		'App\Console\Commands\Inspire',
		'App\Console\Commands\Taskman',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
// 		$schedule->command('inspire')->hourly();
		$schedule->command('taskman')->everyMinute()->before(function () {
 			\Log::info("before schedule taskman at ".Carbon::now()->toDateTimeString());
		})
         ->after(function () {
 			\Log::info("after schedule taskman at ".Carbon::now()->toDateTimeString());
         });
	}

}
