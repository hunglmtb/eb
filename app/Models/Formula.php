<?php

namespace App\Models;

use App\Models\DynamicModel;

class Formula extends DynamicModel
{
    protected $table = 'FORMULA';
    protected $primaryKey = 'ID';
  
    public function FoVar($fields=null)
    {
    	if ($fields!=null&&is_array($fields)) {
    		return $this->hasMany('App\Models\FoVar', 'FORMULA_ID', 'ID')->select($fields);
    	}
    	return $this->hasMany('App\Models\FoVar', 'FORMULA_ID', 'ID')
    	->select(['*',\DB::raw("case when (STATIC_VALUE like '%-%-%' or STATIC_VALUE = '@OCCUR_DATE') then 1 else 0 end as IS_DATE")])
    	->orderBy('ORDER');
    }
}
