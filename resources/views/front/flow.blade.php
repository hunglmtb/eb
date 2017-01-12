<?php
	$currentSubmenu ='/dc/flow';
	$lang			= session()->get('locale', "en");
	$tables = ['FlowDataFdcValue'	=>['name'=>'FDC Value'],
				'FlowDataValue'		=>['name'=>'STD Value'],
				'FlowDataTheor'		=>['name'=>Lang::has("front/site.Theoretical", $lang)?trans("front/site.Theoretical"):"Theoretical"],
				'FlowDataAlloc'		=>['name'=>Lang::has("front/site.Allocation", $lang)?trans("front/site.Allocation"):"Allocation"],
				'FlowCompDataAlloc'	=>['name'=>Lang::has("front/site.Composition", $lang)?trans("front/site.Composition"):"Composition Alloc"],
				'FlowDataPlan'		=>['name'=>Lang::has("front/site.Plan", $lang)?trans("front/site.Plan"):"Plan"],
				'FlowDataForecast'	=>['name'=>Lang::has("front/site.Forecast", $lang)?trans("front/site.Forecast"):"Forecast"],
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
