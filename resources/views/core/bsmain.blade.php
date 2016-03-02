<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])

@section('main')
<div style="padding-left:10px">
	<div style="padding:10px 10px 10px 0px;font-size:16pt;">FLOW DATA CAPTURE</div>
	<form name="form_fdc" id="form_fdc" action="saveeufdc.php" method="POST"> 
		<input name="fields_fdc" value="" type="hidden">
		<input name="fields_value" value="" type="hidden">
		<input name="fields_theor" value="" type="hidden">
		<input name="fields_alloc" value="" type="hidden">
		<input name="fields_plan" value="" type="hidden">
		<input name="fields_forecast" value="" type="hidden">
		@include('partials.group',['groups'=>$groups])
		<br>
		@yield('content') 
	</form>
</div>
@stop