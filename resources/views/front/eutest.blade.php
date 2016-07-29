<?php
	$currentSubmenu ='/dc/eutest';
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
	actions.loadUrl 		= "/eutest/load";
	actions.saveUrl 		= "/eutest/save";
	actions.historyUrl 		= "/eutest/history";
	
	actions.type = {
					idName:['ID','EU_ID','BEGIN_TIME','END_TIME','EFFECTIVE_DATE'],
					keyField:'ID',
					saveKeyField : function (model){
										return 'ID';
									},
					};
//  	addingOptions.keepColumns = ['SAMPLE_DATE','TEST_DATE','EFFECTIVE_DATE','PRODUCT_TYPE','SRC_ID','SRC_TYPE'];
		addingOptions.keepColumns = ['BEGIN_TIME','END_TIME','EFFECTIVE_DATE'];
	
</script>
@stop