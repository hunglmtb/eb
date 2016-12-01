<?php
namespace App\Http\Controllers\Config;

use App\Http\Controllers\CodeController;
use App\Services\lazy_mofo;

class TableDataController extends CodeController {
    
    public function edittable(){
//     	public function edittable($tablename,$action){

    	/* include('lazy_mofo.php');
    	
    	$db_host = 'localhost';
    	$db_name = 'energy_builder_from_server';
    	$db_user = 'root';
    	$db_pass = '';
    	
    	// connect with pdo
    	try {
    		$dbh = new PDO("mysql:host=$db_host;dbname=$db_name;", $db_user, $db_pass);
    	}
    	catch(PDOException $e) {
    		die('pdo connection error: ' . $e->getMessage());
    	} */
    	
    	$tablename 	=  \Input::get('table');
    	$action 	=  \Input::get('action');
    	
    	$dbh = \DB::connection()->getPdo();
    	// create LM object, pass in PDO connection
    	$lm = new lazy_mofo($dbh);
    	$lm->setModelName($tablename);
    	
    	return view ( 'tableData.edittable',['tablename'=>$tablename,
							    			'action'=>$action,
							    			'lm'	=>$lm
    	]);
    }
}
