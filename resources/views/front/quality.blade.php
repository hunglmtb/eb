<?php
	$currentSubmenu ='quality';
	$tables = ['QltyData'	=>['name'=>'QUALITY DATA']];
 	$active = 0;
?>

@extends('core.pm')
@section('funtionName')
QUALITY DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/quality/load";
	actions.saveUrl = "/quality/save";
	actions.type = {
					idName:['{{config("constants.qualityId")}}','{{config("constants.flFlowPhase")}}'],
					keyField:'{{config("constants.qualityId")}}',
					saveKeyField : function (model){
						return '{{config("constants.qualityIdColumn")}}';
					},
// 				,xIdName:'X_FL_ID'
					};
</script>
@stop
