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
    	
    	$set1 = ["ID"		=> 2,
    			"NAME"		=> "FPSO Constraint Scenario test1",
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
    	
    	$set2 = ["ID"		=> 7,
    			"NAME"		=> "FPSO Constraint Scenario test2",
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
//      	return ['dataSet'=>[$set1,$set2]];
     	return ['dataSet'=>$dataSet];
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
    
    public function summaryData($constraints,$beginDate,$endDate){
    	$summaryData	= [];
    	$sumField		= "V";
    	if (count($constraints['CONFIG'])>0){
    		$categories	= [];
    		$groups 	= [];
    		$minY 		= 1000000000;
    		$series		= [];
    		foreach($constraints['CONFIG'] as $key => $constraint ){
    			$rquery				= null;
    			$factor				= $constraint['FACTOR'];
    			$factor				= $factor&&$factor!=''?$factor:0;
    			$category			= $constraint['NAME'];
    			$group				= $constraint['GROUP'];
    			$groups[]			= $group;
    	
    			$categories[] 		= $category;
    			$serie				= [];
    			if (!array_key_exists('OBJECTS', $constraint)) continue;
    			foreach($constraint['OBJECTS'] as $index => $object ){
    				$modelName		= 'App\Models\\' .$object["ObjectDataSource"];
    				$datefield		= $modelName::$dateField;
    				$objectIdField	= $modelName::$idField;
    				$objectId		= $object["ObjectName"];
    				$operation		= $object["cboOperation"];
    				$operationValue	= $object["txtConstant"];
    				$queryField		= $object["ObjectTypeProperty"];
    				$queryField		= $operation&&$operation!=''&&$operationValue&&$operationValue!=""&&$operationValue!=0?
    				"$queryField$operation$operationValue":
    				$queryField;
    				$query			= $modelName::where($objectIdField,$objectId)
    				->whereDate("$datefield", '>=', $beginDate)
    				->whereDate("$datefield", '<=', $endDate)
    				->select(\DB::raw("$queryField as $sumField"));
    				if ($index==0) $rquery = $query;
    				else 	$rquery->union($query);
    			}
    			$value								= 0;
    			if ($rquery) {
    				$dataSet						= $rquery->get();
    				$value							= $dataSet->sum($sumField);
    				$ycaptionValue					= $value*$factor;
    				$minY							= ($ycaptionValue < $minY && $ycaptionValue>0)?$ycaptionValue:$minY;
    				$constraint['VALUE']			= $value;
    				$constraint['YCAPTION']			= $ycaptionValue;
    				$constraints['CONFIG'][$key]	= $constraint;
    			}
    			if(!array_key_exists($group,$series)) $series[$group] = [];
    			$series[$group][$category] 			= [
    					"name"	=> $category,
    					"data"	=> [$ycaptionValue],
    					"color"	=> "#".$constraint['COLOR'],
    			];
    		}
    	
    		$groups		= array_unique($groups);
    		$groups		= array_values($groups);
    	
    		$title 						= $constraints["NAME"];
    		$ycaption 					= $constraints["YCAPTION"];
    		if(!$ycaption) 	$ycaption	= "Oil Limit";
    		if(!$title) 	$ycaption	= "Limit Diagram";
    		$bgcolor					= "";
    		$summaryData["diagram"] = ["bgcolor"		=> $bgcolor,
    				"title"			=> $title,
    				"groups"		=> $groups,
    				"categories"	=> $categories,
    				"series"		=> $series,
    				"ycaption"		=> $ycaption,
    				"minY"			=> $minY==1000000000?0:$minY,
    		];
    	}
    	$summaryData["constraints"] 	= $constraints;
    	return $summaryData;
    }
    public function summary(Request $request){
    	$postData 		= $request->all();
    	$beginDate 		= \Helper::parseDate($postData['date_begin']);
    	$endDate 		= \Helper::parseDate($postData['date_end']);
    	if(isset($postData['constraints']))
	    	$constraints	= $postData['constraints'];
    	else{
    		$constraintId	= $postData['constraintId'];
    		$constraints	= ConstraintDiagram::find($constraintId);
    		if ($constraints) {
    			$constraints->CONFIG 	= json_decode($constraints->CONFIG,true);
     			$constraints			= $constraints->toArray();
    		}
    	}
    	$summaryData	= $this->summaryData($constraints,$beginDate,$endDate);
    	
    	return response()->json($summaryData);
    }
    
    public function diagram(Request $request){
    	$beginDate 		= \Helper::parseDate($postData['date_begin']);
    	$endDate 		= \Helper::parseDate($postData['date_end']);
    	$constraints	= $postData['constraints'];
    	$summaryData	= $this->summaryData($constraints,$beginDate,$endDate);
    	
    	return response()->json($summaryData);
    }
}
