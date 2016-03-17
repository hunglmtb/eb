<?php
$subMenus = [
		array('title' => 'USERS', 'link' => 'users'),
		array('title' => 'ROLES', 'link' => 'roles'),
		array('title' => 'AUDITTRAIL', 'link' => 'audittrail'),
		array('title' => 'VALIDATEDATA', 'link' => 'validatedata'),
		array('title' => 'APPROVEDATA', 'link' => 'approvedata'),
		array('title' => 'LOCKDATA', 'link' => 'lockdata'),
		array('title' => 'USERSLOG', 'link' => 'userslog')		
];
?>
@extends('core.bsmain',['subMenus' => $subMenus])

@section('content')
@stop