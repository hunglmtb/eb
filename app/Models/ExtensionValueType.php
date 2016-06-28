<?php

namespace App\Models;
use App\Models\DynamicModel;
// use Illuminate\Support\Collection;

class ExtensionValueType extends DynamicModel
{
	public static function all($columns = array())
	{
		return  collect([
							(object)['ID' =>	'GRS_VOL'	,'NAME' => 'Gross Volume'    	],
							(object)['ID' =>	'NET_VOL'	,'NAME' => 'Net Volume'      	],
							(object)['ID' =>	'GRS_MASS'	,'NAME' => 'Gross Mass'      	],
							(object)['ID' =>	'NET_MASS'	,'NAME' => 'Net Mass'        	],
							(object)['ID' =>	'GRS_ENGY'	,'NAME' => 'Gross Energy'    	],
							(object)['ID' =>	'GRS_PWR'	,'NAME' => 'Gross Power'     	],
						]);
	}
}
