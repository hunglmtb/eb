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
	actions.loadUrl = "/code/loadeu";
	actions.saveUrl = "/code/saveeu";
	actions.type = {idName:'EU_ID',xIdName:'X_EU_ID'};
	var aLoadNeighbor = actions.loadNeighbor;
	actions.loadNeighbor = function() {
		var activeTabID = getActiveTabID();
		if(activeTabID=='EnergyUnitDataAlloc'){
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