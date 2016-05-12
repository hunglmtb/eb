<?php
	$currentSubmenu ='eutest';
	$tables = ['EuTestDataFdcValue'		=>['name'=>'FDC VALUE'],
			'EuTestDataStdValue'		=>['name'=>'STD VALUE'],
			'EuTestDataValue'			=>['name'=>'DAY VALUE'],
	];
 	$active = 1;
?>

@extends('core.pm')
@section('funtionName')
WELL TEST DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/eutest/load";
	actions.saveUrl = "/eutest/save";
	actions.type = {
					idName:['{{config("constants.euId")}}','{{config("constants.euFlowPhase")}}'],
					keyField:'ID',
					saveKeyField : function (model){
						return '{{config("constants.euPhaseConfigId")}}';
					},
// 					xIdName:'X_EU_ID'
					};
	var aLoadNeighbor = actions.loadNeighbor;
	actions.loadNeighbor = function() {
		var activeTabID = getActiveTabID();
		if(activeTabID=='EnergyUnitDataAlloc'||activeTabID=='EnergyUnitCompDataAlloc'){
			$('.CodeAllocType').css('display','block');
		}
		else{
			$('.CodeAllocType').css('display','none');
		}
		aLoadNeighbor();
	}


	var aLoadParams = actions.loadParams;
	actions.loadParams = function(reLoadParams) {
		var pr = aLoadParams(reLoadParams);
		var activeTabID = getActiveTabID();
		if(activeTabID=='EnergyUnitDataAlloc'){
			pr['CodeAllocType']= $('#CodeAllocType').val();
		}
		else{
			pr['CodeAllocType']= 0;
		}
		return pr;
	}
	
</script>
@stop