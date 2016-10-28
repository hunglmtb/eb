<?php

namespace App\Models;
use App\Models\DynamicModel;
use App\Trail\ObjectNameLoad;

class IntObjectType extends DynamicModel
{
	use ObjectNameLoad;
	
	protected $table = 'INT_OBJECT_TYPE';
	protected $primaryKey = 'ID';
	
	public static function getPreosObjectType(){
		$entries = static ::whereIn('CODE',['FLOW','ENERGY_UNIT','TANK','STORAGE'])->get();
		return $entries;
	}
	
	public static function getGraphObjectType($columns = array()){
		return  collect([
				(object)['ID' =>	'FLOW'			,'CODE' =>	'FLOW'			,'NAME' => 'Flow'    		],
				(object)['ID' =>	'ENERGY_UNIT'	,'CODE' =>	'ENERGY_UNIT'	,'NAME' => 'Energy unit'	],
				(object)['ID' =>	'TANK'			,'CODE' =>	'TANK'			,'NAME' => 'Tank'    		],
				(object)['ID' =>	'STORAGE' 		,'CODE' =>	'STORAGE'		,'NAME' => 'Storage'    	],
				(object)['ID' =>	'EU_TEST'		,'CODE' =>	'EU_TEST'		,'NAME' => 'Well test'    	],
		]);
	}
	
	public function ObjectDataSource($option=null){
		if ($option!=null&&is_array($option)) {
			$sourceData 	= ["IntObjectType"	=>	(object)[
															'CODE'	=>	$option['IntObjectType']["name"],
															'ID'	=>	$option['IntObjectType']["id"]]
								];
			$mdlName 		= "ObjectDataSource";
			$mdl 			= \Helper::getModelName ( $mdlName);
			return $mdl::loadBy($sourceData);
		}
		return null;
	}
}
