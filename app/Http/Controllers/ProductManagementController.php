<?php

namespace App\Http\Controllers;

class ProductManagementController extends EBController {
	 
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function flow() {
		$filterGroups = array('productionFilterGroup'=> ['Tank'],
							  'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date'],
													)
		);
		/* $filterGroups = array('productionFilterGroup'=> ['model'=>'tank','name'=>'Tank'],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date'],
				)
		); */
		
		return view ( 'front.flow',['filters'=>$filterGroups]);
	}
	
	public function eu() {
		$filterGroups = array('productionFilterGroup'=>['EnergyUnitGroup'],
							 'frequenceFilterGroup'=> ['CodeReadingFrequency','CodeFlowPhase']
						);
		return view ( 'front.eu',['filters'=>$filterGroups]);
	}
	
	public function quality() {
		$filterGroups = array('productionFilterGroup'=> ['model'=>'tank','name'=>'Tank'],
				'dateFilterGroup'=> array(['id'=>'cboFilterBy','name'=>'Filter by'],
						['id'=>'date_begin','name'=>'From Date'],
						['id'=>'date_end','name'=>'To Date'],
				)
		);
		return view ( 'front.flow',['filters'=>$filterGroups]);
	}
	
}
