<?php

namespace App\Models;
use App\Models\DynamicModel;
use App\Trail\ObjectNameLoad;
class IntObjectType extends DynamicModel
{
	use ObjectNameLoad;
	
	protected $table = 'INT_OBJECT_TYPE';
	protected $primaryKey = 'ID';
	
	public function __construct($param=null) {
		if (is_array($param)) {
			parent::__construct($param);
			if(array_key_exists("ID", $param)&&
					array_key_exists("CODE", $param)&&
					array_key_exists("NAME", $param)
					){
				$this->ID 		= $param["ID"];
				$this->CODE 	= $param["CODE"];
				$this->NAME 	= $param["NAME"];
				$this->keyType 	= "string";
			}
		}
		else parent::__construct($param);
	}
	
	public static function find($id){
		if (!is_numeric($id)) {
			$objects = static ::getGraphObjectType();
			$instance = $objects->where('CODE',$id)->first();
			return $instance;
		}
		else  return static ::where('ID',$id)->first();
	}
	
	public static function getPreosObjectType(){
		$entries = static ::whereIn('CODE',['FLOW','ENERGY_UNIT','TANK','STORAGE'])->get();
		return $entries;
	}
	
	public static function getGraphObjectType($columns = array()){
		return  collect([
				new IntObjectType(['ID' =>	'FLOW'			,'CODE' =>	'FLOW'			,'NAME' => 'Flow'    		]),
				new IntObjectType(['ID' =>	'ENERGY_UNIT'	,'CODE' =>	'ENERGY_UNIT'	,'NAME' => 'Energy unit'	]),
				new IntObjectType(['ID' =>	'TANK'			,'CODE' =>	'TANK'			,'NAME' => 'Tank'    		]),
				new IntObjectType(['ID' =>	'STORAGE' 		,'CODE' =>	'STORAGE'		,'NAME' => 'Storage'    	]),
				new IntObjectType(['ID' =>	'EU_TEST'		,'CODE' =>	'EU_TEST'		,'NAME' => 'Well test'    	]),
				new IntObjectType(['ID' =>	'KEYSTORE'		,'CODE' =>	'KEYSTORE'		,'NAME' => 'Keystore'    	]),
		]);
	}
	
	public function ObjectDataSource($option=null){
		if ($option!=null&&is_array($option)) {
			$sourceData 	= ["IntObjectType"	=>	(object)[
															'CODE'	=>	$option['IntObjectType']["name"],
															'ID'	=>	$option['IntObjectType']["id"]]
								];
			$mdl 			= \Helper::getModelName ("ObjectDataSource");
			return $mdl::loadBy($sourceData);
		}
		return null;
	}
}
