<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Safety extends Model
{
    protected $table = 'safety';
    public $timestamps = false;
    protected $primaryKey = 'ID';
    protected $safety_category_id = 'code_safety_category_id';
    
    public function __construct() {
    	$this->isReservedName = config('database.default')==='oracle';
    	parent::__construct();
    }
    
    public function getCodeSafetyCategory(){
    	return $this->belongsTo('App\Models\CodeSafetyCategory', $this->safety_category_id);
    }
}
