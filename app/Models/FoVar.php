<?php

namespace App\Models;

use App\Models\DynamicModel;

class FoVar extends DynamicModel
{
    protected $table = 'FO_VAR';
    protected $primaryKey = 'ID';
    
    public function Formula($fields=null)
    {
    	if ($fields!=null&&is_array($fields)) {
    		return $this->belongsTo('App\Models\Formula', 'FORMULA_ID', 'ID')->select($fields);
    	}
    	return $this->belongsTo('App\Models\Formula', 'FORMULA_ID', 'ID');
    }
}
