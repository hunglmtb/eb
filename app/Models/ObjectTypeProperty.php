<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class ObjectTypeProperty extends DynamicModel { 
	public static function loadBy($sourceData){
		$objectType 	= $sourceData['ObjectDataSource'];
		$id 			= $objectType->ID;	
		$code 			= $id&&$id!==0?$objectType->ID:$objectType->CODE;
		$model 			= 'App\\Models\\' .trim($code);
		$tableName 		= $model::getTableName ();
		$dates 			= $model::getDateFields();
		
		$tmp  			= GraphCfgFieldProps::where(['USE_FDC'=>1, 'TABLE_NAME'=>$tableName])
											->whereNotIn("COLUMN_NAME",$dates)
											->get(['COLUMN_NAME AS ID','COLUMN_NAME AS CODE', 'LABEL AS NAME']);
		$tmp 			= $tmp->each(function ($item, $key){
							if($item->NAME == '' || is_null($item->NAME)){
								$item->NAME = $item->CODE;
							}
						});
		return $tmp;
	}
} 
