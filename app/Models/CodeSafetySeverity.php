<?php

namespace App\Models;

use App\Models\DynamicModel;

class CodeSafetySeverity extends DynamicModel
{
    protected $table = 'code_safety_severity';
    
    public function __construct() {
    	$this->isReservedName = config('database.default')==='oracle';
    	parent::__construct();
    }    
}
