<?php

namespace App\Models;

use App\Models\DynamicModel;
use App\Models\FacilitySafetyCategory;

class CodeSafetyCategory extends DynamicModel
{
    protected $table = 'code_safety_category'; 
    protected $category_col = 'category_id';       
    
    /* public function __construct() {
    	$this->isReservedName = config('database.default')==='oracle';
    	parent::__construct();
    }   */  
    
    public function facilitySafetyCategory(){
    	return $this->hasMany('App\Models\FacilitySafetyCategory', 'SAFETY_CATEGORY_ID', 'ID');
    }    
    
    /* 
    public function safety(){
    	return $this->belongsTo('App\Models\Safety', $this->$category_col, $this->primaryKey);
    } */
    
}
