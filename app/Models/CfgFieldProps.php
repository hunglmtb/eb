<?php

namespace App\Models;

use App\Models\DynamicModel;

class CfgFieldProps extends DynamicModel
{
    protected $table = 'cfg_field_props';
    protected $primaryKey = 'ID';
    
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
    
    
    public static function getConfigFields($tableName){
    	return static ::where('TABLE_NAME', '=', $tableName)
									->where('USE_FDC', '=', 1)
									->orderBy('FIELD_ORDER')
									->select('COLUMN_NAME');
    }
}
