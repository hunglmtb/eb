<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$subMenus = [
		array('title' => 'RUN ALLOCATION', 'link' => 'run_allocation'),
		array('title' => 'CONFIG ALLOCATION', 'link' => 'config_allocation')
];
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])
@section('script')
	<link rel="stylesheet" href="/common/css/common.css"/>
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

