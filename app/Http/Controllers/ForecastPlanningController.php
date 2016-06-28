<?php

namespace App\Http\Controllers;
 
class ForecastPlanningController extends EBController {
	
	public function forecast(){
		$filterGroups = array(	'productionFilterGroup'	=>['EnergyUnit'],
								'frequenceFilterGroup'=> ['ExtensionPhaseType','ExtensionValueType','ExtensionDataSource'],
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
								'enableButton'		=> false
						);
		return view ( 'fp.forecast',['filters'=>$filterGroups]);
	}
}
