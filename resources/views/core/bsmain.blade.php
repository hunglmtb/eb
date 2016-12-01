<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$enableFilter	= isset($enableFilter)?$enableFilter:true;

$currentClass = $currentSubmenu;
if($currentClass!=''){
	$spits = explode("/", $currentClass);
	$currentClass = $spits[count($spits)-1];
}
?>

@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])
@section('ebfilter')
	@if($enableFilter)
		@include('group.production')
	@endif
@stop
@section('main')
<div class="rootMain {{$currentClass}}">
	<div id="functionName" style="padding:10px 10px 10px 0px;font-size:16pt;display:none">@yield('funtionName')</div>
	<div id="ebfilter" style="width:100%; clear:both">@yield('ebfilter')</div>
	<div id="mainContent" style="width:100%; clear:both">@yield('content')</div>
</div>
@yield('adaptData')
@stop

@section('script')
	<link rel="stylesheet" href="/common/css/jquery.dataTables.css"/>
	<link href="/common/css/bootstrap.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-responsive.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-datetimepicker.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-editable.css" rel="stylesheet"/>
	
	<link href="/jqueryui-editable/css/jqueryui-editable.css" rel="stylesheet"/>
	<link href="/common/css/fixedHeader.dataTables.min.css" rel="stylesheet"/>
<!-- 	<link href="/common/css/select.dataTables.min.css" rel="stylesheet"/>
 -->	
	<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
	<!-- <script src="/jqueryui-editable/js/jqueryui-editable.js"></script> -->
	<script src="/common/js/tableHeadFixer.js"></script>

<!-- 	<script src="/common/js/moment.js"></script>
 -->	
 	<script src="/common/js/bootstrap.js"></script>
	<script src="/common/js/bootstrap-datetimepicker.js"></script>
	<script src="/common/js/bootstrap-editable.js"></script>
	<script src="/common/js/eb.js"></script>
	
	<!-- <script src="/common/js/datetime.js"></script> -->
	
<!-- 	<script src="/common/js/dataTables.select.min.js"></script>
 -->	
@stop

@section('modalWindow')
	@include('core.history')
@stop
