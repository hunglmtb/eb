<?php
namespace App\Http\Controllers\Forecast;

use App\Http\Controllers\CodeController;
use App\Models\ConstraintDiagram; 

class ChokeController extends CodeController {
    
	/* public function __construct() {
		parent::__construct();
	} */
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	
    	/* $sSQL="select * from constraint_diagram order by NAME";
    	$re=mysql_query($sSQL) or die (mysql_error());
    	$sRole="";
    	while($row=mysql_fetch_array($re))
    	{
    		echo "<span class='plotItem' id='plotItem_$row[ID]' ycaption='$row[YCAPTION]' cons_id='$row[ID]' style='display:block;line-height:20px;margin:2px;'><a id='a_$row[ID]' href='javascript:openCons($row[ID])'>$row[NAME]</a> <img valign='middle' onclick='deleteCons($row[ID])' class='xclose' src='../img/x.png'></span>";
    	}
    	
    	$date_end 		= $postData['date_end'];
    	$date_end 		= \Helper::parseDate($date_end);
    	
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName"; */
    	
//     	\DB::enableQueryLog();
    	$dataSet = ConstraintDiagram::orderBy("NAME")->get();
//  		\Log::info(\DB::getQueryLog());
 		
    	$set1 = ["NAME"		=> "FPSO Constraint Scenario test1",
    			"YCAPTION"	=> "Oil Limit (bbl) test1",
    			"CONFIG"	=> [
			    					["NAME"		=> "max oil 1.1",
    								"GROUP"		=> "Group A",
    								"FACTOR"	=> "0.7",
    								"OBJECTS"	=> [],
    								],
			    					["NAME"		=> "max oil 1.2",
			    					"GROUP"		=> "Group A2",
			    					"FACTOR"	=> "0.5",
			    					"OBJECTS"	=> [],
			    					],
    							]
    			];
    	
    	$set2 = ["NAME"		=> "FPSO Constraint Scenario test2",
    			"YCAPTION"	=> "Oil Limit (bbl) test2",
    			"CONFIG"	=> [
			    					["NAME"		=> "max oil 2.1",
			    					"GROUP"		=> "Group b",
			    					"FACTOR"	=> "0.5",
			    					"OBJECTS"	=> [],
			    					],
			    					["NAME"		=> "max oil 2.2",
    								"GROUP"		=> "Group B2",
			    					"FACTOR"	=> "0.5",
			    					"OBJECTS"	=> [],
			    					],
    							]
    			];
    	return ['dataSet'=>[$set1,$set2]];
    }
}
