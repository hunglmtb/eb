<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Forecast\ChokeController;
use App\Http\Controllers\ProductDeliveryController;
use App\Models\DynamicModel;
use App\Models\IntObjectType;
use App\Models\PlotViewConfig;
use App\Models\StorageDisplayChart;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
	
    public function summaryData($constraints,$beginDate=null,$endDate=null,$postData=null){
	// 		$midleDate		= $postData["date_mid"];
//     	$midleDate 		= \Helper::parseDate($midleDate);
		$summaryData	= [];
		$sumField		= "V";
		$Configuration	= auth()->user()->getConfiguration();
		$dateFormat		= $Configuration["time"]["DATE_FORMAT_UTC"];
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
				$categories[] 		= $category;
				$serie				= [];
				$lineSeries			= [];
				$objectNames		= [];
				foreach($objects as $index => $object ){
					$objectId		= $object["ObjectName"];
					$queryField		= $object["ObjectTypeProperty"];
					$objectType		= $object["IntObjectType"];
					$flowPhase		= array_key_exists("CodeFlowPhase", $object)?$object["CodeFlowPhase"]:0;
					$calculation	= array_key_exists("Calculation", $object)?$object["Calculation"]:
									 (array_key_exists("cboOperation", $object)&&array_key_exists("txtConstant", $object)
									 		&&$object["cboOperation"]!=""&&$object["txtConstant"]!=""?
									 			$object["cboOperation"].$object["txtConstant"]:"");
					$tableView		= $object["ObjectDataSource"];
					$tableName		= $tableView;
					$extraSelect	= \DB::raw("-1 as E");
					if (strpos($tableView, "V_")===0) {
// 						$modelName		= \DB::table($tableName);
						$modelName		= new DynamicModel;
						$modelName->setTable($tableView);
						$datefield		= "OCCUR_DATE";
						$objectIdField	= $objectType."_ID";
						$extraSelect	= \Helper::getExtraSelects($tableView,$objectType,$objectId,$extraSelect);
					}
					else{
						$modelName		= \Helper::getModelName($tableName);
						$datefield		= property_exists($modelName, "dateField")?$modelName::$dateField:null;
						$objectIdField	= $modelName::getObjectTypeCode($objectType);
						$objectTypeModel= \Helper::getModelName($objectType);
						$objectName 	= $objectTypeModel::find($objectId);
						if ($objectName) {
							$objectName 	= $objectName->NAME;
// 							$objectNames[]	= "$objectName($tableName.$queryField)$calculation";
							$objectNames[]	= $objectName;
						}
					}
					
					$is_eutest		= false;
					$is_defer		= false;
					if(substr($tableView, 0, strlen("EU_TEST")) === "EU_TEST"){
						$is_eutest	= true;
					}
					else if(substr($tableView, 0, strlen("DEFERMENT")) === "DEFERMENT"){
						$is_defer	= true;
					}
					
					$where			= [$objectIdField	=> $objectId];
					if(method_exists($modelName,"addExtraQueryCondition")) $modelName::addExtraQueryCondition($where,$object,$objectType);
					
					if ($objectType=="ENERGY_UNIT" && !$is_eutest && !$is_defer && $flowPhase>0)  $where['FLOW_PHASE']	= $flowPhase;
					
					$lineQuery		= null;
					$query			= null;
					if ($datefield) {
						if (is_string($modelName))
							$query			= $modelName::where($objectIdField,$objectId);
						else
							$query			= $modelName->where($objectIdField,$objectId);
						
						$query				= $query->whereDate("$datefield", '>=', $beginDate)
													->whereDate("$datefield", '<=', $endDate)
													->select(\DB::raw("$minus($queryField$calculation) as $sumField"),
															\DB::raw("DATE($datefield) as D"),
															$extraSelect
// 															"$datefield as D"
															)
													->take(300);
						if ($rquery&&$query) $rquery->union($query);
						else if($query)	$rquery = $query;
					}
					else{
						if (is_string($modelName))
							$lineQuery = $modelName::where($objectIdField,$objectId)->select(\DB::raw("$minus($queryField$calculation) as $sumField"))
													->take(1);
						else 
							$lineQuery = $modelName->where($objectIdField,$objectId)->select(\DB::raw("$minus($queryField$calculation) as $sumField"))
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
										->selectRaw("sum($sumField) as $sumField, D, E")
										->get();
					$series[]       = [
							"type"  		=> $chartType,
							"name"  		=> $category,
							"color" 		=> "#$color",
							"data"  		=> $dataSet,
							"extraTooltip"  => $objectNames,
					];
					foreach($dataSet as $index => $data ){
						$dataValue		= $data->V;
						
 						/* $fieldD			= Carbon::parse($data->D)->format($dateFormat);
						$fieldD			= substr($data->D, 0, strlen("2016-01-09"));
						$data->D		=  $fieldD; */
 						$fieldD			=  $data->D;
						if (array_key_exists($fieldD, $summaryLine))
							$summaryLine[$fieldD]->V	+= $dataValue;
						else
							$summaryLine[$fieldD]	= (object) array('D' => $fieldD,'V' => $dataValue);
						$compareValue = $summaryLine[$fieldD]->V;
						if ((!$minY||$minY>$dataValue)&&$dataValue) $minY = $dataValue;
						if ((!$maxY||$maxY<$dataValue)&&$dataValue) $maxY = $dataValue;
						
						if ((!$minY||$minY>$compareValue)&&$compareValue) $minY = $compareValue;
						if ((!$maxY||$maxY<$compareValue)&&$compareValue) $maxY = $compareValue;
					}
				}
				if ($rLineQuery) {
// 					$binding = $rLineQuery instanceof  Illuminate\Database\Eloquent\Builder ? $rLineQuery->getQuery():$rLineQuery;
					$binding = $rLineQuery->getQuery();
					$rDataSet	= \DB::table( \DB::raw("({$rLineQuery->toSql()}) as sub") )
										->mergeBindings($binding) // you need to get underlying Query Builder
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
						if ((!$maxY||$maxY<$rValue)&&$rValue) $maxY		= $rValue;
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
