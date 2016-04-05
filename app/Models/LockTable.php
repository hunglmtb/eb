<?php

namespace App\Models;
use App\Models\DynamicModel;

class LockTable extends DynamicModel
{
	protected $table = 'lock_table';
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = ['ID', 'TABLE_NAME', 'LOCK_DATE', 'USER_ID', 'FACILITY_ID'];
	
}
