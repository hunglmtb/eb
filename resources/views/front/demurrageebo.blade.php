<?php
	$currentSubmenu ='demurragreebo';
	$tables = ['demurrage'	=>['name'=>'Load']];
?>

@extends('core.pd')
@section('funtionName')
DEMURRAGE/EBO
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/demurragreebo/load";
	actions.saveUrl = "/demurragreebo/save";
	actions.type = {
					idName:['CREATED_DATE','FACILITY_ID','X_CATEGORY_ID'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
						return 'CATEGORY_ID';
					},
				};
</script>
@stop