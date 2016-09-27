<?php

namespace App\Models;
use App\Models\FeatureTankModel;
use App\Models\PdLiftingAccount;


class Storage extends FeatureTankModel
{
	protected $table = 'STORAGE';
	
	public function PdLiftingAccount($option=null){
		return PdLiftingAccount::where("STORAGE_ID",$this->ID)->get();
	}
	
}
