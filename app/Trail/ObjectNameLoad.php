<?php

namespace App\Trail;


trait ObjectNameLoad
{
	public function ObjectName($option=null)
	{
		if ($option!=null&&is_array($option)) {
			$objectType 	= $option['IntObjectType'];
			if ( array_key_exists('Facility', $option)) {
				$facility 		= $option['Facility'];
				$facility_id 	= $facility['id'];
			}
			else $facility_id 	= $this->ID;
			
			if ( array_key_exists('ExtensionPhaseType', $option)) {
				$phaseType 		= $option['ExtensionPhaseType'];
				$phaseTypeId 	= $phaseType['id'];
			}
			else if ( array_key_exists('CodeProductType', $option)) {
				$phaseType 		= $option['CodeProductType'];
				$phaseTypeId 	= $phaseType['id'];
			}
			else $phaseTypeId 	= 0;
			
			$mdlName 		= $objectType['name'];
			$mdl 			= \Helper::getModelName ( $mdlName);
			return $mdl::getEntries($facility_id,$phaseTypeId);
		}
		return null;
	}
}
