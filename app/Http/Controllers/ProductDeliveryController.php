<?php

namespace App\Http\Controllers;

class ProductDeliveryController extends CodeController {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function demurrageebo() {
		$filterGroups = array(	'productionFilterGroup'	=>[2			=>'Storage'],								
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
						);
		return view ( 'front.demurrageebo',['filters'=>$filterGroups]);
	}
	
	public function cargoentry() {
		$filterGroups = array(	'productionFilterGroup'	=>[],
		);
		return view ( 'front.cargoadmin.cargoentry',['filters'=>$filterGroups]);
	}
	
	public function cargonomination() {
		$filterGroups = array(	'productionFilterGroup'	=>[2			=>'Storage'],
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
						);
		return view ( 'front.cargoadmin.cargonomination',['filters'=>$filterGroups]);
	}
	
	public function cargoschedule() {
		$filterGroups = array(	'productionFilterGroup'	=>[2=>'Storage'],
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																		['id'=>'date_end','name'=>'To date']),
		);
		return view ( 'front.cargoadmin.cargoschedule',['filters'=>$filterGroups]);
	}
	
	
	public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
		 
		$mdlName = $postData[config("constants.tabTable")];
		$mdl = "App\Models\\$mdlName";
		$date_end = $postData['date_end'];
		//     	$date_end = Carbon::parse($date_end);
		$date_end = \Helper::parseDate($date_end);
		 
		return ['dataSet'=>null				
		];
	}
	
	public function getFirstProperty($dcTable){
		return  null;
	}
}
