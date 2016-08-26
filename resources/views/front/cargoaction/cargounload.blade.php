<?php
	$currentSubmenu ='/pd/cargounload';
	$tables = ['PdCargoUnload'	=>['name'=>'Unload']];
	$isLoad = 0;
?>

@extends('core.cargoaction')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargounload/load";
	actions.saveUrl = "/cargounload/save";
	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="PdCargoUnload";
	};
</script>
@stop
@section('editBoxParams')
@parent
<script>
 	editBox.loadUrl = "/timesheet/unload";
</script>
@stop