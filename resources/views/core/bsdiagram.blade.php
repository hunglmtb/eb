<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$subMenus = [
		array('title' => 'NETWORK MODELS', 'link' => 'diagram'),
		array('title' => 'DATA VIEWS', 'link' => 'roles'),
		array('title' => 'REPORT', 'link' => 'audittrail'),
		array('title' => 'ADVANCED GRAPH', 'link' => 'validatedata'),
		array('title' => 'TASK MANAGER', 'link' => 'approvedata'),
		array('title' => 'WORKFLOW', 'link' => 'workflow')
];
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])
@section('script')
	<script type="text/javascript" src="/common/js/mxClient.js?3"></script>
	<script type="text/javascript" src="/common/js/utils"></script>
	<script type="text/javascript" src="/common/js/mxApplication.js?3"></script>
	<script src="/common/js/svgtopng.js"></script>
	<script src="/common/js/skinable_tabs.min.js"></script>
	<link rel="stylesheet" href="/common/css/diagram.css"/>
	<link rel="stylesheet" href="/common/css/common.css"/>
	<link rel="stylesheet" href="/common/css/styleTab.css"/>
@stop	
@section('main')

<div id="content">
		@yield('content')
</div>
@yield('adaptData')

@stop

