<?php 
namespace App\Models; 
use App\Models\DynamicModel; 
use App\Trail\ObjectNameLoad;

 class CodeProductType extends DynamicModel {
	use ObjectNameLoad;
	protected $table = 'CODE_PRODUCT_TYPE'; 
} 
