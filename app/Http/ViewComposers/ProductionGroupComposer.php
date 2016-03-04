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
    protected $users;

    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        // Dependencies automatically resolved by service container...
        $this->users = $users;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
    	$productionUnits = LoProductionUnit::all(['ID', 'NAME']);
    	$areas = $productionUnits->first()->areas()->getResults();
    	$fArea = $areas!=null?$areas->first():null;
    	$facilities = $fArea!=null?$fArea->facilities()->getResults():null;
    	 
    	$filterGroup = [array(	'type' => 'options',
    			'id' => 'cboProdUnit',
    			'name' => 'Production Unit',
    			'selectName' => 'cboProdUnit',
    			'tableName' => 'LO_PRODUCTION_UNIT',
    			'options'=>$productionUnits),
    			array(	'type' => 'options',
    					'id' => 'cboArea',
    					'name' => 'Area',
    					'selectName' => 'cboArea',
    					'options'=>$areas),
    			array(	'type' => 'options',
    					'id' => 'Facility',
    					'name' => 'Facility',
    					'selectName' => 'Facility',
    					'options'=>$facilities),
    	];
    	$view->with('filterGroup', $filterGroup);
    }
}