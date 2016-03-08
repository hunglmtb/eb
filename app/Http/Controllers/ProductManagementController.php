<?php

namespace App\Http\Controllers;

class ProductManagementController extends Controller {
	 
	public function __construct() {
		$this->middleware ( 'auth' );
	}
	
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function flow() {
		$filterGroups = array('productionFilterGroup'=> ['model'=>'tank','name'=>'Tank'],
							  'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date'],
													)
		);
		return view ( 'front.flow',['filterGroups'=>$filterGroups]);
	}
	
	public function eu() {
		$filterGroups = array('productionFilterGroup'=> array('model'=>'energyUnitGroup',
															'name'=>'Energy Unit Group',
															'default'=> array('value'=>'',
																			'name'=>'No Group'
															)),
							 'frequenceFilterGroup'=> [array('default'=>['value'=>'','name'=>'All'],
															'model'=>'App\Models\CodeReadingFrequency',
															'filteName'=>'Record Frequency'),
													 array('default'=>['value'=>'','name'=>'All'],
															'model'=>'App\Models\CodeFlowPhase',
															'filteName'=>'Phase Type')]
		);
		return view ( 'front.eu',['filterGroups'=>$filterGroups]);
	}
	
	public function quality() {
		$filterGroups = array('productionFilterGroup'=> ['model'=>'tank','name'=>'Tank'],
				'dateFilterGroup'=> array(['id'=>'cboFilterBy','name'=>'Filter by'],
						['id'=>'date_begin','name'=>'From Date'],
						['id'=>'date_end','name'=>'To Date'],
				)
		);
		return view ( 'front.flow',['filterGroups'=>$filterGroups]);
	}
	
}
