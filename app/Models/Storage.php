<?php

namespace App\Models;
use App\Models\PdLiftingAccount;

class Storage extends FeatureStorageModel
{
	protected $table = 'STORAGE';
	public  static  $idField = 'ID';
	public  static  $dateField = null;
	
	public function PdLiftingAccount($option=null){
		return PdLiftingAccount::where("STORAGE_ID",$this->ID)->get();
	}
	
	public function Tank(){
		return $this->hasMany('App\Models\Tank', 'STORAGE_ID', 'ID');
	}
}
