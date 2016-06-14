<?php

namespace App\Models;

use App\Models\CodeSafetyCategory;
use App\Models\DynamicModel;

class FacilitySafetyCategory extends DynamicModel
{
    protected $table = 'facility_safety_category';
    protected $primaryKey = 'ID';
//     protected $safety_category_id = 'CODE_SAFETY_CATEGORY_ID';
    
   /*  public function __construct() {
    	$this->isReservedName = config('database.default')==='oracle';
    	parent::__construct();
    } */
    
     public function codeSafetyCategory(){
    	return $this->belongsTo('App\Models\CodeSafetyCategory', 'CODE_SAFETY_CATEGORY_ID', 'ID');
    }
}
