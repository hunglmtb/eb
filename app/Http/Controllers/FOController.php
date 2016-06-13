<?php

namespace App\Http\Controllers;
 
class FOController extends EBController {
	
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function safety(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date'],
				)
		);
		return view ( 'front.safety',['filters'=>$filterGroups]);
	}
}
