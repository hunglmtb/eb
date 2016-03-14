<?php
$subMenus = [array('title' => 'COMMENTS', 'link' => 'flow'),
		array('title' => 'EQUIPMENT', 'link' => 'eu'),
		array('title' => 'CHEMICAL', 'link' => 'storage'),
		array('title' => 'PERSONNEL', 'link' => 'storage'),
		
];
?>
@extends('core.bsmain',['subMenus' => $subMenus])

@section('content')
@stop