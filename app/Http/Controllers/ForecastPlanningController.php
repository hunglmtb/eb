<?php

namespace App\Http\Controllers;
 
class ForecastPlanningController extends EBController {
	
	public function forecast(){
		$filterGroups = array(	'productionFilterGroup'	=>['EnergyUnit'],
								'frequenceFilterGroup'=> [['name'=>'ExtensionPhaseType','single'=> true],
															'ExtensionValueType',
															'ExtensionDataSource'],
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
								'enableButton'		=> false
						);
		return view ( 'fp.forecast',['filters'=>$filterGroups]);
	}
	
	public function preos(){
		$filterGroups = array(	'productionFilterGroup'	=>[
																['name'=>'IntObjectType',		'independent'=>true,'getMethod'=>'getPreosObjectType'],
																['name'=>'ExtensionPhaseType',	'independent'=>true,'getMethod'=>'getPreosPhaseType'],
																'IntObjectType'			=>'ObjectName',
															],
								'frequenceFilterGroup'	=> [
																['name'=>'ExtensionValueType','getMethod'=>'getPreosObjectType'],
																['name'=>'ExtensionDataSource','getMethod'=>'getPreosObjectType'],
															],
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date']),
								'enableButton'			=> 	false,
								'extra' 				=> 	['IntObjectType','ExtensionPhaseType']
		);
		return view ( 'fp.preos',['filters'=>$filterGroups]);
	}
	
	public function allocateplan(){
		$filterGroups = array(	'productionFilterGroup'	=>[
				['name'=>'IntObjectType',		'independent'=>true,'getMethod'=>'getPreosObjectType'],
				['name'=>'ExtensionPhaseType',	'independent'=>true,'getMethod'=>'getPreosPhaseType'],
				'IntObjectType'			=>'ObjectName',
		],
				'frequenceFilterGroup'	=> [],
				'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
						['id'=>'date_end','name'=>'To date']),
// 				'enableButton'			=> 	false,
				'extra' 				=> 	['IntObjectType','ExtensionPhaseType']
		);
		return view ( 'fp.allocateplan',['filters'=>$filterGroups]);
	}
}
