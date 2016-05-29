<?php

namespace App\Models;
use App\Models\DynamicModel;

class QltyDataDetail extends DynamicModel
{
	protected $table = 'QLTY_DATA_DETAIL';
	protected $primaryKey = 'ID';
	protected $fillable  = ['QLTY_DATA_ID',
							 'ELEMENT_TYPE',
							 'VALUE',
							 'UOM',
							 'GAMMA_C7',
							 'MOLE_FACTION',
							 'MASS_FRACTION',
							 'NORMALIZATION',
							 'MOLE_FACTION2',
							 'MASS_FRACTION2'];
	
	
	
	public function QltyProductElementType(){
		return $this->hasOne('App\Models\QltyProductElementType','ID','ELEMENT_TYPE');
	}
	
}
