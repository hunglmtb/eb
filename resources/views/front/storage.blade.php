<?php
	$currentSubmenu ='storage';
	$tables = ['TankDataFdcValue'		=>['name'=>'TANK FDC'],
				'TankDataValue'			=>['name'=>'TANK VALUE'],
				'TankDataPlan'			=>['name'=>'TANK PLAN'],
				'TankDataForecast'		=>['name'=>'TANK FORECAST'],
				'StorageDataValue'		=>['name'=>'STORAGE VALUE'],
				'StorageDataPlan'		=>['name'=>'STORAGE PLAN'],
				'StorageDataForecast'	=>['name'=>'STORAGE FORECAST'],
	];
// 	$active = 3;
?>

@extends('core.pm')
@section('funtionName')
TANK & STORAGE DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/code/load-storage";
	actions.saveUrl = "/code/save-storage";
	actions.type = {
					idName:['{{config("constants.flowId")}}','{{config("constants.flFlowPhase")}}'],
					keyField:'{{config("constants.flowId")}}',
					saveKeyField:'{{config("constants.flowIdColumn")}}'
// 				,xIdName:'X_FL_ID'
					};
</script>
@stop
