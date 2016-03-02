<?php
	$filterGroup = [array(	'type' => 'options',
					'id' => 'Frequency',
					'name' => 'Record Frequency',
					'selectName' => 'Frequency',
					'options'=>[array('value' => '7', 'name' => 'Amazing Basin')])];
	
?>
@include('partials.filter',['filterGroup'=>$filterGroup])

