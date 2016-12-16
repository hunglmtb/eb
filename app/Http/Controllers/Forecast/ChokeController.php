<?php
namespace App\Http\Controllers\Forecast;

use App\Http\Controllers\CodeController;
use App\Models\ConstraintDiagram; 
use Illuminate\Http\Request;

class ChokeController extends CodeController {
    
	/* public function __construct() {
		parent::__construct();
	} */
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$dataSet = ConstraintDiagram::orderBy("NAME")->get();
    	$item1 = [
    		"LoProductionUnit"		=>	"12",
    		"LoArea"				=>	"23",
    		"Facility"				=>	"23",
    		"CodeProductType"		=>	"2",
    		"IntObjectType"			=>	"ENERGY_UNIT",
    		"ObjectName"			=>	"382",
    		"ObjectDataSource"		=>	"EnergyUnitDataValue",
    		"ObjectTypeProperty"	=>	"EU_DATA_GRS_ENGY",
    		"CodeFlowPhase"			=>	"2",
    		"CodeAllocType"			=>	"1",
    		"CodePlanType"			=>	"1",
    		"CodeForecastType"		=>	"1"
    	];
    	
    	$item2 = [
    			"LoProductionUnit"		=>			 "9",                     
    			"LoArea"				=>	 "7",                             
    			"Facility"				=>	 "19",                            
    			"CodeProductType"		=>			 "2",                     
    			"IntObjectType"			=>			 "FLOW",                  
    			"ObjectName"			=>		 "396",                       
    			"ObjectDataSource"		=>	"FlowDataForecast",      
    			"ObjectTypeProperty"	=>	"FL_DATA_NET_VOL",   
    			"CodeFlowPhase"			=>	 null,                                 
    			"CodeAllocType"			=>			 "1",                     
    			"CodePlanType"			=>		 "1",                         
    			"CodeForecastType"		=>			 "1"                      
    	];
    	
    	$item3 = [
    			"LoProductionUnit"		=>			 "9",
    			"LoArea"				=>	 "7",
    			"Facility"				=>	 "19",
    			"CodeProductType"		=>			 "2",
    			"IntObjectType"			=>			 "FLOW",
    			"ObjectName"			=>		 "398",
    			"ObjectDataSource"		=>	"FlowDataForecast",
    			"ObjectTypeProperty"	=>	"FL_DATA_NET_VOL",
    			"CodeFlowPhase"			=>	 null,
    			"CodeAllocType"			=>			 "1",
    			"CodePlanType"			=>		 "1",
    			"CodeForecastType"		=>			 "1"
    	];
    	
    	$set1 = ["NAME"		=> "FPSO Constraint Scenario test1",
    			"YCAPTION"	=> "Oil Limit (bbl) test1",
    			"CONFIG"	=> [
			    					["NAME"		=> "max oil 1.1",
    								"GROUP"		=> "Group A",
    								"FACTOR"	=> "0.7",
    								"COLOR"		=> "e6c373",
	    							"OBJECTS"	=> [$item2,$item1,$item3],
    								"DT_RowId"	=> 1,
			    					$dcTable	=> 1,
			    					],
			    					["NAME"		=> "max oil 1.2",
			    					"GROUP"		=> "Group A2",
			    					"FACTOR"	=> "0.5",
    								"COLOR"		=> "c72076",
	    							"OBJECTS"	=> [$item1,$item2,$item3],
    								"DT_RowId"	=> 2,
			    					$dcTable	=> 2,
			    					],
    							],
    			];
    	
    	$set2 = ["NAME"		=> "FPSO Constraint Scenario test2",
    			"YCAPTION"	=> "Oil Limit (bbl) test2",
    			"CONFIG"	=> [
			    					["NAME"		=> "max oil 2.1",
			    					"GROUP"		=> "Group b",
			    					"FACTOR"	=> "0.4",
    								"COLOR"		=> "80c71e",
	    							"OBJECTS"	=> [$item3,$item1,$item2],
	    							"DT_RowId"	=> 1,
			    					$dcTable	=> 1,
			    					],
			    					["NAME"		=> "max oil 2.2",
    								"GROUP"		=> "Group B2",
			    					"FACTOR"	=> "0.5",
    								"COLOR"		=> "2b20c7",
	    							"OBJECTS"	=> [$item1,$item3,$item2],
	    							"DT_RowId"	=> 2,
			    					$dcTable	=> 2,
			    					],
    							],
    	];
     	return ['dataSet'=>[$set1,$set2]];
//     	return ['dataSet'=>$dataSet];
    }
    
    public function filter(Request $request){
    	$postData 		= $request->all();
    	$filterGroups	= \Helper::getCommonGroupFilter();
    	if(isset($filterGroups['dateFilterGroup'])) unset($filterGroups['dateFilterGroup']);
    	return view ( 'choke.editfilter',['filters'			=> $filterGroups,
						    			'prefix'			=> "secondary_",
						    			"currentData"		=> $postData
						    	]);
    }
}
