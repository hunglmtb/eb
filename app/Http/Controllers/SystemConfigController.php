<?php

namespace App\Http\Controllers;
 
class SystemConfigController extends EBController {
	
	public function tagsmapping(){
		$filterGroups = array('productionFilterGroup'	=>[
																['name'=>'IntObjectType',
																'independent'=>true],
															],
								'frequenceFilterGroup'	=> [	["name"			=> "ObjectName",
																"getMethod"		=> "loadBy",
																'default'		=> ['ID'=>0,'NAME'=>'All'],
																"source"		=> ['productionFilterGroup'=>["Facility","IntObjectType"]],
															]],
								'FacilityDependentMore'	=> ["ObjectName"],
								'extra' 				=> ['IntObjectType']
						);
		return view ( 'front.tagsmapping',['filters'=>$filterGroups]);
	}
}
