<?php
	$currentSubmenu ='flow';
	$groups = [array('name' => 'group.date','data' => 'Date'),
				array('name' => 'group.production','data' => 'data'),
				array('name' => 'group.frequency','data' => 'frequency')
				];
?>

@extends('core.pm')
@section('funtionName')
FLOW DATA CAPTURE
@stop

@section('adaptData')
<script>
actions.url = "keke";
</script>
@stop
