<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$subMenus = [
		array('title' => 'NETWORK MODELS', 'link' => 'diagram'),
		array('title' => 'DATA VIEWS', 'link' => 'dataview'),
		array('title' => 'REPORT', 'link' => 'workreport'),
		array('title' => 'ADVANCED GRAPH', 'link' => 'graph'),
		array('title' => 'TASK MANAGER', 'link' => 'approvedata'),
		array('title' => 'WORKFLOW', 'link' => 'workflow')
];
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])
@section('script')
	<script type="text/javascript" src="/common/js/mxClient.js"></script>
	<script type="text/javascript" src="/common/js/utils.js"></script>
	<script type="text/javascript" src="/common/js/mxApplication.js?3"></script>
	<script src="/common/js/svgtopng.js"></script>
	<script src="/common/js/skinable_tabs.min.js"></script>
	<link rel="stylesheet" href="/common/css/diagram.css"/>
	<link rel="stylesheet" href="/common/css/common.css"/>
	<link rel="stylesheet" href="/common/css/styleTab.css"/>
@stop	
@section('main')

<div id="content">
	<div class="title">
		@yield('title')
	</div>
	@yield('group')		

	@yield('content')
</div>
@yield('adaptData')

@stop

