<?php

namespace App\Models;
use App\Models\EbBussinessModel;

class FeatureTicketModel extends EbBussinessModel
{
	public  static  $idField = 'ID';
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		$attributes = parent:: getKeyColumns($newData,$occur_date,$postData);
		if ( array_key_exists ( 'isAdding', $newData ) && array_key_exists ( 'Tank', $postData )) {
			$newData['TANK_ID'] = $postData['Tank'];
		}
		return $attributes;
	}
	
	/* public function setOccurDateAttribute($occur_date)
	{
		$occur_date = Carbon::parse($occur_date);
		$occur_date->hour = 0;
		$occur_date->minute = 0;
		$occur_date->second = 0;
		$this->attributes['OCCUR_DATE'] = $occur_date;
	} */
}
