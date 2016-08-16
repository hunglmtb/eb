<?php
	$currentSubmenu ='/cargodocuments';
	$tables = ['PdCargoDocument'	=>['name'=>'Load']];
	$isAction = true;
?>

@extends('core.pd')
@section('funtionName')
CARGO DOCUMENTS
@stop

@section('adaptData')
@parent
<script>

	actions.loadUrl = "/cargodocuments/load";
	actions.saveUrl = "/cargodocuments/save";
	actions.type = {
					idName:['ID'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
						return 'ID';
					},
				};

	actions.isDisableAddingButton	= function (tab,table) {
		return true;
	};
</script>
@stop