<?php

namespace App\Trail;

trait RelationDynamicModel 
{
	
	public static function getSourceModel(){
		return null;
	}
	
	public static function getForeignColumn($row,$originCommand,$columnName){
		$command = $originCommand;
		$sourceIdColumn		= isset(static :: $relateColumns)&&array_key_exists("id", static :: $relateColumns)?
							static :: $relateColumns["id"]:"OBJECT_ID";
		$sourceTypeColumn	= isset(static :: $relateColumns)&&array_key_exists("type", static :: $relateColumns)?
							static :: $relateColumns["type"]:"OBJECT_TYPE";
		if ($columnName==$sourceIdColumn) {
			/* $s_where	= array_key_exists('where', $oColumns)?$oColumns['where']:"";
				$s_order	= array_key_exists('order', $oColumns)?$oColumns['order']:""; */
			$s_where	= "";
			$s_order	= "";
			$namefield	= "NAME";
			$id			= $row&&array_key_exists($sourceTypeColumn, $row)?$row[$sourceTypeColumn]:1;
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
		$option 			= null;
		$sourceIdColumn		= isset(static :: $relateColumns)&&array_key_exists("id", static :: $relateColumns)?
							static :: $relateColumns["id"]:"OBJECT_ID";
		$sourceTypeColumn	= isset(static :: $relateColumns)&&array_key_exists("type", static :: $relateColumns)?
							static :: $relateColumns["type"]:"OBJECT_TYPE";
		
		if ($columnName==$sourceTypeColumn) {
			$option = ["dependences"	=> [["name"		=>"ObjectName",
					"elementId"	=> $sourceIdColumn,
			]],
					"sourceModel"	=> static::getSourceModel(),
					"targets"		=> [$sourceIdColumn],
					'extra'			=> [$sourceTypeColumn],
			];
				
		}
		return $option;
	}
}
