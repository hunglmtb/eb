<?php

namespace App\Models;
use App\Models\PdLiftingAccount;

class Storage extends FeatureStorageModel
{
	protected $table = 'STORAGE';
	
	public function PdLiftingAccount($option=null){
		return PdLiftingAccount::where("STORAGE_ID",$this->ID)->get();
	}
	
}
