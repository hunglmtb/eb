<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])

@section('main')
<div style="padding-left:10px">
	<div style="padding:10px 10px 10px 0px;font-size:16pt;">@yield('funtionName')</div>
	@include('group.production')
	<br>
	@yield('content')
</div>
@yield('adaptData')
@stop

@section('script')
	<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
@stop

