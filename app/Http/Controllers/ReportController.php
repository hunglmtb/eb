<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FoGroup;

class ReportController extends Controller {
	
	public function __construct() {
		$this->isReservedName = config ( 'database.default' ) === 'oracle';
		$this->middleware ( 'auth' );
	}
	
	public function _index() {
		$facility = Facility::whereIn('ID', [18,19])->get ( [ 
				'ID',
				'NAME' 
		] );
		
		$fogroup = FoGroup::get ( [
				'ID',
				'NAME'
		] );
		
		return view ( 'front.flowreport', ['facility' => $facility, 'fogroup'=>$fogroup] );
	}
}