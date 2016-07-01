<?php

namespace App\Http\Controllers;
 
class SystemConfigController extends EBController {
	
	public function tagsmapping(){
		$filterGroups = array('productionFilterGroup'	=>	[
																['name'=>'IntObjectType',
																'independent'=>true],
																'IntObjectType'=>'ObjectName',
															],
								'extra' 				=> 	['IntObjectType']
						);
		return view ( 'front.tagsmapping',['filters'=>$filterGroups]);
	}
}
