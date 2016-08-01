<?php
$subMenus = [array('title' => 'CONTRACT ADMIN', 	'link' => ''),
			array('title' => 'CARGO ADMIN', 		'link' => 'cargoentry'),
			array('title' => 'CARGO NOMINATION', 	'link' => 'cargonomination'),
			array('title' => 'CARGO SCHEDULE', 		'link' => 'cargoschedule'),
			array('title' => 'CARGO ACTION', 		'link' => ''),
			array('title' => 'DEMURRAGE/EBO', 	'link' => 'demurrageebo'),
			array('title' => 'CARGO DOCUMENTS', 	'link' => 'cargodocuments'),
			array('title' => 'CARGO MONITORING', 	'link' => '')
];
?>
@extends('core.bscontent',['subMenus' => $subMenus])

@if(isset($isAction)&&$isAction)
	@section('adaptData')
	@parent
	<script src="/common/js/eb_table_action.js"></script>
	@stop
	
	@section('floatWindow')
		@yield('editBox')
		@include('core.float_window')
	@stop
@endif
