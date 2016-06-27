<?php

namespace App\Models;
use App\Models\DynamicModel;

class IntObjectType extends DynamicModel
{
	protected $table = 'INT_OBJECT_TYPE';
	protected $primaryKey = 'ID';
	
	public static function getAll(){
// 		$entries = static ::select('ID as XID','CODE as ID','CODE','NAME')->orderBy('XID')->get();
		$entries = static ::all();
		return $entries;
	}
	
	
	public function ObjectName($option=null)
	{
		if ($option!=null&&is_array($option)) {
			$objectType = $option['IntObjectType'];
			$mdlName = $objectType['name'];
			$tableName = strtolower ( $mdlName );
			$mdlName = \Helper::camelize ( $tableName, '_' );
			$mdl = 'App\Models\\' . $mdlName;
			$facility = $option['Facility'];
			$facility_id = $facility['id'];
			return $mdl::getEntries($facility_id);
		}
		return null;
	}
}
