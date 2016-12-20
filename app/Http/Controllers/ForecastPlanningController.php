<?php

namespace App\Http\Controllers;
use App\Models\IntImportSetting; 

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
																['name'			=>'ObjectName',
																'extra'			=> ["Facility","IntObjectType","ExtensionPhaseType"],
																],
															],
								'frequenceFilterGroup'	=> [
																['name'=>'ExtensionValueType','getMethod'=>'getPreosObjectType'],
																['name'=>'ExtensionDataSource','getMethod'=>'getPreosObjectType'],
															],
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date']),
								'enableButton'			=> false,
								'extra' 				=> ['IntObjectType','ExtensionPhaseType'],
								'FacilityDependentMore'	=> ["ObjectName"],
		);
		return view ( 'fp.preos',['filters'=>$filterGroups]);
	}
	
	public function allocateplan(){
		$filterGroups = array(	'productionFilterGroup'	=>[
				['name'			=>'IntObjectType',		'independent'=>true,'getMethod'=>'getPreosObjectType'],
				['name'			=>'ExtensionPhaseType',	'independent'=>true,'getMethod'=>'getPreosPhaseType'],
				['name'			=>'ObjectName',
				'extra'			=> ["Facility","IntObjectType","ExtensionPhaseType"],
				],
		],
				'frequenceFilterGroup'	=> [],
				'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
						['id'=>'date_end','name'=>'To date']),
// 				'enableButton'			=> 	false,
				'extra' 				=> 	['IntObjectType','ExtensionPhaseType'],
				'FacilityDependentMore'	=> ["ObjectName"],
		);
		return view ( 'fp.allocateplan',['filters'=>$filterGroups]);
	}
	
	public function loadplan(){
		$filterGroups = array(	'productionFilterGroup'	=>[
				['name'=>'IntObjectType',		'independent'=>true,'getMethod'=>'getPreosObjectType'],
				['name'=>'ExtensionPhaseType',	'independent'=>true,'getMethod'=>'getPreosPhaseType'],
				['name'			=>'ObjectName',
				'extra'			=> ["Facility","IntObjectType","ExtensionPhaseType"],
				],
		],
				'frequenceFilterGroup'	=> [],
				'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
						['id'=>'date_end','name'=>'To date']),
				'extra' 				=> 	['IntObjectType','ExtensionPhaseType'],
				'FacilityDependentMore'	=> ["ObjectName"],
		);
		
		$int_import_setting = IntImportSetting::all('ID', 'NAME');
		return view ( 'fp.loadplanforecast',['filters'=>$filterGroups, 'int_import_setting'=>$int_import_setting]);
	}
	
	public function choke(){
		$filterGroups = array(	'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
								'enableButton'			=> 	false,
						);
	
		return view ( 'fp.choke',['filters'=>$filterGroups]);
	}
}
