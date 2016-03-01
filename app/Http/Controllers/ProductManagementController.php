<?php

namespace App\Http\Controllers;

class ProductManagementController extends Controller {
	 
	protected $subMenus = [array('title' => 'FLOW STREAM', 'link' => 'flow'),
			array('title' => 'ENERGY UNIT', 'link' => 'eu'),
			array('title' => 'STORAGE', 'link' => 'storage'),
			array('title' => 'TICKET', 'link' => 'ticket'),
			array('title' => 'WELL TEST', 'link' => 'eutest'),
			array('title' => 'DEFERMENT', 'link' => 'deferment'),
			array('title' => 'QUALITY', 'link' => 'quality')
	];
	
	protected $subMenus2 = [array('title' => 'FLOW STREAM', 'link' => 'flow'),
			array('title' => 'ENERGY UNIT', 'link' => 'eu'),
			array('title' => 'STORAGE', 'link' => 'storage'),
			array('title' => 'QUALITY', 'link' => 'quality')
	];
	
	public function __construct() {
		$this->middleware ( 'auth' );
	}
	
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function flow() {
		return view ( 'front.flow' ,['subMenus' => $this->subMenus,
									 'currentSubMenu' => 'flow'
									]);
	}
	
	public function eu() {
		return view ( 'front.eu',['subMenus' => $this->subMenus2,
									 'currentSubMenu' => 'flow'
									]);
	}
	
}
