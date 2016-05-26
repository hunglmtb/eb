<?php
	$currentSubmenu ='eutest';
	$tables = ['EuTestDataFdcValue'		=>['name'=>'FDC VALUE'],
			'EuTestDataStdValue'		=>['name'=>'STD VALUE'],
			'EuTestDataValue'			=>['name'=>'DAY VALUE'],
	];
 	$active = 1;
?>

@extends('core.action')
@section('funtionName')
WELL TEST DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/eutest/load";
	actions.saveUrl = "/eutest/save";
	actions.type = {
					idName:['ID'],
					keyField:'ID',
					saveKeyField : function (model){
										return 'ID';
									},
					};
</script>
@stop