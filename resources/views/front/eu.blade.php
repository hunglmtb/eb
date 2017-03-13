<?php
	$currentSubmenu ='/dc/eu';
	$tables = ['EnergyUnitDataFdcValue'	=> ['name'=>'FDC Value'],
			'EnergyUnitDataValue'		=> ['name'=>'STD Value'],
			'EnergyUnitDataTheor'		=> ['name'=>"Theoretical"],       
			'EnergyUnitDataAlloc'		=> ['name'=>"Allocation"],          
			'EnergyUnitCompDataAlloc'	=> ['name'=>"Composition Alloc"], 
			'EnergyUnitDataPlan'		=> ['name'=>"Plan"],                            
			'EnergyUnitDataForecast'	=> ['name'=>"Forecast"],                
	];
 	$active = 1;
?>

@extends('core.pm')
@section('funtionName')
ENERGY UNIT DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/eu/load";
	actions.saveUrl 		= "/eu/save";
	actions.historyUrl 		= "/eu/history";
	
	actions.type = {
					idName:['{{config("constants.euId")}}','{{config("constants.euFlowPhase")}}','{{config("constants.eventType")}}'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
						return '{{config("constants.euPhaseConfigId")}}';
					},
// 					xIdName:'X_EU_ID'
					};
	actions.validating = function (reLoadParams){
		return true;
	}
	
	var aLoadNeighbor = actions.loadNeighbor;
	actions.loadNeighbor = function() {
		var activeTabID = getActiveTabID();
		$('.CodePlanType , .CodeAllocType , .CodeForecastType').css('display','none');
		if(activeTabID=='EnergyUnitDataAlloc'||activeTabID=='EnergyUnitCompDataAlloc'){
			$('.CodeAllocType').css('display','block');
		}
		else if(activeTabID=='EnergyUnitDataPlan'){
			$('.CodePlanType').css('display','block');
		}
		else if(activeTabID=='EnergyUnitDataForecast'){
			$('.CodeForecastType').css('display','block');
		}
		aLoadNeighbor();
	}


	var aLoadParams = actions.loadParams;
	actions.loadParams = function(reLoadParams) {
		var pr = aLoadParams(reLoadParams);
		pr['CodeAllocType']		= $('#CodeAllocType').val();
		pr['CodePlanType']		= $('#CodePlanType').val();
		pr['CodeForecastType']	= $('#CodeForecastType').val();
		return pr;
	}

	actions.similarCells	= ['ACTIVE_HRS',
							   'CHOKE_SETTING',
							   'OBS_TEMP',
							   'OBS_PRESS',
							   'EU_DATA_AVG_WHT',
							   'EU_DATA_AVG_WHP',
							   'EU_DATA_AVG_BHT',
							   'EU_DATA_AVG_BHP',
							   'EU_DATA_AVG_FTP',
							   'EU_DATA_AVG_SITP'
							   ];
	actions.getSimilarCells = function(property,tab, td, rowData, columnName,collection,type) {
		var similarCells = [];
		if(jQuery.inArray( columnName, actions.similarCells ) >= 0 ){
			var table 		= $('#table_'+tab).DataTable();
			var tableData 	= table.data();
			var result = 	$.grep(tableData, function(item){ 
			              		return item.X_EU_ID == rowData.X_EU_ID
			              		&& item.EU_CONFIG_EVENT_TYPE == rowData.EU_CONFIG_EVENT_TYPE;
			            	});
			if (result.length > 0) {
				$.each(result, function( index, value ) {
					similarCells.push({
										td			: td,
										rowData		: value,
										collection	: collection,
						});
	             });
			}
		}
			
		return similarCells;
	}
	
	
</script>
@stop