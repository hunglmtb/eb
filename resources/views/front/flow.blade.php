<?php
	$currentSubmenu ='/dc/flow';
	$tables = ['FlowDataFdcValue'	=>['name'=>'FDC Value'],
				'FlowDataValue'		=>['name'=>'STD Value'],
				'FlowDataTheor'		=>['name'=>"Theoretical"],
				'FlowDataAlloc'		=>['name'=>"Allocation"],
				'FlowCompDataAlloc'	=>['name'=>"Composition Alloc"],
				'FlowDataPlan'		=>['name'=>"Plan"],
				'FlowDataForecast'	=>['name'=>"Forecast"],
	];
// 	$active = 3;
?>

@extends('core.pm')
@section('funtionName')
FLOW DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/code/load";
	actions.saveUrl 		= "/code/save";
	actions.historyUrl 		= "/code/history";
	
	actions.type = {
					idName:['{{config("constants.flowId")}}','{{config("constants.flFlowPhase")}}'],
					keyField:'{{config("constants.flowId")}}',
					saveKeyField : function (model){
						return '{{config("constants.flowIdColumn")}}';
					},
// 				,xIdName:'X_FL_ID'
					};

	actions.validating = function (reLoadParams){
		return true;
	}

	
	var aLoadNeighbor = actions.loadNeighbor;
	actions.loadNeighbor = function() {
		var activeTabID = getActiveTabID();
		$('.CodePlanType  , .CodeForecastType').css('display','none');
		if(activeTabID=='FlowDataPlan'){
			$('.CodePlanType').css('display','block');
		}
		else if(activeTabID=='FlowDataForecast'){
			$('.CodeForecastType').css('display','block');
		}
		aLoadNeighbor();
	}

	var aLoadParams = actions.loadParams;
	actions.loadParams = function(reLoadParams) {
		var pr = aLoadParams(reLoadParams);
		pr['CodePlanType']		= $('#CodePlanType').val();
		pr['CodeForecastType']	= $('#CodeForecastType').val();
		return pr;
	}
</script>
@stop
