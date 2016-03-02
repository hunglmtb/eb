<?php
	$filterGroup = [array(	'type' => 'options',
							'id' => 'cboProdUnit',
							'name' => 'Production Unit',
							'selectName' => 'cboProdUnit',
							'options'=>[array('value' => '9', 'name' => 'Test Server')]),
					array(	'type' => 'options',
							'id' => 'cboArea',
							'name' => 'Area',
							'selectName' => 'cboArea',
							'options'=>[array('value' => '7', 'name' => 'Amazing Basin')]),
					array(	'type' => 'options',
							'id' => 'Facility',
							'name' => 'Facility',
							'selectName' => 'Facility',
							'options'=>[array('value' => '7', 'name' => 'Amazing Basin')]),
	];
?>
@include('partials.filter',['filterGroup'=>$filterGroup])

