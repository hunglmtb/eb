<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])
@section('main')
<div class="rootMain {{$currentSubmenu}}">
	<div style="padding:10px 10px 10px 0px;font-size:16pt;">@yield('funtionName')</div>
	@include('group.production')
	<br>
	@yield('content')
</div>
@yield('adaptData')
@stop

@section('script')
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

	<script src="/common/js/moment.js"></script>
	<script src="/common/js/bootstrap.js"></script>
	<script src="/common/js/bootstrap-datetimepicker.js"></script>
	<script src="/common/js/bootstrap-editable.js"></script>
	
<!-- 	<script src="/common/js/dataTables.select.min.js"></script>
 -->	
@stop

