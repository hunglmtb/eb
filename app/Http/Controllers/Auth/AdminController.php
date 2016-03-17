<?php
namespace App\Http\Controllers;
 
class AdminController extends Controller {
	 
	public function __construct() {
		$this->middleware ( 'auth' );
	}
	
	
	public function index(){
		
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date'],
				)
		);
		
		return view ( 'admin.users',['filters'=>$filterGroups]);
	}
}