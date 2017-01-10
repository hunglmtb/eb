<?php
	$currentSubmenu ='/dc/eu';
	$lang			= session()->get('locale', "en");
	$tables = ['EnergyUnitDataFdcValue'	=> ['name'=>'FDC Value'],
			'EnergyUnitDataValue'		=> ['name'=>'STD Value'],
			'EnergyUnitDataTheor'		=> ['name'=>Lang::has("front/site.Theoretical", $lang)?trans("front/site.Theoretical"):"Theoretical"],       
			'EnergyUnitDataAlloc'		=> ['name'=>Lang::has("front/site.Allocation", $lang)?trans("front/site.Allocation"):"Allocation"],          
			'EnergyUnitCompDataAlloc'	=> ['name'=>Lang::has("front/site.Composition", $lang)?trans("front/site.Composition"):"Composition Alloc"], 
			'EnergyUnitDataPlan'		=> ['name'=>Lang::has("front/site.Plan", $lang)?trans("front/site.Plan"):"Plan"],                            
			'EnergyUnitDataForecast'	=> ['name'=>Lang::has("front/site.Forecast", $lang)?trans("front/site.Forecast"):"Forecast"],                
	];
 	$active = 3;
?>

@extends('core.pm')
@section('funtionName')
ENERGY UNIT DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/eu/load";
	actions.saveUrl 		= "/eu/save";
	actions.historyUrl 		= "/eu/history";
// 	actions.reloadAfterSave	= false;
	
	actions.type = {
					idName:['{{config("constants.euId")}}','{{config("constants.euFlowPhase")}}','{{config("constants.eventType")}}'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
						return '{{config("constants.euPhaseConfigId")}}';
					},
// 					xIdName:'X_EU_ID'
					};
	actions.validating = function (reLoadParams){
		return true;
	}
	
	var aLoadNeighbor = actions.loadNeighbor;
	actions.loadNeighbor = function() {
		var activeTabID = getActiveTabID();
		if(activeTabID=='EnergyUnitDataAlloc'||activeTabID=='EnergyUnitCompDataAlloc'){
			$('.CodeAllocType').css('display','block');
		}
		else{
			$('.CodeAllocType').css('display','none');
		}
		aLoadNeighbor();
	}


	var aLoadParams = actions.loadParams;
	actions.loadParams = function(reLoadParams) {
		var pr = aLoadParams(reLoadParams);
		var activeTabID = getActiveTabID();
		if(activeTabID=='EnergyUnitDataAlloc'){
			pr['CodeAllocType']= $('#CodeAllocType').val();
		}
		else{
			pr['CodeAllocType']= 0;
		}
		return pr;
	}
	
</script>
@stop