
<?php
$facility = array('filterName'	=>'Facility',
				'name'			=>'facility',
				'dependences'	=>$filters['productionFilterGroup']);

if (isset($filters['extra'])) {
	$facility['extra'] = $filters['extra'];
}

$mapping = ['LoProductionUnit'		=> 	array('filterName'	=>'Production Unit',
											'name'			=>'productionUnit',
											'dependences'	=> array_merge(['LoArea','Facility'],$filters['productionFilterGroup']),
//  											'extra'			=>$filters['extra'],
										),
			'LoArea'				=>	array('filterName'	=>'Area',
											'name'			=>'area',
											'dependences'	=> array_merge(['Facility'],$filters['productionFilterGroup'])),
			'Facility'				=>	$facility
			];

$subMapping = config("constants.subProductFilterMapping");
$mapping = array_merge($mapping,$subMapping);

?>
<script type='text/javascript'>
var javascriptFilterGroups = <?php echo json_encode($filterGroups); ?>
</script>
<script src="/common/js/eb.js"></script>
<script>
$( document ).ready(function() {
    console.log( "ready!" );
    var onChangeFunction = function() {
    	if ($('#buttonLoadData').attr('value')=='Refresh') {
	    	actions.doLoad(true);
		}
    };
    
    $( "#date_begin" ).change(onChangeFunction);
    $( "#date_end" ).change(onChangeFunction);
});
</script>
<div id="ebFilters" style="height:auto">
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
		@if(!auth()->user()->hasRight('DATA_READONLY'))
			<input type="button" value="Save" name="B3" id = "buttonSave" onClick="actions.doSave(true)" style="width: 85px;foat:left; height: 26px">
		@endif
		<input type="button" value="Load data" id="buttonLoadData" name="B33"
			onClick="actions.doLoad(true)" style="width: 85px; height: 26px;foat:left;">
	</div>
</div>
