<?php

namespace App\Http\Controllers;
use App\Models\PdCodeContractAttribute;

class ProductDeliveryController extends CodeController {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function demurrageebo() {
		$filterGroups = array(	'productionFilterGroup'	=>[2			=>'Storage'],								
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
						);
		return view ( 'front.cargoadmin.demurrageebo',['filters'=>$filterGroups]);
	}
	
	public function cargoentry() {
		$filterGroups = array(	'productionFilterGroup'	=>[],
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
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
	
	public function cargodocuments() {
		$filterGroups = array(	'productionFilterGroup'	=>[2			=>'Storage'],
				'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
						['id'=>'date_end','name'=>'To date']),
		);
		return view ( 'front.cargoadmin.cargodocuments',['filters'=>$filterGroups]);
	}
	
	public function contractdata() {
		$filterGroups = array(	'productionFilterGroup'	=>[2			=>'Storage'],
				'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
						['id'=>'date_end','name'=>'To date']),
		);
		
		$sql="select * from PD_CODE_CONTRACT_ATTRIBUTE order by `ORDER`";
		
		$contractAttributes = PdCodeContractAttribute::all();
		return view ( 'front.contract.contractdata',['filters'=>$filterGroups,
													'contractAttributes'=>$contractAttributes
		]);
	}
}
