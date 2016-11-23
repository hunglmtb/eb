<?php
if (!isset($filters['extra'])) {
	$filters['extra'] = [];
}
$functionName		= isset($functionName)?$functionName:"";
$enableButton 		= isset($filterGroups['enableButton'])?	$filterGroups['enableButton']	:true;
$enableSaveButton 	= isset($filters['enableSaveButton'])?	$filters['enableSaveButton']	:true;

if (array_key_exists('productionFilterGroup', $filters)) {
	$dependences = isset($filters['FacilityDependentMore'])?
					array_merge($filters['FacilityDependentMore'],$filters['productionFilterGroup'])
					:$filters['productionFilterGroup'];
	$mapping = ['LoProductionUnit'		=> 	array('filterName'	=>'Production Unit',
												'name'			=>'LoProductionUnit',
												'dependences'	=> array_merge(['LoArea','Facility'],$dependences),
	  											'extra'			=>$filters['extra'],
											),
				'LoArea'				=>	array('filterName'	=>'Area',
												'name'			=>'LoArea',
												'dependences'	=> array_merge(['Facility'],$dependences),
	  											'extra'			=>$filters['extra'],
											),
				'Facility'				=>	array('filterName'	=>'Facility',
												'name'			=>'Facility',
												'dependences'	=>$dependences,
	  											'extra'			=>$filters['extra'],
											)
				];
	
	$subMapping = config("constants.subProductFilterMapping");
	$mapping = array_merge($mapping,$subMapping);
}
else{
	$mapping = config("constants.subProductFilterMapping");
}

?>

<script type='text/javascript'>
if (typeof actions == "undefined") {
	 document.write('<script type="text/javascript" src="'
			    + '/common/js/eb.js' + '"></scr' + 'ipt>'); 
}
var javascriptFilterGroups = <?php echo json_encode($filterGroups); ?>
</script>
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
<div id="ebFilters_{{$functionName}}" class="{{$functionName}}" style="height:auto">
	@foreach( $filterGroups as $key => $filters )
			@if($key=='productionFilterGroup')
			<div class = "product_filter">
				@foreach( $filters as $filter )
					{{ Helper::buildFilter(array_merge($mapping[$filter['modelName']],$filter)) }}
				@endforeach
			</div>
			@elseif($key=='dateFilterGroup')
			<div class = "date_filter">
				@foreach( $filters as $filter )
					{{ Helper::selectDate($filter)}}
				@endforeach
			</div>
			@elseif($key=='frequenceFilterGroup')
			<div id = "filterFrequence" class = "product_filter">
				@foreach( $filters as $filter )
					{{ Helper::filter(array_key_exists($filter['modelName'], $mapping)?array_merge($mapping[$filter['modelName']],$filter):$filter) }}
				@endforeach
				@yield('frequenceFilterGroupMore')
			</div>
			@endif
	@endforeach
	@if($enableButton)
		<div class="action_filter">
			@if(!auth()->user()->hasRight('DATA_READONLY')&&$enableSaveButton)
				<input type="button" value="Save" name="B3" id = "buttonSave" onClick="actions.doSave(true)" style="width: 85px;foat:left; height: 26px">
			@endif
			<input type="button" value="Load data" id="buttonLoadData" name="B33"
				onClick="actions.doLoad(true)" style="width: 85px; height: 26px;foat:left;">
		</div>
	@endif
	@yield('action_extra')
</div>
