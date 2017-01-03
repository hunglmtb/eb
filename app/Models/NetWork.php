<?php

namespace App\Models;
use App\Models\DynamicModel;

class NetWork extends DynamicModel
{
	protected $table = 'NETWORK';
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = ['ID', 'CODE', 'NAME', 'START_DATE', 'END_DATE', 'NETWORK_TYPE', 'XML_CODE', 'DATA_CONNECTION'];
	
}
