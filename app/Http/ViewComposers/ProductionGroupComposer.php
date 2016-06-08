<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\UserRepository as UserRepository;
use App\Models\LoProductionUnit;
use Carbon\Carbon;

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
    	$filterGroups = array();
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
	    
	    foreach($extras as $model ){
	    	$eCollection = $currentFacility->$model()->getResults();
	    	$extraFilter = ProductionGroupComposer::getCurrentSelect($eCollection);
		    $productionFilterGroup[] = ProductionGroupComposer::getFilterArray($model,$eCollection,$extraFilter);
	    
	    }
	    return $productionFilterGroup;
    }
    
    public function initFrequenceFilterGroup($extras=null)
    {
    	$frequenceFilterGroup =[];
    	foreach($extras as $model ){
    		$frequenceFilterGroup[] = ProductionGroupComposer::getFilterArray($model);
    	}
    	return $frequenceFilterGroup;
    }
       
    
    public static function getCurrentSelect($collection,$id=null)
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
    
    public static function getFilterArray($id,$collection=null,$currentUnit=null,$option=null)
    {
    	if ($option==null) {
    		$option = array();
    	}
    	$option['id'] = $id;
    	$option['collection'] = $collection;
    	$option['currentId'] =  $currentUnit!=null?$currentUnit->ID:'';
    	return $option; 
    }
    
    
}