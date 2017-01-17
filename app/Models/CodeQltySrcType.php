<?php

namespace App\Models;
use App\Models\DynamicModel;

class CodeQltySrcType extends DynamicModel
{
	protected $table = 'CODE_QLTY_SRC_TYPE';
	
	/* public function getReferenceTable($code){
		if($code=="PARCEL") return CodeQltySrcType::getTableName();
		return $code;
	} */
}
