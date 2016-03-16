<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])

@section('main')
<div style="padding-left:10px">
	<div style="padding:10px 10px 10px 0px;font-size:16pt;">@yield('funtionName')</div>
	<form name="form_fdc" id="form_fdc" action="saveeufdc.php" method="POST"> 
		<input name="fields_fdc" value="" type="hidden">
		<input name="fields_value" value="" type="hidden">
		<input name="fields_theor" value="" type="hidden">
		<input name="fields_alloc" value="" type="hidden">
		<input name="fields_plan" value="" type="hidden">
		<input name="fields_forecast" value="" type="hidden">
		@include('group.production')
		<br>
		@yield('content')
	</form>
</div>
@yield('adaptData')
@stop

@section('script')
	<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
	
	<script>
		$(document).ready(function () {
// 	 	    $("#tabs").tabs();
			$("#tabs").tabs({active:1});
	
		});
	</script>
@stop

