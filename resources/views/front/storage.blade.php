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
	actions.loadUrl = "/storage/load";
	actions.saveUrl = "/storage/save";
	actions.type = {
					idName:['{{config("constants.tankId")}}'],
					keyField:'{{config("constants.tankId")}}',
					saveKeyField:'{{config("constants.tankIdColumn")}}'
// 				,xIdName:'X_FL_ID'
					};
</script>
@stop
