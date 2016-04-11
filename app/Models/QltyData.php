<?php

namespace App\Models;
use App\Models\DynamicModel;

class QltyData extends DynamicModel
{
	protected $table = 'QLTY_DATA';
	protected $primaryKey = 'ID';
	protected $fillable  = ['CODE',
							'LAB_NAME',
							'NAME',
							'SAMPLE_DATE',
							'TEST_DATE',
							'SAMPLE_TAKER_NAME',
							'LAB_TECHNICIAN_NAME',
							'SRC_TYPE',
							'SRC_ID',
							'PRODUCT_TYPE',
							'EFFECTIVE_DATE',
							'QLTY_VALUE1',
							'QLTY_VALUE2',
							'QLTY_VALUE3',
							'QLTY_VALUE4',
							'QLTY_VALUE5',
							'ENGY_RATE'];
	
	
	public function CodeQltySrcType()
	{
		return $this->belongsTo('App\Models\CodeQltySrcType', 'SRC_TYPE', 'ID');
	}
}
