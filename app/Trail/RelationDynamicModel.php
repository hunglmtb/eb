<?php

namespace App\Trail;

trait RelationDynamicModel 
{
	
	public static function getSourceModel(){
		return null;
	}
	
	public static function getForeignColumn($row,$originCommand,$columnName){
		$command = $originCommand;
		if ($columnName=="OBJECT_ID") {
			/* $s_where	= array_key_exists('where', $oColumns)?$oColumns['where']:"";
				$s_order	= array_key_exists('order', $oColumns)?$oColumns['order']:""; */
			$s_where	= "";
			$s_order	= "";
			$namefield	= "NAME";
			$id			= $row&&array_key_exists('OBJECT_TYPE', $row)?$row["OBJECT_TYPE"]:1;
			$sourceModel= static::getSourceModel();
			$sourceModel= 'App\Models\\' .$sourceModel;
			$inject		= $sourceModel::find($id);
			if($inject&&$inject->CODE) {
				$ref_table	= $inject->CODE;
				$command 	= "select ID, $namefield from `$ref_table` $s_where $s_order ; --select";
			}
		}
		return $command;
	}
	
	public static function getDependences($columnName,$idValue){
		$option = null;
		if ($columnName=="OBJECT_TYPE") {
			$option = ["dependences"	=> [["name"		=>"ObjectName",
					"elementId"	=> "OBJECT_ID",
			]],
					"sourceModel"	=> static::getSourceModel(),
					"targets"		=> ["OBJECT_ID"],
					'extra'			=> ["OBJECT_TYPE"],
			];
				
		}
		return $option;
	}
}
