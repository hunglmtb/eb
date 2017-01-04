<?php 
namespace App\Models; 

 class FeatureKeystore extends EbBussinessModel { 
	protected static $objectModelName 	= null;
	public static $foreignKeystore 	= null;
	protected $dates 				= ['OCCUR_DATE'];
	
	public static function getEntries($facility_id=null,$product_type = 0){
		$oModel = static::$objectModelName;
		if ($oModel) {
			$oModel 	= 'App\Models\\' . $oModel;
			$wheres 	= [];
			if ($facility_id)	$wheres ['FACILITY_ID']	= $facility_id;
			if ($product_type>0)$wheres ['PRODUCT']		= $product_type;
			$entries = $oModel::where($wheres)->select('ID','NAME')->get();
			return $entries;
		}
		return null;
	}
} 
