<?php

namespace App\Trail;


trait ObjectNameLoad
{
	public function ObjectName($option=null){
		if ($option!=null&&is_array($option)) {
			if(array_key_exists('IntObjectType', $option)){
				$objectType 	= $option['IntObjectType'];
				if ($objectType['id']=="KEYSTORE") {
					if (array_key_exists('ObjectDataSource', $option)) {
						$objectDataSource 	= $option['ObjectDataSource'];
						$mdl 				= $objectDataSource['id'];
						$mdl 				= 'App\Models\\' . $mdl;
					}
					else return null;
				}
				else {
					$mdlName 		= $objectType['name'];
					$mdl 			= \Helper::getModelName ( $mdlName);
				}
			}
			else if ($this->CODE) {
				$mdl			= $this->CODE;
				$mdl 			= 'App\Models\\' . $mdl;
			}
			
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
			
			if ($mdl&&method_exists($mdl, "getEntries")) 
				return $mdl::getEntries($facility_id,$phaseTypeId);
		}
		return null;
	}
}
