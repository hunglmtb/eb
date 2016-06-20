<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$subMenus = [
		array('title' => 'FIELDS CONFIG', 'link' => 'diagram'),
		array('title' => 'TABLES DATA', 'link' => 'roles'),
		array('title' => 'PD TABLES', 'link' => 'workreport'),
		array('title' => 'TAGS MAPPING', 'link' => 'graph'),
		array('title' => 'FORMULA EDITOR', 'link' => 'approvedata'),
		array('title' => 'VIEW CONFIG', 'link' => 'viewconfig')
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

