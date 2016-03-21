<?php
	$currentSubmenu ='flow';
	$tables = ['FlowDataFdcValue'	=>['name'=>'FDC VALUE'],
				'FlowDataValue'		=>['name'=>'STD VALUE'],
				'FlowDataTheor'		=>['name'=>'THEORETICAL'],
				'FlowDataAlloc'		=>['name'=>'ALLOCATION'],
				'FlowCompDataAlloc'	=>['name'=>'COMPOSITION ALLOC'],
				'FlowDataPlan'		=>['name'=>'PLAN'],
				'FlowDataForecast'	=>['name'=>'FORECAST'],
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
	actions.loadUrl = "/code/load";
</script>
@stop
