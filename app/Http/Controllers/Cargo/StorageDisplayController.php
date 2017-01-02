<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Forecast\ChokeController;
use App\Http\Controllers\ProductDeliveryController;
use Illuminate\Http\Request;
use App\Models\PlotViewConfig; 
use App\Models\StorageDisplayChart;

class StorageDisplayController extends ChokeController {
    
	public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
		$dataSet = StorageDisplayChart::orderBy("TITLE")->get();
		return ['dataSet'=>$dataSet];
	}
	
	public function filter(Request $request){
		$postData 		= $request->all();
		$filterGroups	= ProductDeliveryController::storagedisplayFilter();
		return view ( 'front.cargoadmin.editfilter',['filters'			=> $filterGroups,
				'prefix'			=> "secondary_",
				"currentData"		=> $postData
		]);
	}
	
	public function loadDiagramConfig($constraintId,$postData){
		$constraints	= StorageDisplayChart::find($constraintId);
		if ($constraints) {
			$constraints->CONFIG 	= json_decode($constraints->CONFIG,true);
			$constraints			= $constraints->toArray();
		}
		return $constraints;
	}
	
	public function summaryData($constraints,$beginDate,$endDate,$postData){
// 		$midleDate		= $postData["date_mid"];
//     	$midleDate 		= \Helper::parseDate($midleDate);
		$summaryData	= [];
		$sumField		= "V";
		if (count($constraints['CONFIG'])>0){
			$categories	= [];
			$minY 		= 1000000000;
			$summaryLine= [];
			$series		= [];
			foreach($constraints['CONFIG'] as $key => $constraint ){
				$rquery				= null;
				$chartType			= $constraint['CHART_TYPE'];
				$color				= $constraint['COLOR'];
				$negative			= $constraint['NEGATIVE'];
				$minus				= $negative==1?"-":"";
				$plotViewConfigId	= $constraint['PlotViewConfig'];
				
				$plotViewConfig		= PlotViewConfig::find($plotViewConfigId);
				if (!$plotViewConfig) continue;
				
				$objects			= $plotViewConfig->parseViewConfig();
				if (!$objects||count($objects)<=0) continue;
						
				$beginDate			= $constraint['FROM_DATE'];
				$endDate			= $constraint['TO_DATE'];
				/* $timeline			= $plotViewConfig->TIMELINE;
				$date_from			= $beginDate;
				$date_to			= $endDate;
				if($timeline==2){
					$date_from		= $beginDate;
					$date_to		= $midleDate;
				}
				else if($timeline==5){
					$date_from		= $midleDate;
					$date_to		= $endDate;
				} */
				
				$category			= $plotViewConfig->NAME;
				$categories[] 		= $category;
				$serie				= [];
				foreach($objects as $index => $object ){
					$tableView		= $object["TableView"];
					$tableName		= strpos($tableView, "V_")===0?substr($tableView, 2):$tableView;
					$modelName		= \Helper::getModelName($tableName);
					$datefield		= $modelName::$dateField;
					$objectIdField	= $modelName::$idField;
					
					$objectType		= $object["ObjectType"];
					$objectId		= $object["ObjectId"];
					$flowPhase		= $object["FlowPhase"];
					$queryField		= $object["Column"];
					$calculation	= $object["Calculation"];
					
					$is_eutest		= false;
					$is_defer		= false;
					if(substr($tableView, 0, strlen("EU_TEST")) === "EU_TEST"){
						$is_eutest	= true;
					}
					else if(substr($tableView, 0, strlen("DEFERMENT")) === "DEFERMENT"){
						$is_defer	= true;
					}
					
					
					$where			= [$objectIdField	=> $objectId];
					if ($objectType=="ENERGY_UNIT" && !$is_eutest && !$is_defer)  $where['FLOW_PHASE']	= $flowPhase;
					
					$query			= $modelName::where($objectIdField,$objectId)
												->whereDate("$datefield", '>=', $beginDate)
												->whereDate("$datefield", '<=', $endDate)
												->select(\DB::raw("$minus($queryField$calculation) as $sumField"),
														"$datefield as D"
														)
												->take(300);
					if ($index==0) 
						$rquery = $query;
					else 	
						$rquery->union($query);
					
				}
				$value						= 0;
				if ($rquery) {
					$dataSet	= \DB::table( \DB::raw("({$rquery->toSql()}) as sub") )
										->mergeBindings($rquery->getQuery()) // you need to get underlying Query Builder
										->groupBy("D")
										->orderBy("D")
										->selectRaw("sum($sumField) as $sumField, D")
										->get();
				}
				$series[] 	= [
								"type"	=> $chartType,
								"name"	=> $category,
								"color"	=> "#$color",
								"data"	=> $dataSet,
							];
				foreach($dataSet as $index => $data ){
					if (array_key_exists($data->D, $summaryLine))
						$summaryLine[$data->D]->V	+= $data->V;
					else 
						$summaryLine[$data->D]	= (object) array('D' => $data->D,'V' => $data->V);
				}
				
			}
			$summarySeriesData = array_values($summaryLine);
			ksort($summarySeriesData);
			$series[] 	= [
					"type"	=> "line",
					"name"	=> 'Storage Display',
					"color"	=> "#de3ee6",
					"data"	=> $summarySeriesData,
			];
			 
			$title 					= $constraints["TITLE"];
			$bgcolor				= "";
			$ycaption				= "";
			$summaryData["diagram"] = ["bgcolor"		=> $bgcolor,
										"title"			=> $title,
										"categories"	=> $categories,
										"series"		=> $series,
										"ycaption"		=> $ycaption,
										"minY"			=> $minY==1000000000?0:$minY,
			];
		}
		$summaryData["constraints"] 	= $constraints;
		return $summaryData;
	}
}
