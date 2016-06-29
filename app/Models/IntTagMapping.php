<?php

namespace App\Models;
use App\Models\EbBussinessModel;

class IntTagMapping extends EbBussinessModel
{
	protected $table = 'INT_TAG_MAPPING';
	protected $primaryKey = 'ID';
	protected $fillable  = ['TAG_ID', 
							'SYSTEM_ID', 
							'FREQUENCY', 
							'ALLOW_OVERRIDE', 
							'OBJECT_TYPE', 
							'OBJECT_ID', 
							'BEGIN_DATE', 
							'END_DATE', 
							'EVENT_TYPE', 
							'FLOW_PHASE', 
							'TABLE_NAME', 
							'COLUMN_NAME'];
	
	public  static  $idField = 'ID';
}
