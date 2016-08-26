<?php
	$currentSubmenu ='/pd/cargoload';
	$tables = ['PdCargoLoad'	=>['name'=>'Load']];
	$isLoad = 1;
?>

@extends('core.cargoaction')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargoload/load";
	actions.saveUrl = "/cargoload/save";
	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="PdCargoLoad";
	};	 	
</script>
@stop


@section('editBoxParams')
@parent
<script>
 	editBox.loadUrl = "/timesheet/load";
</script>
@stop