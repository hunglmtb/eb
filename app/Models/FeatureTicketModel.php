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
}
