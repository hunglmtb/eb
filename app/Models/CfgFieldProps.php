<?php

namespace App\Models;

use App\Models\DynamicModel;

class CfgFieldProps extends DynamicModel
{
    protected $table = 'cfg_field_props';
    
    public function LockTable(){
    	return $this->hasMany('App\Models\LockTable', 'TABLE_NAME', 'TABLE_NAME');
    }
    
    
    public static function getConfigFields($tableName){
    	return static ::where('TABLE_NAME', '=', $tableName)
									->where('USE_FDC', '=', 1)
									->orderBy('FIELD_ORDER')
									->select('COLUMN_NAME');
    }
}
