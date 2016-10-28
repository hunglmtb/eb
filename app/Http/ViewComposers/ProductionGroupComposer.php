<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\UserRepository as UserRepository;
use App\Models\LoProductionUnit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\Model;

class ProductionGroupComposer
{
    /**
     * The user repository implementation.
     *
     * @var UserRepository
     */
    protected $user;

    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        // Dependencies automatically resolved by service container...
        $this->user = auth()->user();
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
    	$fgs = $view->filters;
    	$filterGroups = array('enableButton'	=> isset($fgs['enableButton'])?$fgs['enableButton']:true);
    	$workspace = $this->user->workspace();
    	
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
    	
    	$productionUnits = LoProductionUnit::all(['ID', 'NAME']);
    	$currentProductUnit = ProductionGroupComposer::getCurrentSelect($productionUnits,$pid);
    	$areas = $currentProductUnit->LoArea()->getResults();
    	$currentArea = ProductionGroupComposer::getCurrentSelect($areas,$aid);
    	$facilities = $currentArea->Facility()->getResults();
    	$currentFacility = ProductionGroupComposer::getCurrentSelect($facilities,$fid);
	    $productionFilterGroup =['LoProductionUnit'	=>	ProductionGroupComposer::getFilterArray('LoProductionUnit',$productionUnits,$currentProductUnit),
					    		'LoArea'			=>	ProductionGroupComposer::getFilterArray('LoArea',$areas,$currentArea),
					    		'Facility'			=>	ProductionGroupComposer::getFilterArray('Facility',$facilities,$currentFacility)
    							];
	    
	    $currentObject = $currentFacility;
	    foreach($extras as $source => $model ){
	    	$option = $this->getExtraOptions($productionFilterGroup,$model,$source);
	    	if ($option&&array_key_exists($source, $option)&&array_key_exists("object", $option[$source])) 
	    		$currentObject = $option[$source]["object"];
	    	$rs = ProductionGroupComposer::initExtraDependence($productionFilterGroup,$model,$currentObject,$option);
	    	$eCollection = $rs['collection'];
	    	$modelName 		= $rs['model'];
	    	$extraFilter = ProductionGroupComposer::getCurrentSelect ( $eCollection );
	    	$productionFilterGroup [$modelName] = ProductionGroupComposer::getFilterArray ( $modelName, $eCollection, $extraFilter,$model );
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
    			$collection 			= $this->getFrequenceCollection($model,$filterGroups);
    			$unit = $collection!=null&&$collection->count()>0?$collection->first():null;
    			$frequenceFilterGroup[$model['name']] = ProductionGroupComposer::getFilterArray($model['name'],$collection,$unit,$model);
    		}
    		else $frequenceFilterGroup[$model] = ProductionGroupComposer::getFilterArray($model);
    	}
    	return $frequenceFilterGroup;
    }
       
    public function getFrequenceCollection($options=null,$filterGroups = null){
    	$collection 			= null;
    	if ($filterGroups&&array_key_exists('source', $options)&&array_key_exists('getMethod', $options)) {
    		$source 	= $options['source'];
    		$getMethod 	= $options['getMethod'];
    		$mdl		= $options['name'];
    		$sourceData	= [];
    		foreach($source as $filter => $fields ){
    			foreach($fields as $field ){
    				if ($filter=="dateFilterGroup") {
	    				$sourceData[$field] = $filterGroups[$filter][$field]['value'];
    				}
    				else $sourceData[$field] = $filterGroups[$filter][$field]['current'];
    			}
    		}
    		$mdl = 'App\Models\\' . $mdl;
    		$getMethod = array_key_exists('getMethod', $options)?$options['getMethod']:'getAll';
    		$collection = $mdl::$getMethod($sourceData);
    	}
    	return $collection;
    }
    
    public static function getCurrentSelect($collection,$id=null)
    {
    	if ($collection!=null&& $collection instanceof Collection) {
    		$units = $collection->keyBy('ID');
    		$unit = $units->get($id);
    		 
    		if ($unit==null) {
    			$unit = $units->first();
    		}
    		return $unit;
    	}
    	return null;
    }
    
    public static function getFilterArray($id,$collection=null,$currentUnit=null,$option=null)
    {
    	if ($option==null||is_string($option)) {
    		$option = array();
    	}
    	$option['id'] 			= $id;
    	$option['collection'] 	= $collection;
    	$option['currentId'] 	=  $currentUnit&&isset($currentUnit->ID)?$currentUnit->ID:'';
    	$option['current'] 		=  $currentUnit;
    	return $option; 
    }
    
    
    public static function initExtraDependence($productionFilterGroup, $model, $currentUnit,$option = null) {
    	$modelName = $model;
		$currentId = null;
		$eCollection = [];
    	if (is_string ( $model )) {
			$entry = $currentUnit->$model($option);
			if ($entry) {
				if ($entry instanceof Collection) $eCollection = $entry;
				else  $eCollection = $entry->getResults();
			}
			$currentId = isset($option[$model]['id'])?$option[$model]['id']:null;
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
					if ($entry instanceof Collection) $eCollection = $entry;
					else  $eCollection = $entry->getResults();
				}
			}
			
		}
		return ['collection' 	=> $eCollection,
				'model' 		=> $modelName,
				'currentId'		=> $currentId
		];
	}
    
}