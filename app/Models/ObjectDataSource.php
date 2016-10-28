<?php 
namespace App\Models; 
use App\Models\DynamicModel; 
use App\Models\ObjectTypeProperty;

 class ObjectDataSource extends DynamicModel { 
 	
 	protected $primaryKey = 'ID2';
 	
	public static function loadBy($sourceData){
		$result				= null;
		if ($sourceData!=null&&is_array($sourceData)) {
			$objectType 	= $sourceData['IntObjectType'];
			$code 			= $objectType->CODE;
			$datasource 	= config("constants.tab");
			$collection		= $datasource[$code];
			$result 		= collect();
			$collection 	= $collection->each(function ($item, $key) use(&$result){
				$instance 	= new ObjectDataSource();
				$instance->CODE = $item->ID;
				$instance->ID = $item->ID;
				$instance->NAME = $item->NAME;
				$result->push($instance);
			});
		}
		return $result;
	}
	
	public static function find($id){
// 		$instance = (object)['NAME'=>$id, 'ID'=>$id];
		$instance = new ObjectDataSource(["NAME"	=> $id,"CODE"	=> $id]);
		$instance->CODE = $id;
		$instance->exists = false;
		return $instance;
	}
	
	public function ObjectTypeProperty(){
		$result = ObjectTypeProperty::loadBy(['ObjectDataSource'	=> $this]);
		return $result;
	}
} 
