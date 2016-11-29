<?php 
namespace App\Models; 
use App\Models\DynamicModel; 
use App\Models\EnergyUnit; 

 class ObjectName extends DynamicModel { 
 	protected $primaryKey = 'ID2';
 	
	public static function loadBy($sourceData){
		if ($sourceData!=null&&is_array($sourceData)) {
			$facility 			= $sourceData['Facility'];
			$facility_id 		= $facility->ID;
			if (array_key_exists('CodeProductType', $sourceData)) {
				$phaseType 		= $sourceData['CodeProductType'];
				$phaseTypeId 	= $phaseType->ID;
			}
			else $phaseTypeId 	= 0;
			
			$objectType 		= $sourceData['IntObjectType'];
			$mdlName 			= $objectType->CODE;
			$mdl 				= \Helper::getModelName ( $mdlName);
			return $mdl::getEntries($facility_id,$phaseTypeId);
		}
		return null;
	}
	
	public static function find($id){
		return EnergyUnit::find($id);
	}
} 
