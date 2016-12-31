<?php
namespace App\Http\Controllers\Forecast;

use App\Http\Controllers\CodeController;
use App\Models\ConstraintDiagram; 
use Illuminate\Http\Request;

class ChokeController extends CodeController {
    
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$dataSet = ConstraintDiagram::orderBy("NAME")->get();
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
    
    public function summaryData($constraints,$beginDate,$endDate,$postData){
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
    		$constraints	= $this->loadDiagramConfig($constraintId,$postData);
    	}
    	$summaryData	= $this->summaryData($constraints,$beginDate,$endDate,$postData);
    	
    	return response()->json($summaryData);
    }
    
    public function loadDiagramConfig($constraintId,$postData){
    	$constraints	= ConstraintDiagram::find($constraintId);
    	if ($constraints) {
    		$constraints->CONFIG 	= json_decode($constraints->CONFIG,true);
    		$constraints			= $constraints->toArray();
    	}
    	return $constraints;
    }
}
