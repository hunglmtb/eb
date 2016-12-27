<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\EBController;
use App\Http\Controllers\ProductDeliveryController;
use Illuminate\Http\Request;

class StorageDisplayController extends EBController {
    
	public function filter(Request $request){
		$postData 		= $request->all();
		$filterGroups	= ProductDeliveryController::storagedisplayFilter();
		return view ( 'front.cargoadmin.editfilter',['filters'			=> $filterGroups,
				'prefix'			=> "secondary_",
				"currentData"		=> $postData
		]);
	}
}
