<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\ViewComposers\ProductionGroupComposer;

class CodeController extends EBController {
	 
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function getCodes(Request $request)
    {
		$options = $request->only('type','value', 'dependences');
		
		$mdl = 'App\Models\\'.$options['type'];
		$unit = $mdl::find($options['value']);
// 		->value('email');all(['ID', 'NAME']);
		$results = [];
		
		foreach($options['dependences'] as $model ){
			$eCollection = $unit->$model(['ID', 'NAME'])->getResults();
			if (count ( $eCollection ) > 0) {
				$unit = ProductionGroupComposer::getCurrentSelect ( $eCollection );
				$results [] = ProductionGroupComposer::getFilterArray ( $model, $eCollection, $unit );
			}
			else break;
		}
		
		return response($results, 200) // 200 Status Code: Standard response for successful HTTP request
			->header('Content-Type', 'application/json');
    }
}
