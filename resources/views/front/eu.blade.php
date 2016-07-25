<?php
	$currentSubmenu ='eu';
	$tables = ['EnergyUnitDataFdcValue'	=>['name'=>'FDC VALUE'],
			'EnergyUnitDataValue'		=>['name'=>'STD VALUE'],
			'EnergyUnitDataTheor'		=>['name'=>'THEORETICAL'],
			'EnergyUnitDataAlloc'		=>['name'=>'ALLOCATION'],
			'EnergyUnitCompDataAlloc'	=>['name'=>'COMPOSITION ALLOC'],
			'EnergyUnitDataPlan'		=>['name'=>'PLAN'],
			'EnergyUnitDataForecast'	=>['name'=>'FORECAST'], 
	];
 	$active = 1;
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
	actions.type = {
					idName:['{{config("constants.euId")}}','{{config("constants.euFlowPhase")}}','{{config("constants.eventType")}}'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
						return '{{config("constants.euPhaseConfigId")}}';
					},
// 					xIdName:'X_EU_ID'
					};
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