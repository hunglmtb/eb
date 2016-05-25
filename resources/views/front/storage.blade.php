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
	var tankIdColumn = '{{config("constants.tankIdColumn")}}';
	var storageIdColumn = '{{config("constants.storageIdColumn")}}';
	
	var saveKeyFields =  {TankDataFdcValue	 : tankIdColumn,
							TankDataValue : tankIdColumn,
							TankDataPlan : tankIdColumn,
							TankDataForecast : tankIdColumn,
							StorageDataValue : storageIdColumn,
							StorageDataPlan : storageIdColumn,
							StorageDataForecast : storageIdColumn,
						};
	actions.loadUrl = "/storage/load";
	actions.saveUrl = "/storage/save";
	actions.type = {
					idName:['{{config("constants.tankId")}}','{{config("constants.tankFlowPhase")}}'],
					keyField:'{{config("constants.tankId")}}',
					saveKeyField : function (model){
						return saveKeyFields[model];
					},
// 				,xIdName:'X_FL_ID'
					};
</script>
@stop
