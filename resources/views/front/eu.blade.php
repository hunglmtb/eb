<?php
	$currentSubmenu ='eu';
	$tables = ['FlowDataFdcValue'	=>['name'=>'FDC VALUE'],
			'FlowDataValue'		=>['name'=>'STD VALUE'],
			'FlowDataTheor'		=>['name'=>'THEORETICAL'],
			'FlowDataAlloc'		=>['name'=>'ALLOCATION'],
			'FlowCompDataAlloc'	=>['name'=>'COMPOSITION ALLOC'],
			'FlowDataPlan'		=>['name'=>'PLAN'],
			'FlowDataForecast'	=>['name'=>'FORECAST'],
	];
 	$active = 2;
?>
@extends('core.pm')

@section('funtionName')
ENERGY UNIT DATA CAPTURE
@stop
