<?php
$subMenus = [array('title' => 'CONTRACT ADMIN', 'link' => ''),
		array('title' => 'CARGO ADMIN', 'link' => ''),
		array('title' => 'CARGO ACTION', 'link' => ''),
		array('title' => 'CARGO MANAGEMENT', 'link' => 'ticket'),
		array('title' => 'CARGO MONITORING', 'link' => '')
];
?>
@extends('core.bscontent',['subMenus' => $subMenus])
