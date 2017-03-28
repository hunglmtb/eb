<?php

namespace App\Models;
use App\Models\DynamicModel;

class NetWork extends DynamicModel
{
	protected $table = 'NETWORK';
	
	public $timestamps = false;
	
	protected $fillable  = ['ID', 'CODE', 'NAME', 'START_DATE', 'END_DATE', 'NETWORK_TYPE', 'XML_CODE', 'DATA_CONNECTION'];
	
	
	public function AllocJob($option=null){
		return AllocJob::where("NETWORK_ID","=",$this->ID)->select("ID","NAME")->get();
	}
	
}
