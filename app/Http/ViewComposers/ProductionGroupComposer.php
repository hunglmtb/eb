<?php

namespace App\Http\ViewComposers;

use App\Models\LoProductionUnit;
use App\Models\LoArea;
use App\Models\Facility;
use App\Models\UserDataScope;
use App\Repositories\UserRepository as UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
class ProductionGroupComposer
{
    /**
     * The user repository implementation.
     *
     * @var UserRepository
     */
    protected $user;
    protected $prefix;
    protected $currentData;
    
    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $users){
        $this->user 		= auth()->user();
        $this->prefix 		= "";
        $this->currentData 	= null;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view){
    	$fgs 				= $view->filters;
    	$this->prefix		= isset($view->prefix)?$view->prefix:"";
    	$this->currentData	= isset($view->currentData)?$view->currentData:null;
    	$filterGroups 		= array('enableButton'	=> isset($fgs['enableButton'])?$fgs['enableButton']:true);
    	$workspace 			= $this->user->workspace();
    	
    	if (array_key_exists('dateFilterGroup', $fgs)) {
	    	$dateFilterGroup = $this->initDateFilterGroup($workspace,$fgs['dateFilterGroup']);
	    	$filterGroups['dateFilterGroup'] = $dateFilterGroup;
    	}
    	
    	if (array_key_exists('productionFilterGroup', $fgs)) {
	    	$productionFilterGroup = $this->initProductionFilterGroup($workspace,$fgs['productionFilterGroup']);
	    	$filterGroups['productionFilterGroup'] = $productionFilterGroup;
    	}
    	
    	$fres = array_key_exists('frequenceFilterGroup', $fgs)?$fgs['frequenceFilterGroup']:array();
    	$frequenceFilterGroup = $this->initFrequenceFilterGroup($fres,$filterGroups);
    	$filterGroups['frequenceFilterGroup'] = $frequenceFilterGroup;
    	$view->with('filterGroups', $filterGroups);
    	$view->with('prefix', $this->prefix);
    }
    
    public function initDateFilterGroup($workspace,$extra=null){
    	if ($extra==null) return null;
    	if ($workspace) {
    		$beginDate = $workspace->W_DATE_BEGIN;
    		$endDate = $workspace->W_DATE_END;
    	}
    	else{
	    	$beginDate 	= Carbon::yesterday();
	    	$endDate 	= Carbon::now();
    	}
    	
    	foreach($extra as $id => $item ){
    		switch ($item['id']) {
    			case 'date_begin':
    				$item['value'] = $beginDate;
    				break;
    					
    			case 'date_end':
    				$item['value'] = $endDate;
    				break;
    			default:
    				$item['value'] = Carbon::now();
    				break;
    		}
    		$extra[$id] = $item;
    	};
    	return $extra;
    }
    
    
    public function initProductionFilterGroup($workspace,$extras=null)
    {
    	if ($workspace) {
	    	$pid = $workspace->PRODUCTION_UNIT_ID;
	    	$aid = $workspace->AREA_ID;
	    	$fid = $workspace->W_FACILITY_ID;
    	}
    	else{
    		$pid = 0;
    		$aid = 0;
    		$fid = 0;
    	}
    	
    	if ($this->currentData) {
    		$pid = array_key_exists('LoProductionUnit', $this->currentData)?$this->currentData["LoProductionUnit"]:$pid;
    		$aid = array_key_exists('LoArea', $this->currentData)?$this->currentData["LoArea"]:$aid;
    		$fid = array_key_exists('Facility', $this->currentData)?$this->currentData["Facility"]:$fid;
    	}
    	
    	$userDataScope	= UserDataScope::where("USER_ID",$this->user->ID)->first();
    	if ($userDataScope) {
    		$DATA_SCOPE_PU			=$userDataScope->PU_ID;
			$DATA_SCOPE_AREA		=$userDataScope->AREA_ID;
			$DATA_SCOPE_FACILITY	=$userDataScope->FACILITY_ID;
    	}
    	else {
    		$DATA_SCOPE_PU			=null;
    		$DATA_SCOPE_AREA		=null;
    		$DATA_SCOPE_FACILITY	=null;
    	}
    	
    	if($DATA_SCOPE_PU&&$DATA_SCOPE_PU>0)
    		$productionUnits = LoProductionUnit::where('ID',$DATA_SCOPE_PU)->get();
    	else 
    		$productionUnits = LoProductionUnit::all(['ID', 'NAME']);

    	$currentProductUnit = ProductionGroupComposer::getCurrentSelect($productionUnits,$pid);
    	
    	if($currentProductUnit) $areas = $currentProductUnit->LoArea()->getResults();
    	else if($DATA_SCOPE_AREA&&$DATA_SCOPE_AREA>0) $areas = LoArea::where('ID',$DATA_SCOPE_AREA)->get();
	    else  $areas 	=	null;
    			
    	$currentArea = ProductionGroupComposer::getCurrentSelect($areas,$aid);
    	
    	if($currentArea) $facilities = $currentArea->Facility()->getResults();
    	else if($DATA_SCOPE_FACILITY&&$DATA_SCOPE_FACILITY>0) $areas = Facility::where('ID',$DATA_SCOPE_FACILITY)->get();
    	else  $facilities 	=	null;
    	
    	$currentFacility = ProductionGroupComposer::getCurrentSelect($facilities,$fid);
	    $productionFilterGroup =['LoProductionUnit'	=>	$this->getFilterArray('LoProductionUnit',$productionUnits,$currentProductUnit),
					    		'LoArea'			=>	$this->getFilterArray('LoArea',$areas,$currentArea),
					    		'Facility'			=>	$this->getFilterArray('Facility',$facilities,$currentFacility)
    							];
	    
	    $currentObject = $currentFacility;
	    foreach($extras as $source => $model ){
	    	$option = $this->getExtraOptions($productionFilterGroup,$model,$source);
	    	if ($option&&array_key_exists($source, $option)&&array_key_exists("object", $option[$source])) 
	    		$currentObject = $option[$source]["object"];
	    	$rs = ProductionGroupComposer::initExtraDependence($productionFilterGroup,$model,$currentObject,$option);
	    	$eCollection 	= $rs['collection'];
	    	$modelName 		= $rs['model'];
	    	$eId			= $this->currentData&&isset($this->currentData[$modelName])?$this->currentData[$modelName]:null;
	    	$extraFilter 	= ProductionGroupComposer::getCurrentSelect ( $eCollection,$eId );
	    	$productionFilterGroup [$modelName] = $this->getFilterArray ($modelName, $eCollection, $extraFilter,$model );
	    }
	    return $productionFilterGroup;
    }
    
	public function getExtraOptions($productionFilterGroup, $model, $source = null) {
		if (is_string ( $source )) {
			$extraSource = $productionFilterGroup [$source];
			// $currentId = $extraSource['currentId'];
			$entry = ProductionGroupComposer::getCurrentSelect($extraSource ['collection']);
			$eModel = $entry->CODE;
			$tableName = strtolower ( $eModel );
			$mdlName = \Helper::camelize ( $tableName, '_' );
// 			$mdl = 'App\Models\\' . $mdlName;
// 			$eCollection = $mdl::getEntries ( $currentFacility->ID );
			return [
					$source =>[	'name'		=>$mdlName,
								'id'		=>$entry->ID,
								'object'	=>$entry
					]
			];
		}
		return null;
	}
    
    
    public function initFrequenceFilterGroup($extras=null,$filterGroups = null)
    {
    	$frequenceFilterGroup =[];
    	foreach($extras as $model ){
    		if ($filterGroups) {
    			$filterGroups["frequenceFilterGroup"] = $frequenceFilterGroup;
    		}
    		if (is_array($model)) {
    			$collection 		= $this->getFrequenceCollection($model,$filterGroups);
    			$modelName			= $model['name'];
	    		$eId				= $this->currentData&&isset($this->currentData[$modelName])?$this->currentData[$modelName]:null;
    			$unit 				= ProductionGroupComposer::getCurrentSelect($collection,$eId);
//     			$unit = $collection!=null&&$collection->count()>0?$collection->first():null;
    			$frequenceFilterGroup[$modelName] = $this->getFilterArray($modelName,$collection,$unit,$model);
    		}
    		else $frequenceFilterGroup[$model] = $this->getFilterArray($model);
    	}
    	return $frequenceFilterGroup;
    }
       
    public function getFrequenceCollection($options=null,$filterGroups = null){
    	$collection 			= null;
    	if ($filterGroups) {
    		$mdl		= $options['name'];
	    	$mdl 		= 'App\Models\\' . $mdl;
    		if (array_key_exists('source', $options)&&array_key_exists('getMethod', $options)) {
	    		$source 	= $options['source'];
	    		$getMethod 	= $options['getMethod'];
	    		$sourceData	= [];
	    		foreach($source as $filter => $fields ){
	    			foreach($fields as $field ){
	    				if ($filter=="dateFilterGroup") {
		    				$sourceData[$field] = $filterGroups[$filter][$field]['value'];
	    				}
	    				else $sourceData[$field] = $filterGroups[$filter][$field]['current'];
	    			}
	    		}
	    		$getMethod = array_key_exists('getMethod', $options)?$options['getMethod']:'getAll';
	    		$collection = $mdl::$getMethod($sourceData);
    		}
    		else if (array_key_exists('getMethod', $options)) {
	    		$getMethod 	= $options['getMethod'];
	    		$collection = $mdl::$getMethod($options);
    		}
    		else
    			$collection = $mdl::getAll();
    	}
    	return $collection;
    }
    
    public static function getCurrentSelect($collection,$id=null)
    {
    	if ($collection!=null&& $collection instanceof Collection) {
    		$units 	= $collection->keyBy('ID');
    		$unit 	= $units->get($id);
    		if ($unit==null) {
    			$unit = $collection->first();
    		}
    		return $unit;
    	}
    	return null;
    }
    
    public function getFilterArray($id,$collection=null,$currentUnit=null,$option=null){
    	$fullId					= "$this->prefix".$id;
    	$filters				= \Helper::getFilterArray($fullId,$collection,$currentUnit,$option);
		$filters['modelName'] 	= $id;
    	return $filters;
    }
    
    public static function initExtraDependence($productionFilterGroup, $model, $currentUnit,$option = null) {
    	$modelName = $model;
		$currentId = null;
		$eCollection = [];
    	if (is_string ( $model )) {
    		if (method_exists($currentUnit,$model)) {
				$entry = $currentUnit->$model($option);
				if ($entry) {
					if ($entry instanceof Collection || is_array($entry)) $eCollection = $entry;
					else if ($entry instanceof Relation)   $eCollection = $entry->getResults();
				}
				$currentId = isset($option[$model]['id'])?$option[$model]['id']:null;
    		}
    	} else {
			$modelName 		= $model ['name'];
			if (array_key_exists('independent', $model)&&$model ['independent']) {
				$mdl = 'App\Models\\' . $modelName;
				$getMethod = array_key_exists('getMethod', $model)?$model['getMethod']:'getAll';
				$eCollection = $mdl::$getMethod();
			}
			else if(!is_array($currentUnit)&&method_exists($currentUnit,$modelName)){
				$entry = $currentUnit->$modelName($option);
				if ($entry) {
					if ($entry instanceof Collection || is_array($entry)) $eCollection = $entry;
					else if ($entry instanceof Relation)   $eCollection = $entry->getResults();
				}
			}
			
		}
		return ['collection' 	=> $eCollection,
				'model' 		=> $modelName,
				'currentId'		=> $currentId
		];
	}
    
}