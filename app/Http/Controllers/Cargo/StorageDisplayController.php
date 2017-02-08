<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Forecast\ChokeController;
use App\Http\Controllers\ProductDeliveryController;
use App\Models\PlotViewConfig;
use App\Models\StorageDisplayChart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class StorageDisplayController extends ChokeController {
    
	public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
		$dataSet = StorageDisplayChart::orderBy("TITLE")->get();
		/* $dataSet->each(function ($item, $key) {
			if ($item&&$item instanceof Model) {
				$item->CONFIG	= $item->CONFIG;
				if ($item->CONFIG) {
					foreach($item->CONFIG as $index => $objectRow ){
						$objectRow["PlotViewConfig"] = $objectRow["PlotViewConfig"];
					}
				}
			}
		}); */
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
	
    public function summaryData($constraints,$beginDate=null,$endDate=null,$postData=null){
	// 		$midleDate		= $postData["date_mid"];
//     	$midleDate 		= \Helper::parseDate($midleDate);
		$summaryData	= [];
		$sumField		= "V";
		if (count($constraints['CONFIG'])>0){
			$categories	= [];
			$minY 		= null;
			$maxY 		= null;
			$summaryLine= [];
			$series		= [];
			$plotLines	= [];
			foreach($constraints['CONFIG'] as $key => $constraint ){
				$rquery				= null;
				$rLineQuery			= null;
				$chartType			= $constraint['CHART_TYPE'];
				$color				= $constraint['COLOR'];
				$negative			= $constraint['NEGATIVE'];
				$minus				= $negative==1?"-":"";
				$objects			= array_key_exists("OBJECTS", $constraint)?$constraint['OBJECTS']:null;
				$plotViewConfigId	= $constraint['PlotViewConfig'];
				$plotViewConfig		= PlotViewConfig::find($plotViewConfigId);
// 				if (!$plotViewConfig) continue;
				if (!$objects||count($objects)<=0) {
					$objects		= $plotViewConfig->parseViewConfig();
					$category		= $plotViewConfig?$plotViewConfig->NAME:"category";
				}
				else{
					$category		=  array_key_exists("viewName", $constraint)?$constraint['viewName']	:"category";
				}
				if (!$objects||!is_array($objects)||count($objects)<=0) continue;
						
				$beginDate			= array_key_exists("FROM_DATE", $constraint)?$constraint['FROM_DATE']	:$beginDate;
				$endDate			= array_key_exists("TO_DATE", $constraint)	?$constraint['TO_DATE']		:$endDate;
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
				
				$categories[] 		= $category;
				$serie				= [];
				$lineSeries			= [];
				foreach($objects as $index => $object ){
					$tableView		= $object["ObjectDataSource"];
					$tableName		= strpos($tableView, "V_")===0?substr($tableView, 2):$tableView;
					$modelName		= \Helper::getModelName($tableName);
					
					$datefield		= property_exists($modelName, "dateField")?$modelName::$dateField:null;
					$objectIdField	= $modelName::$idField;
					
					$objectType		= $object["IntObjectType"];
					$objectId		= $object["ObjectName"];
					$flowPhase		= array_key_exists("CodeFlowPhase", $object)?$object["CodeFlowPhase"]:0;
					$queryField		= $object["ObjectTypeProperty"];
					$calculation	= array_key_exists("Calculation", $object)?$object["Calculation"]:
									 (array_key_exists("cboOperation", $object)&&array_key_exists("txtConstant", $object)
									 		&&$object["cboOperation"]!=""&&$object["txtConstant"]!=""?
									 			$object["cboOperation"].$object["txtConstant"]:"");
					
					$is_eutest		= false;
					$is_defer		= false;
					if(substr($tableView, 0, strlen("EU_TEST")) === "EU_TEST"){
						$is_eutest	= true;
					}
					else if(substr($tableView, 0, strlen("DEFERMENT")) === "DEFERMENT"){
						$is_defer	= true;
					}
					
					
					$where			= [$objectIdField	=> $objectId];
					if ($objectType=="ENERGY_UNIT" && !$is_eutest && !$is_defer && $flowPhase>0)  $where['FLOW_PHASE']	= $flowPhase;
					
					$lineQuery		= null;
					$query			= null;
					if ($datefield) {
						$query			= $modelName::where($objectIdField,$objectId)
													->whereDate("$datefield", '>=', $beginDate)
													->whereDate("$datefield", '<=', $endDate)
													->select(\DB::raw("$minus($queryField$calculation) as $sumField"),
															"$datefield as D"
															)
													->take(300);
						if ($rquery&&$query) $rquery->union($query);
						else if($query)	$rquery = $query;
					}
					else{
						$lineQuery		= $modelName::where($objectIdField,$objectId)
													->select(\DB::raw("$minus($queryField$calculation) as $sumField"))
													->take(1);
						if ($rLineQuery&&$lineQuery) $rLineQuery->union($lineQuery);
						else if ($lineQuery) $rLineQuery = $lineQuery;
					}
				}
				$value						= 0;
				if ($rquery) {
					$dataSet	= \DB::table( \DB::raw("({$rquery->toSql()}) as sub") )
										->mergeBindings($rquery->getQuery()) // you need to get underlying Query Builder
										->groupBy("D")
										->orderBy("D")
										->selectRaw("sum($sumField) as $sumField, D")
										->get();
					$series[]       = [
							"type"  => $chartType,
							"name"  => $category,
							"color" => "#$color",
							"data"  => $dataSet,
					];
					foreach($dataSet as $index => $data ){
						$dataValue		= $data->V;
						if (array_key_exists($data->D, $summaryLine))
							$summaryLine[$data->D]->V	+= $dataValue;
						else
							$summaryLine[$data->D]	= (object) array('D' => $data->D,'V' => $dataValue);
						if ((!$minY||$minY>$dataValue)&&$dataValue) $minY = $dataValue;
						if ((!$maxY||$maxY<$dataValue)&&$dataValue) $maxY = $dataValue;
					}
				}
				if ($rLineQuery) {
					$rDataSet	= \DB::table( \DB::raw("({$rLineQuery->toSql()}) as sub") )
										->mergeBindings($rLineQuery->getQuery()) // you need to get underlying Query Builder
										->selectRaw("sum($sumField) as $sumField")
										->first();
					if($rDataSet){
						$rValue			= $rDataSet->$sumField;
						$plotLines[] 	= [
										"label"		=> ["text"	=> $category],
										"color"		=> "#$color",
// 										"dashStyle"	=> 'shortdash',
										"value"		=> $rValue,
									];
						if ((!$minY||$minY>$rValue)&&$rValue) $minY 	= $rValue;
						if ((!$maxY||$maxY<$rValue)&&$rValue) $maxY	= $rValue;
					}
				}
			}
			$summarySeriesData = array_values($summaryLine);
// 			ksort($summarySeriesData);
			$series[] 	= [
					"type"	=> "line",
					"name"	=> 'Storage Display',
					"color"	=> "#de3ee6",
					"data"	=> $summarySeriesData,
			];
			ksort($series);
				
			$title 					= $constraints["TITLE"];
			$bgcolor				= "";
			$ycaption				= "";
			$summaryData["diagram"] = ["bgcolor"		=> $bgcolor,
										"title"			=> $title,
										"categories"	=> $categories,
										"series"		=> $series,
										"plotLines"		=> $plotLines,
										"ycaption"		=> $ycaption,
										"minY"			=> $minY,
										"maxY"			=> $maxY,
			];
		}
		$summaryData["constraints"] 	= $constraints;
		return $summaryData;
	}
	
	public function diagram(Request $request){
		$postData 			= $request->all();
		$id					= array_key_exists("id", $postData)?$postData["id"]:0;
		if ($id>0) {
			$constraints	= $this->loadDiagramConfig($id,$postData);
			$summaryData	= $this->summaryData($constraints);
			return view ( 'datavisualization.storage_diagram_alone',['summaryData'=>$summaryData]);
		}
		return response()->json("not available!");
	}
	
}
