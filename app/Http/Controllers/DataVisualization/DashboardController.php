<?php

namespace App\Http\Controllers\DataVisualization;
use App\Models\Dashboard;
use App\Http\Controllers\CodeController;

class DashboardController extends CodeController {
	
	public function all(){
		$results = Dashboard::orderBy("TYPE")->get();
    	return response()->json($results);
	}
}