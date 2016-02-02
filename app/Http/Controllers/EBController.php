<?php

namespace App\Http\Controllers;

use App\Jobs\ChangeLocale;

class EBController extends Controller {
	
	/**
	 * Instantiate a new UserController instance.
	 *
	 * @return void
	 */
	public function __construct() {
// 		$this->middleware('ajax');
		// 		$this->middleware ( 'eb.auth' );
		
		/* $this->middleware ( 'log', [ 
				'only' => [ 
						'fooAction',
						'barAction' 
				] 
		] );
		
		$this->middleware ( 'subscribed', [ 
				'except' => [ 
						'fooAction',
						'barAction' 
				] 
		] ); */
	}
	/**
	 * Change language.
	 *
	 * @param App\Jobs\ChangeLocaleCommand $changeLocale        	
	 * @param String $lang        	
	 * @return Response
	 */
	public function language($lang, ChangeLocale $changeLocale) {
		$lang = in_array ( $lang, config ( 'app.languages' ) ) ? $lang : config ( 'app.fallback_locale' );
		$changeLocale->lang = $lang;
		$this->dispatch ( $changeLocale );
		
		return redirect ()->back ();
	}
}
