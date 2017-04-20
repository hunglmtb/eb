<?php
namespace App\Http\Controllers;

class TestController extends EBController {
    
    public function runSchedule(){
    	$output = shell_exec('cd .. & php artisan schedule:run');
    	return response ()->json ($output);
    }
}
