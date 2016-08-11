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
		
		$contractAttributes = PdCodeContractAttribute::all();
		return view ( 'front.contract.contractdata',['filters'=>$filterGroups,
													'contractAttributes'=>$contractAttributes
		]);
	}
	
	public function contractcalculate() {
		$filterGroups = array(
						'frequenceFilterGroup'		=> array([	'id'			=>'PdContract',
																'name'			=>'PdContract',
																'filterName'	=>'Contract',
																'getMethod'		=>'getByDateRange',
																'source'		=>['dateFilterGroup'=>['date_begin','date_end']]
						]),
						'dateFilterGroup'			=> array('date_begin'	=> [	'id'			=>'date_begin',
																					'name'			=>'From date',
																					'dependences'	=>['PdContract'],
																					'extra'			=>['date_end','PdContract']
																				],
															'date_end'		=> [	'id'			=>'date_end',
																					'name'			=>'To date',
																					'dependences'	=>['PdContract'],
																					'extra'			=>['date_begin','PdContract']
																				],
						),
						'enableButton'	=>false
		);
		return view ( 'front.contract.contractcalculate',['filters'=>$filterGroups,
		]);
	}
}
