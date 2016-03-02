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
		return view ( 'front.flow');
	}
	
	public function eu() {
		return view ( 'front.eu');
	}
	
}
