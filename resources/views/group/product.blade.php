<?php
	/*$filterGroup = [array(	'type' => 'options',
							'id' => 'cboProdUnit',
							'name' => 'Production Unit',
							'selectName' => 'cboProdUnit',
							'tableName' => 'LO_PRODUCTION_UNIT',
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
							 */
?>
<div class = "product_filter">
{{ Helper::filter('App\Models\LoProductionUnit','Production Unit') }}
{{ Helper::filter('App\Models\LoArea','Area') }}
{{ Helper::filter('App\Models\Facility','Facility') }}
</div>
