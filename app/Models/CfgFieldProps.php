<?php

namespace App\Models;

use App\Models\DynamicModel;

class CfgFieldProps extends DynamicModel
{
    protected $table = 'cfg_field_props';
    
    public function LockTable(){
    	return $this->hasMany('App\Models\LockTable', 'TABLE_NAME', 'TABLE_NAME');
    }
}
