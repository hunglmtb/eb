<?php

namespace App\Models;
use App\Models\DynamicModel;

class ExtensionDataSource extends DynamicModel
{
	public static function all($columns = array())
	{
		return  collect([
							(object)['ID' =>	'VALUE'	,'NAME' => 'Standard'      	],
							(object)['ID' =>	'THEOR'	,'NAME' => 'Theoretical'    ],
							(object)['ID' =>	'ALLOC'	,'NAME' => 'GAllocation'    ],
						]);
	}
}
