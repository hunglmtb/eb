<?php

namespace App\Http\Controllers;

use App\Jobs\ChangeLocale;

class EBController extends Controller {
	
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
