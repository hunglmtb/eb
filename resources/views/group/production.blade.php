
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
											'model'			=>'CodeReadingFrequency',
											'default'		=>['value'=>'','name'=>'All']),
			'CodeFlowPhase'			=>	array('filterName'	=>'Phase Type',
											'name'			=>'phaseType',
											'model'			=>'CodeFlowPhase',
											'default'		=>['value'=>'','name'=>'All']),
			];
?>
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
				{{ Helper::filter($mapping[$filter]) }}
			@endforeach
		</div>
		@endif
 @endforeach

<div class="action_filter">

	<input type="button" value="Save" name="B3" id = "buttonSave" style="width: 85px;foat:left; height: 26px">
	<input type="button" value="Load data" id="buttonLoadData" name="B33"
		style="width: 85px; height: 26px;foat:left;">
</div>
