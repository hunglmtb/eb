<?php

namespace App\Http\Controllers;


class ProductManagementController extends EBController {
	 
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function flow() {
		$filterGroups = array('productionFilterGroup'	=> [],
							  'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'Date']),
							 'frequenceFilterGroup'		=> ['CodeReadingFrequency','CodeFlowPhase']
						);
		
		return view ( 'front.flow',['filters'=>$filterGroups]);
	}
	
	public function eu() {
		$filterGroups = array('productionFilterGroup'	=>['EnergyUnitGroup'],
							  'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'Date']),
							'frequenceFilterGroup'		=> ['CodeReadingFrequency','CodeFlowPhase','CodeEventType','CodeAllocType']
						);
		return view ( 'front.eu',['filters'=>$filterGroups]);
	}
	
	public function storage() {
		$filterGroups = array('productionFilterGroup'	=> [],
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'Date']),
								'frequenceFilterGroup'	=> ['CodeProductType']
						);
		return view ( 'front.storage',['filters'=>$filterGroups]);
	}
	
	
	public function eutest() {
		$filterGroups = array('productionFilterGroup'	=>['EnergyUnit'],
							'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'Effective Date'],
																['id'=>'date_end','name'=>'To']),
						);
		return view ( 'front.eutest',['filters'=>$filterGroups]);
	}
	
	public function quality() {
		$filterGroups = array('productionFilterGroup'	=> [],
								'frequenceFilterGroup'=> ['CodeQltySrcType'],
								'dateFilterGroup'=> array(
				 						['id'=>'cboFilterBy','name'=>'Filter by'],
										['id'=>'date_begin','name'=>'From Date'],
										['id'=>'date_end','name'=>'To Date'],
						)
		);
		return view ( 'front.quality',['filters'=>$filterGroups]);
	}
	
	public function deferment() {
		$filterGroups = array('productionFilterGroup'	=> [['name'=>'IntObjectType',
                'independent'=>true,'default'  =>['ID'=>0,'NAME'=>'All']]],
				'dateFilterGroup'=> array(
						['id'=>'date_begin','name'=>'From Date'],
						['id'=>'date_end','name'=>'To Date'],
				)
		);
		return view ( 'front.deferment',['filters'=>$filterGroups]);
	}
	
	public function ticket() {
		$filterGroups = array('productionFilterGroup'	=> ['Tank'],
				'dateFilterGroup'=> array(
						['id'=>'date_begin','name'=>'From Date'],
						['id'=>'date_end','name'=>'To Date'],
				)
		);
		return view ( 'front.ticket',['filters'=>$filterGroups]);
	}
	
}
