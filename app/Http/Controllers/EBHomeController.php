<?php

namespace App\Http\Controllers;



class EBHomeController extends EBController
{

	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('front.ebindex');
	}

}
