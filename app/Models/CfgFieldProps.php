<?php

namespace App\Models;

use App\Models\DynamicModel;

class CfgFieldProps extends DynamicModel
{
    protected $table = 'cfg_field_props';
    
    public function LockTable(){
    	return $this->hasMany('App\Models\LockTable', 'TABLE_NAME', 'TABLE_NAME');
    }
    
    public function shouldLoadLastValueOf($object){
    	$should	= !$object||$this->RANGE_PERCENT&&$this->RANGE_PERCENT>0;
   	 	if (!$should) {
   	 		$objectExtension = isset ( $this->OBJECT_EXTENSION)?json_decode ($this->OBJECT_EXTENSION,true):[];
   	 		$found	= current(array_filter(array_keys($objectExtension), function($key) use($object,$objectExtension) { 
			    			$result	= $object->{$object::$idField}==$key;
			    			if ($result) {
			    				$overwrite 	= array_key_exists("OVERWRITE", $objectExtension[$key])?$objectExtension[$key]["OVERWRITE"]:false;
			    				$basic 		= array_key_exists("basic", $objectExtension[$key])?$objectExtension[$key]["basic"]:[];
			    				$result		= ($overwrite==true||$overwrite=="true")
			    								&&array_key_exists("RANGE_PERCENT", $basic)
			    								&&$basic["RANGE_PERCENT"]>0;
			    			}
			    			return $result;
			    	}));
   	 		$should =	$found!==FALSE;
    	}
    	return $should;
    }
    
    
    public static function getOriginProperties($where,$runQuery= true){
    	$properties = CfgFieldProps::where($where)
    	->orderBy('FIELD_ORDER')
    	->select('COLUMN_NAME as data',
    			'COLUMN_NAME as name',
    			'COLUMN_NAME',
    			'FDC_WIDTH as width',
    			'LABEL as title',
    			"DATA_METHOD",
    			"INPUT_ENABLE",
    			'INPUT_TYPE',
    			'VALUE_MAX',
    			'VALUE_MIN',
    			'DATA_FORMAT',
    			'VALUE_WARNING_MAX',
    			'VALUE_WARNING_MIN',
    			'RANGE_PERCENT',
    			'VALUE_FORMAT',
    			'ID',
    			'FIELD_ORDER',
    			'OBJECT_EXTENSION'
    	);
    	if ($runQuery) $properties = $properties->get();
    	return $properties;
    }
    public static function getConfigFields($tableName){
    	return static ::where('TABLE_NAME', '=', $tableName)
									->where('USE_FDC', '=', 1)
									->orderBy('FIELD_ORDER')
									->select('COLUMN_NAME');
    }
    
     public static function getFieldProperties($table,$field){
     	$re_prop 				= CfgFieldProps::where(['TABLE_NAME'=>$table, 'COLUMN_NAME'=>$field])->select('*')->get();
     	$mdl					= \Helper::getModelName($table);
     	$objectExtension 		= method_exists($mdl,"getObjects")?$mdl::getObjects():[];
     	$objectExtensionTarget 	= method_exists($mdl,"getObjectTargets")?$mdl::getObjectTargets():[];
     	
     	return ["data"					=> $re_prop,
     			"objectExtension"		=> $objectExtension,
     			'objectExtensionTarget'	=> $objectExtensionTarget,
     	];
     }
}
