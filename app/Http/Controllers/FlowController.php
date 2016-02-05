<?php

namespace App\Http\Controllers;

class FlowController extends Controller {
	
	
	public function __construct() {
		$this->middleware ( 'auth' );
	}
	
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function index() {
		return view ( 'front.flow' );
	}
}
