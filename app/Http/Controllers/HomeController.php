<?php

namespace App\Http\Controllers;

use App\Jobs\ChangeLocale;

class HomeController extends Controller
{

	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function index($menu = '')
	{
		return view ( 'front.ebindex',['menu'=>$menu]);
	}

	/**
	 * Change language.
	 *
	 * @param  App\Jobs\ChangeLocaleCommand $changeLocale
	 * @param  String $lang
	 * @return Response
	 */
	public function language( $lang,
		ChangeLocale $changeLocale)
	{		
		$lang = in_array($lang, config('app.languages')) ? $lang : config('app.fallback_locale');
		$changeLocale->lang = $lang;
		$this->dispatch($changeLocale);

		$au = auth();
		$un  = $au->user();
		if ($un) {
			$un->language	= $lang;
			$un->save();
		}
		return redirect()->back();
	}

	public function loginSuccess()
	{
		$au = auth();
		$un  = $au->user();
		return view('front.logginned');
	}
}
