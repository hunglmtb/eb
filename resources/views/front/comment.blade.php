<?php
$currentSubmenu ='comment';
$tables = ['Comment'	=>['name'=>'Comment']];
?>
@extends('core.fo_action')

@section('funtionName')
COMMENT DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/comment/load";
	actions.saveUrl = "/comment/save";
	actions.type = {
					idName:['ID'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
					},
				};
</script>
@stop
