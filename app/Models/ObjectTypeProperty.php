<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class ObjectTypeProperty extends DynamicModel { 
	public static function loadBy($sourceData){
		$objectType 	= $sourceData['ObjectDataSource'];
		$id 			= $objectType->ID;	
		$code 			= $id&&$id!==0?$objectType->ID:$objectType->CODE;
		if (strpos($code, 'V_') === 0){
			$db_schema = ENV('DB_DATABASE');
// 			$s="SELECT COLUMN_NAME,COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$db_schema' AND TABLE_NAME = '$table'$condDataType";
			$tmp 		= \DB::table('INFORMATION_SCHEMA.COLUMNS')
							->where('TABLE_SCHEMA','=',$db_schema)
							->where('TABLE_NAME','=',$code)
							->where('COLUMN_NAME','<>',"ID")
							->whereIn('DATA_TYPE',['decimal','int','double'])
							->select(['COLUMN_NAME AS ID','COLUMN_NAME AS CODE', 'COLUMN_NAME AS NAME'])
							->get();
		}
		else {
			if (strpos($code, '_') !== false){
				$model		= \Helper::getModelName($code);
			}
			else $model 			= 'App\\Models\\' .trim($code);
			$tableName 		= $model::getTableName ();
			$dates 			= $model::getDateFields();
			
			$tmp  			= GraphCfgFieldProps::where(['USE_FDC'=>1, 'TABLE_NAME'=>$tableName])
												->whereNotIn("COLUMN_NAME",$dates)
												->where("INPUT_TYPE",2)
												->get(['COLUMN_NAME AS ID','COLUMN_NAME AS CODE', 'LABEL AS NAME']);
			$tmp 			= $tmp->each(function ($item, $key){
								if($item->NAME == '' || is_null($item->NAME)){
									$item->NAME = $item->CODE;
								}
							});
		}
		return $tmp;
	}
} 
