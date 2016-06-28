<?php
	$currentSubmenu ='forecast';
	$tables = ['EnergyUnitDataFdcValue'	=>['name'=>'FDC VALUE'],
	];
 	$active = 1;
?>

@extends('core.fp')
@section('funtionName')
WELL FORECAST
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/eu/load";
	actions.saveUrl = "/eu/save";
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