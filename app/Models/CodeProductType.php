<?php 
namespace App\Models; 
use App\Models\DynamicModel; 
use App\Trail\ObjectNameLoad;

 class CodeProductType extends DynamicModel {
	use ObjectNameLoad;
	protected $table = 'CODE_PRODUCT_TYPE'; 
	
	
	public static function find($id){
		if ($id==0) {
			$instance = new CodeProductType;
			return $instance;
		}
		else  return static ::where('ID',$id)->first();
	}
} 
