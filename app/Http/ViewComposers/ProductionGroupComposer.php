<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\UserRepository as UserRepository;
use App\Models\LoProductionUnit;

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
    	$fgs = $view->filterGroups;
    	$filterGroups = array();
    	$workspace = $this->user->workspace();
    	
    	if (array_key_exists('dateFilterGroup', $fgs)) {
	    	$dateFilterGroup = $this->initDateFilterGroup($workspace,$fgs['dateFilterGroup']);
	    	$filterGroups['dateFilterGroup'] = $dateFilterGroup;
    	}
    	
    	$productionFilterGroup = $this->initProductionFilterGroup($workspace,$fgs['productionFilterGroup']);
    	$filterGroups['productionFilterGroup'] = $productionFilterGroup;
    	
    	if (array_key_exists('frequenceFilterGroup', $fgs)) {
    		$filterGroups['frequenceFilterGroup'] = array_key_exists('frequenceFilterGroup', $fgs)?$fgs['frequenceFilterGroup']:array();
    	}
    		 
    	
    	$view->with('filterGroups', $filterGroups);
    }
    
    public function getCurrentSelect($collection,$id=null)
    {
    	if ($collection!=null) {
	    	$units = $collection->keyBy('ID');
	    	$unit = $units->get($id);
	    	 
	    	if ($unit==null) {
	    		$unit = $units->first();
	    	}
	    	return $unit;
    	}
    	return null;
    }
    
    public function initDateFilterGroup($workspace,$extra=null){
    	if ($extra==null) return null;
    	$beginDate = $workspace->W_DATE_BEGIN;
    	$endDate = $workspace->W_DATE_END;
    	
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
    
    
    public function initProductionFilterGroup($workspace,$extra=null)
    {
    	$pid = $workspace->PRODUCTION_UNIT_ID;
    	$aid = $workspace->AREA_ID;
    	$fid = $workspace->W_FACILITY_ID;
    	
    	$productionUnits = LoProductionUnit::all(['ID', 'NAME']);
    	$currentProductUnit = $this->getCurrentSelect($productionUnits,$pid);
    	$areas = $currentProductUnit->area()->getResults();
    	$currentArea = $this->getCurrentSelect($areas,$aid);
    	$facilities = $currentArea->facility()->getResults();
    	$currentFacility = $this->getCurrentSelect($facilities,$fid);
	    $productionFilterGroup =[$this->getFilterArray('Production Unit',$productionUnits,$currentProductUnit),
						    		$this->getFilterArray('Area',$areas,$currentArea),
						    		$this->getFilterArray('Facility',$facilities,$currentFacility)
    							];
	    if ($extra!=null) {
	    	$model = $extra['model'];
// 	    	$extraId = 1;
	    	$extras = $currentFacility->$model()->getResults();
	    	$extraFilter = $this->getCurrentSelect($extras);
// 	    	$extras[]= array('ID'=>55,'NAME'=>'No Group');
	    	
		    $productionFilterGroup[] = $this->getFilterArray($extra['name'],$extras,$extraFilter,$extra);
	    }
	    return $productionFilterGroup;
    }
        
    
    public function getFilterArray($filteName,$collection,$currentUnit,$option=null)
    {
    	if ($option==null) {
    		$option = array();
    	}
    	$option['collection'] = $collection;
    	$option['filteName'] = $filteName;
    	$option['currentId'] =  $currentUnit!=null?$currentUnit->ID:'';
    	return $option; 
    }
    
    
}