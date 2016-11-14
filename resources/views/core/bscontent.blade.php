<?php
if (!isset($subMenus)) $subMenus = [];
if (!isset($active)) $active =1;
if (!isset($isAction)) $isAction =false;

?>
@extends('core.bsmain',['subMenus' => $subMenus])

@section('content')
	@include('partials.dataTableTab')
@stop

