<?php

namespace App\Http\Controllers;

class EnergyUnitController extends Controller {
	
	
	public function __construct() {
		$this->middleware ( 'auth' );
	}
	
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function index() {
		return view ( 'front.eu' );
	}
}
