<?php
$subMenus = [
		array('title' => 'SAFETY', 'link' => 'safety'),
		array('title' => 'COMMENTS', 'link' => 'comments'),
		array('title' => 'EQUIPMENT', 'link' => 'equipment'),
		array('title' => 'CHEMICAL', 'link' => 'chemical'),
		array('title' => 'PERSONNEL', 'link' => 'personnel'),
		
];
?>
@extends('core.bsmain',['subMenus' => $subMenus])

@section('content')
@stop