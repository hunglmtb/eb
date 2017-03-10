<?php

namespace App\Models;
use App\Models\EbBussinessModel;

class FeatureTicketModel extends EbBussinessModel
{
	public  static  $idField = 'TANK_ID';
	public  static  $dateField = 'OCCUR_DATE';
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		$attributes = parent:: getKeyColumns($newData,$occur_date,$postData);
		if ( array_key_exists ( 'isAdding', $newData ) && array_key_exists ( 'Tank', $postData )) {
			$newData['TANK_ID'] = $postData['Tank'];
		}
		return $attributes;
	}
	
	public static function getObjects() {
		return Tank::where("ID",">",0)->orderBy("NAME")->get();
	}
	
	/* public static function findManyWithConfig($updatedIds) {
		$table	= static::getTableName();
		$tank 	= Tank::getTableName();
		return parent::join("Tank","$tank.ID","=","$table.TARGET_TANK")->whereIn ("$table.ID", $updatedIds )->select("$table.*","$tank.NAME as TARGET_TANK")->get();
	} */
}
