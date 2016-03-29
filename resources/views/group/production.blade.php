
<?php
$mapping = ['LoProductionUnit'		=> 	array('filterName'	=>'Production Unit',
											'name'			=>'productionUnit',
											'dependences'	=> array_merge(['LoArea','Facility'],$filters['productionFilterGroup'])),
			'LoArea'					=>	array('filterName'	=>'Area',
											'name'			=>'area',
											'dependences'	=> array_merge(['Facility'],$filters['productionFilterGroup'])),
			'Facility'				=>	array('filterName'	=>'Facility',
											'name'			=>'facility',
											'dependences'	=>$filters['productionFilterGroup']),
			'Tank'					=>	array('filterName'	=>'Tank',
											'name'			=>'tank'),
			'EnergyUnitGroup'		=>	array('filterName'	=>'Energy Unit Group',
											'name'			=>'energyUnitGroup',
											'default'		=>['value'=>'','name'=>'No Group']),
			'CodeReadingFrequency'	=>	array('filterName'	=>'Record Frequency',
											'name'			=>'CodeReadingFrequency',
											'id'			=>'CodeReadingFrequency',
											'default'		=>['value'=>0,'name'=>'All']),
			'CodeFlowPhase'			=>	array('filterName'	=>'Phase Type',
											'name'			=>'phaseType',
											'id'			=>'CodeFlowPhase',
											'default'		=>['value'=>0,'name'=>'All']),
			];
?>
<script type='text/javascript'>
var javascriptFilterGroups = <?php echo json_encode($filterGroups); ?>
</script>
<script src="/common/js/eb.js"></script>
@foreach( $filterGroups as $key => $filters )
		@if($key=='productionFilterGroup')
		<div class = "product_filter">
			@foreach( $filters as $filter )
				{{ Helper::buildFilter(array_merge($filter, $mapping[$filter['id']])) }}
			@endforeach
		</div>
		@elseif($key=='dateFilterGroup')
		<div class = "date_filter">
			@foreach( $filters as $filter )
				{{ Helper::selectDate($filter)}}
			@endforeach
		</div>
		@elseif($key=='frequenceFilterGroup')
		<div class = "product_filter">
			@foreach( $filters as $filter )
				{{ Helper::filter(array_merge($filter, $mapping[$filter['id']])) }}
			@endforeach
		</div>
		@endif
 @endforeach

<div class="action_filter">

	<input type="button" value="Save" name="B3" id = "buttonSave" onClick="actions.doSave(true)" style="width: 85px;foat:left; height: 26px">
	<input type="button" value="Load data" id="buttonLoadData" name="B33"
		onClick="actions.doLoad(true)" style="width: 85px; height: 26px;foat:left;">
</div>
