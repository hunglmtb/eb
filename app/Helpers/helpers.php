<?php
use App\Models\LockTable;
use App\Models\AuditValidateTable;
use App\Models\AuditApproveTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
class Helper {
	
	public static function getFilterArray($id,$collection=null,$currentUnit=null,$option=null){
		if ($option==null||is_string($option)) {
			$option = array();
		}
		$option['id'] 			= $id;
		$option['modelName'] 	= array_key_exists('modelName', $option)?$option['modelName']:$id;
		$option['collection'] 	= $collection;
		$option['currentId'] 	= $currentUnit&&$currentUnit->ID?$currentUnit->ID:'';
		$option['current'] 		= $currentUnit;
		return $option;
	}
	
	public static function filter($option=null) {
		if ($option==null) return;
		$model			='App\\Models\\'.$option['modelName'];
		$collection		= array_key_exists('collection', $option)?$option['collection']:false;
		
		if (!$collection) {
			if ( array_key_exists('getMethod', $option)) {
				$getMethod 	= $option['getMethod'];
				$params		= array_key_exists('filterData', $option)?$option['filterData']:null;
				$collection = call_user_func("$model::$getMethod",$params);
// 				$collection = $model::$getMethod();
			}
// 			else if(isset($option["source"])&&count($option["source"]))
			else $collection = $model::all(['ID', 'NAME']);
		}
// 		$collection = $model::select(['ID', 'NAME'])->orderBy('NAME')->get();
		$option['collection'] = $collection;
		Helper::buildFilter($option);
	}
	
	public static function buildFilter($option=null) {
		if ($option == null) return;
		$collection 	= $option['collection'];
		$currentUnit 	= array_key_exists('current', $option)?$option['current']:null;
		
		$default		= (!array_key_exists('defaultEnable', $option)||(array_key_exists('defaultEnable', $option)&&$option['defaultEnable']))
							&&array_key_exists('default', $option)?
							$option['default']:false;
		$id				= array_key_exists('id', $option)?$option['id']:false;
		$name			= array_key_exists('name', $option)?$option['name']:false;
		$filterName 	= array_key_exists('filterName', $option)?$option['filterName']:$name;
		$lang			= session()->get('locale', "en");
		$filterName		= Lang::has("front/site.$filterName", $lang)?trans("front/site.$filterName"):$filterName;
		$enableTitle	= array_key_exists('enableTitle', $option)?$option['enableTitle']:true;
		if ($enableTitle) {
			$htmlFilter 	= "<div  class=\"filter $name\" id='container_$id'><div><b id=\"title_$id\">$filterName</b>".
								'</div>
								<select id="'.$id.'" name="'.$name.'">';
		}
		else {
			$htmlFilter 	= "<select id='$id' name='$name'>";
		}
		if ($default) {
			$htmlFilter .= '<option value="'.$default['ID'].'">'.$default['NAME'].'</option>';
		}
	
		$currentId = array_key_exists('currentId', $option)?$option['currentId']:'';
		if ($collection) {
			foreach($collection as $item ){
				if($item){
					$nameValue 	= isset($item->CODE)?$item->CODE:"";
					$fvalue 	= $item->ID!=""?$item->ID:$nameValue;
					$optionName	= $item->NAME;
					$optionName	= Lang::has("front/site.$optionName", $lang)?trans("front/site.$optionName"):$optionName;
					$htmlFilter .= '<option name="'.$nameValue
								.'" value="'.$fvalue.'"'.($currentUnit&&$currentUnit==$item?'selected="selected"':'')
								.'>'.$optionName.'</option>';
				}
			}
		}
		
	
	
		$htmlFilter .= '</select></div>';
		if ($id&&array_key_exists('dependences', $option)&&count($option['dependences'])>0) {
			$dependences = [];
			$more = [];
			$originDependences = $option['dependences'];
			foreach($originDependences as $dependence ){
// 				$dependences[] = $dependence;
				if (is_string($dependence) ) {
					$dependences[] = $dependence;
				}
				/* else if (isset($dependence['independent'])&&$dependence['independent']){
//  					$dependences[] = $dependence['name'];
// 					$more[] = $dependence['name'];
				} */
			}
			
			if (count($originDependences)>0
					&&(!array_key_exists('single', $option)
							||!$option['single'])) {
				$extra = array_key_exists('extra', $option)&&count($option['extra'])>0?$option['extra']:null;
				$extra = is_array($extra)&&count($extra)>0?",['".implode("','", $extra)."']":'';
				$htmlFilter.= "<script>registerOnChange('$id',".json_encode($originDependences)."$extra)</script>";
			}
		}
	
		echo $htmlFilter;
	}
	
	
	public static function selectDate($option=null) {
		
		if ($option==null) return;
		$name=array_key_exists('name', $option)?$option['name']:'';
		$value=array_key_exists('value', $option)?$option['value']:'';
		$id=array_key_exists('id', $option)?$option['id']:'';
		$sName=array_key_exists('sName', $option)?$option['sName']:'';
	
		$htmlFilter = '';
		switch ($id) {
    			/* case 'date_begin':
    			case 'date_end':
    			case 'f_date_from':
    			case 'f_date_to':
    			case 'txtCargoDate':
    				break; */
    			case 'cboFilterBy':
    					$htmlFilter = 	"<div class=\"filter\"><div><b>$name</b>".
			    							'</div><select id="'.$id.'" name="'.$name.'">';
    					$htmlFilter .= "<option value = 'SAMPLE_DATE'>Sample Date</option><option value = 'TEST_DATE'>Test Date</option><option value = 'EFFECTIVE_DATE'>Effective Date</option>";
						$htmlFilter .= '</select></div>';
    					break;
    			default:
    				$configuration = auth()->user()->getConfiguration();
    				$format = $configuration['time']['DATE_FORMAT_CARBON'];//'m/d/Y';
    				if ($value&&$value instanceof Carbon) {
						$value	=	$value->format($format);
    				}
    				$jsFormat = $configuration['picker']['DATE_FORMAT_JQUERY'];//'mm/dd/yy';
    				$htmlFilter.= "<div class='date_input'><div><b>$name</b></div><input style='width:85%' type='text' id = '$id' name='$sName' size='15' value='$value'></div>";
					$htmlFilter.= '<script>
											$( "#'.$id.'" ).datepicker({
												changeMonth:true,
												changeYear:true,
												dateFormat:"'.$jsFormat.'"
											});
										</script>';
					
					if (array_key_exists('dependences', $option)) {
						$dependences = $option['dependences'];
						$extra = array_key_exists('extra', $option)&&count($option['extra'])>0?$option['extra']:null;
						$extra = is_array($extra)&&count($extra)>0?",['".implode("','", $extra)."']":'';
						$htmlFilter.= "<script>registerOnChange('$id',['".implode("','", $dependences)."']$extra)</script>";
					}
    				break;
    		}
		
	
		echo $htmlFilter;
	}
	
	public static function checkLockedTable($dcTable,$occur_date,$facility_id) {
// 		$mdl = "App\Models\\".$mdlName;
// 		$tableName = $mdl::getTableName();
		$lockTable = LockTable::where(['TABLE_NAME'=>$dcTable,'FACILITY_ID'=>$facility_id])
		      					->whereDate('LOCK_DATE', '>=', $occur_date)
								->first();
		return $lockTable!=null&&$lockTable!=false;
	}
	
	public static function checkApproveTable($dcTable,$occur_date,$facility_id) {
		$lockTable = AuditApproveTable::where(['TABLE_NAME'=>$dcTable,'FACILITY_ID'=>$facility_id])
		->whereDate('DATE_FROM', '<=', $occur_date)
		->whereDate('DATE_TO', '>=', $occur_date)
		->first();
		return $lockTable!=null&&$lockTable!=false;
	}
	
	public static function checkValidateTable($dcTable,$occur_date,$facility_id) {
		$lockTable = AuditValidateTable::where(['TABLE_NAME'=>$dcTable,'FACILITY_ID'=>$facility_id])
		->whereDate('DATE_FROM', '<=', $occur_date)
		->whereDate('DATE_TO', '>=', $occur_date)
		->first();
		return $lockTable!=null&&$lockTable!=false;
	}
	
	
	public static function camelize($input, $separator = '_')
	{
		return str_replace($separator, '', ucwords($input, $separator));
	}
	
	public static function getRoundValue($value){
		$value = $value?round($value):0;
		return $value;
	}
	
	public static function getModelName($table)
	{
		$tableName = strtolower ( trim($table) );
		$mdlName = static::camelize ( $tableName, '_' );
		$mdl = 'App\Models\\' . $mdlName;
		return $mdl;
	}
	
	public static function convertDate2CarbonFormat($dateFormat)
	{
		if ($dateFormat) {
			$lowerDateFormat	= 	strtolower($dateFormat);
			$elements			= 	explode('/', $lowerDateFormat);
			$newElements		= 	[];
			foreach ($elements as $element){
	// 			$newElements[] = substr($element, 0, strlen($element)/2);
				if ($element[0]=='y') {
					$newElements[] = 'Y';
				}
				else 
					$newElements[] = $element[0];
			}
			$newFormat = implode('/', $newElements);
			return $newFormat;
		}
		else return null;
	}
	
	public static function convertDate2JqueryFormat($dateFormat)
	{
		if ($dateFormat) {
			$lowerDateFormat	= 	strtolower($dateFormat);
			$elements			= 	explode('/', $lowerDateFormat);
			$newElements		= 	[];
			foreach ($elements as $element){
	 			$newElements[] = substr($element, 0, strlen($element)/2);
			}
			$newFormat = implode('/', $newElements);
			return $newFormat;
		}
		else return null;
	}
	
	public static function convertTime2PickerFormat($timeFormat)
	{
		if ($timeFormat) {
			$newFormat	= \App\Models\DateTimeFormat::$timeFortmatPair;
			return $newFormat[$timeFormat];
		}
		else return null;
	}
	
	public static function parseDate($dateString)
	{
		if (is_null($dateString))  return "";
		$formatSetting 		= 	session('configuration');
		$formatSetting 		= 	$formatSetting?$formatSetting:\App\Models\DateTimeFormat::$defaultFormat;
		$dateFormat 		= 	$formatSetting['DATE_FORMAT'];
		$carbonFormat		= 	\Helper::convertDate2CarbonFormat($dateFormat);
		$carbonDate 		= 	Carbon::createFromFormat($carbonFormat, $dateString);
		$carbonDate->hour 	= 0;
		$carbonDate->minute = 0;
		$carbonDate->second = 0;
		return $carbonDate;
	}
	
	
	public static function logger() {
		$queries = \DB::getQueryLog();
		$query = end($queries);
		$prep = $query['query'];
		foreach( $query['bindings'] as $binding ) : $prep = preg_replace("#\?#", $binding, $prep, 1);
		endforeach;
		return $prep;
	}
	
	public static function isNullOrEmpty($value){
		return $value==null||$value==''||$value==false;
	}
	
	public static function translateText($lang,$text){
		return Lang::has("front/site.$text", $lang)?trans("front/site.$text"):$text;
	}
	
	public static function getExtraSelects($tableView,$objectType,$objectId,$extraSelect){
		if($tableView=="v_cargo_nomination"||$tableView=="V_CARGO_NOMINATION") return "CARGO_NAME as E";
		return $extraSelect;
	}
	
	public static function setGetterUpperCase(){
		if(config('database.default')==='oracle'){
			$dbh 			= \DB::connection()->getPdo();
	    	$dbh->setAttribute (\PDO::ATTR_CASE, \PDO::CASE_UPPER);
		}
	}
	
	public static function setGetterLowerCase(){
		if(config('database.default')==='oracle'){
			$dbh 			= \DB::connection()->getPdo();
			$dbh->setAttribute (\PDO::ATTR_CASE, \PDO::CASE_LOWER);
		}
	}
	
	public static function setGetterNaturalCase(){
		if(config('database.default')==='oracle'){
			$dbh 			= \DB::connection()->getPdo();
			$dbh->setAttribute (\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
		}
	}
	
	
	public static function extractColumns($columns){
		$results = [];
		foreach($columns as $column ){
			if ($column&&isset($column->column_name)) {
				$results[] = $column->column_name;
			}
		}
		return $results;
	}
	
	public static function getCommonGroupFilter($options = []){
		$codeFlowPhase	= ["name"		=>	"CodeFlowPhase",
							"source"	=>	"ObjectName" ];
		$filterGroups = array(	'productionFilterGroup'	=> [['name'			=>'CodeProductType',
															'independent'	=> true,
															"getMethod"		=> "loadActive",
															'extra'			=> ["Facility","CodeProductType","IntObjectType","ObjectDataSource"],
															'dependences'	=>["ObjectName",
																				$codeFlowPhase]],
															['name'			=>'IntObjectType',
															'independent'	=>true,
															"getMethod"		=> "getGraphObjectType",
															'extra'			=> ["Facility","CodeProductType","IntObjectType","ObjectDataSource"],
															'dependences'	=> ["ObjectName",
																				["name"		=>	"ObjectDataSource"],
																				"ObjectTypeProperty",
																				$codeFlowPhase
																				]
															]],
								'frequenceFilterGroup'	=> [	["name"			=> "ObjectName",
																"getMethod"		=> "loadBy",
																"defaultEnable"	=> false,
																'dependences'	=> ["CodeFlowPhase"],
																"source"		=> ['productionFilterGroup'=>["Facility","IntObjectType","CodeProductType"]]],
																["name"			=> "ObjectDataSource",
																"getMethod"		=> "loadBy",
																"filterName"	=>	"Data source",
																'dependences'	=> ["ObjectTypeProperty"],
																'extra'			=> ["Facility","CodeProductType"],
																"source"		=> ['productionFilterGroup'=>["IntObjectType"]]],
																["name"			=> "ObjectTypeProperty",
																"getMethod"		=> "loadBy",
																"filterName"	=>	"Property",
																"source"		=>  ['frequenceFilterGroup'=>["ObjectDataSource"]]],
																["name"			=> "CodeFlowPhase",
																"getMethod"		=> "loadBy",
																"source"		=>  ['frequenceFilterGroup'=>["ObjectName"]]],
																["name"			=>	"CodeAllocType",
																"getMethod"		=> "loadActive",
																"filterName"	=>	"Alloc type",],
																["name"			=>	"CodePlanType",
																"getMethod"		=> "loadActive",
																"filterName"	=>	"Plan type",],
																["name"			=>	"CodeForecastType",
																"getMethod"		=> "loadActive",
																"filterName"	=> "Forecast type",]
														],
								'dateFilterGroup'		=> array(	['id'=>'date_begin','name'=>'From date'],
																	['id'=>'date_end',	'name'=>'To date']),
								'enableButton'			=> false,
								'FacilityDependentMore'	=> ["ObjectName","CodeFlowPhase"],
								'extra' 				=> ['IntObjectType','CodeProductType',"ObjectDataSource"]
		);
		
		return $filterGroups;
	}
}
