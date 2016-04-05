<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])

@section('main')
<link rel="stylesheet" href="/common/css/admin.css">
<script type="text/javascript" src="/common/js/utils.js"></script>
<div id="content">
	<div class="title">
		@yield('title')
	</div>
		@yield('group')		

		@yield('content')
</div>
@yield('adaptData')

@stop

