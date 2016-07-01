<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\UserRepository as UserRepository;
use App\Models\LoProductionUnit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
    	
    	$productionFilterGroup = $this->initProductionFilterGroup($workspace,$fgs['productionFilterGroup']);
    	$filterGroups['productionFilterGroup'] = $productionFilterGroup;
    	
    	$frequenceFilterGroup = $this->initFrequenceFilterGroup(array_key_exists('frequenceFilterGroup', $fgs)?$fgs['frequenceFilterGroup']:array());
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
	    	$beginDate = Carbon::yesterday();
	    	$endDate = Carbon::now();
    	}
    	
    	for($i = 0; $i < count($extra);$i++){
    		switch ($extra[$i]['id']) {
    			case 'date_begin':
    				$extra[$i]['value'] = $beginDate;
    				break;
    				 
    			case 'date_end':
    				$extra[$i]['value'] = $endDate;
    				break;
    		
    			default:
    				break;
    		}
    	}
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
	    $productionFilterGroup =[ProductionGroupComposer::getFilterArray('LoProductionUnit',$productionUnits,$currentProductUnit),
					    		ProductionGroupComposer::getFilterArray('LoArea',$areas,$currentArea),
					    		ProductionGroupComposer::getFilterArray('Facility',$facilities,$currentFacility)
    							];
	    
	    foreach($extras as $source => $model ){
	    	$option = $this->getExtraOptions($productionFilterGroup,$model,$source);
	    	$rs = ProductionGroupComposer::initExtraDependence($productionFilterGroup,$model,$currentFacility,$option);
	    	$eCollection = $rs['collection'];
	    	$modelName = $rs['model'];
	    	$extraFilter = ProductionGroupComposer::getCurrentSelect ( $eCollection );
	    	$productionFilterGroup [$modelName] = ProductionGroupComposer::getFilterArray ( $modelName, $eCollection, $extraFilter );
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
					$source =>['name'=>$mdlName,'id'=>$entry->ID]
			];
		}
		return null;
	}
    
    
    public function initFrequenceFilterGroup($extras=null)
    {
    	$frequenceFilterGroup =[];
    	foreach($extras as $model ){
    		if (is_array($model)) {
    			$frequenceFilterGroup[] = ProductionGroupComposer::getFilterArray($model['name'],null,null,$model);
    		}
    		else $frequenceFilterGroup[] = ProductionGroupComposer::getFilterArray($model);
    	}
    	return $frequenceFilterGroup;
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
    	if ($option==null) {
    		$option = array();
    	}
    	$option['id'] = $id;
    	$option['collection'] = $collection;
    	$option['currentId'] =  $currentUnit&&isset($currentUnit->ID)?$currentUnit->ID:'';
    	return $option; 
    }
    
    
    public static function initExtraDependence($productionFilterGroup, $model, $currentFacility,$option = null) {
    	$modelName = $model;
		if (is_string ( $model )) {
			$entry = $currentFacility->$model($option);
			if ($entry) {
				if ($entry instanceof Collection) $eCollection = $entry;
				else  $eCollection = $entry->getResults ();
			}
			else $eCollection = [];
		} else {
			$modelName = $model ['name'];
			if ($model ['independent']) {
				$mdl = 'App\Models\\' . $modelName;
				$getMethod = array_key_exists('getMethod', $model)?$model['getMethod']:'getAll';
				$eCollection = $mdl::$getMethod();
			}
			/* else {
				$extraSource = $productionFilterGroup [$model ['source']];
				// $currentId = $extraSource['currentId'];
				$eModel = $extraSource ['collection'] [0]->CODE;
				$tableName = strtolower ( $eModel );
				$mdlName = \Helper::camelize ( $tableName, '_' );
				$mdl = 'App\Models\\' . $mdlName;
				$eCollection = $mdl::getEntries ( $currentFacility->ID );
			} */
		}
		return ['collection' => $eCollection,
				'model' => $modelName
		];
	}
    
}