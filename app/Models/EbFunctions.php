<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class EbFunctions extends DynamicModel 
{ 
	protected $table = 'EB_FUNCTIONS'; 
	protected static $isAddAllAsDefault	= true;
// 	protected $primaryKey = 'CODE';
	
	public static function loadBy($sourceData){
		$entries = static ::where('USE_FOR','like',"%TASK_GROUP%")
							->whereIn('LEVEL',[1,2])
							->select("CODE","ID",
									"PATH as FUNCTION_URL",
									\DB::raw("concat(case when PARENT_CODE is null then '' else '--- ' end,NAME) as NAME"))
							->orderBy("CODE")
							->get();
		return $entries;
	}
	
	public static function find($id){
		if($id==="0"||$id===0){
			$instance		= new EbFunctions;
			$instance->CODE	= 0;
			$instance->NAME	= "All";
			return $instance;
		}
		else if(is_string($id)) return static::where('CODE',$id)->first();
		else return null;
	}
	
	public function ExtensionEbFunctions($option=null){
		$sourceData 	= ["EbFunctions"	=>	(object)[
				'CODE'	=>	$this->CODE,
				'ID'	=>	$this->ID
		]];
		return ExtensionEbFunctions::loadBy($sourceData);
	}
	
} 
