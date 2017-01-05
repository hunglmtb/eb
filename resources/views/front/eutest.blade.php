<?php
	$currentSubmenu ='/dc/eutest';
	$tables = ['EuTestDataFdcValue'		=>['name'=>'FDC VALUE'],
			'EuTestDataStdValue'		=>['name'=>'STD VALUE'],
			'EuTestDataValue'			=>['name'=>'DAY VALUE'],
	];
 	$active = 1;
	$isAction = true;
?>

@extends('core.pm')
@section('funtionName')
WELL TEST DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/eutest/load";
	actions.saveUrl 		= "/eutest/save";
	actions.historyUrl 		= "/eutest/history";
	actions.reloadAfterSave	= true;
	
	actions.type = {
					idName:['EU_ID','ID','BEGIN_TIME','END_TIME','EFFECTIVE_DATE'],
					keyField:'ID',
					saveKeyField : function (model){
										return 'ID';
									},
					};
	addingOptions.keepColumns = ['BEGIN_TIME','END_TIME','EFFECTIVE_DATE'];
	actions.validating = function (reLoadParams){
		return true;
	}
</script>
@stop