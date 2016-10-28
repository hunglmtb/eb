<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class ObjectTypeProperty extends DynamicModel { 
	public static function loadBy($sourceData){
		$objectType 	= $sourceData['ObjectDataSource'];
		$id 			= $objectType->ID;	
		$code 			= $id&&$id!==0?$objectType->ID:$objectType->CODE;
// 		$result 		= array ();
		$model 			= 'App\\Models\\' .$code;
		$tableName 		= $model::getTableName ();
		
		$tmp  			= CfgFieldProps::where(['USE_FDC'=>1, 'TABLE_NAME'=>$tableName])->get(['COLUMN_NAME AS CODE', 'LABEL AS NAME']);
		$tmp 			= $tmp->each(function ($item, $key){
							if($item->NAME == '' || is_null($item->NAME)){
								$item->NAME = $item->CODE;
							}
						});
		
							
		/* if(count($tmp) > 0){
			foreach ($tmp as $t){
				if($t->NAME == '' || is_null($t->NAME)){
					$t->NAME = $t->CODE;
				}
				array_push($result, $t);
			}
		} */
		
		return $tmp;
	}
} 
