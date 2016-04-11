<?php

namespace App\Models;
use App\Models\DynamicModel;

class QltyDataDetail extends DynamicModel
{
	protected $table = 'QLTY_DATA_DETAIL';
	protected $primaryKey = 'ID';
	
	
	public function QltyProductElementType()
	{
		return $this->hasOne('App\Models\QltyProductElementType', 'ID', 'ELEMENT_TYPE');
	}
	
}
