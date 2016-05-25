<?php
	$currentSubmenu ='quality';
	$tables = ['QltyData'	=>['name'=>'QUALITY DATA']];
 	$active = 0;
?>

@extends('core.pm')
@section('funtionName')
QUALITY DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/quality/load";
	actions.saveUrl = "/quality/save";
	actions.type = {
					idName:['{{config("constants.qualityId")}}','{{config("constants.flFlowPhase")}}'],
					keyField:'{{config("constants.qualityId")}}',
					saveKeyField : function (model){
						return '{{config("constants.qualityIdColumn")}}';
					},
// 				,xIdName:'X_FL_ID'
					};
	actions.extraDataSetColumns = {'SRC_ID':'SRC_TYPE'};
	actions.dominoColumns = function(columnName,newValue,tab,rowData,collection){
		if(columnName=='SRC_TYPE') {
			postData = actions.loadedData[tab];
			var srcType = null;
			var result = $.grep(collection, function(e){ 
	          	 return e['ID'] == newValue;
	           });
			if (result.length > 0) {
				srcType = result[0]['CODE'];
			}
			else return;
	
			srcData = {name : columnName,
						value : newValue,
						srcType : srcType,
						Facility : postData['Facility']};
			$.ajax({
				url: "/quality/loadsrc",
				type: "post",
				data: srcData,
				success:function(data){
	// 				rowData[]
					collection = data.dataSet;
					var table = $('#table_'+tab).DataTable();
					dependenceColumnName = 'SRC_ID';
					rowData[dependenceColumnName] = '';
					table.row( '#'+rowData['DT_RowId'] ).data(rowData);
					table.draw(false);
					td = ;
					col = ;
	 				actions.applyEditable(tab,'select',td, 'cellData', rowData, 0, col,collection);
					
					console.log ( "success:function dominoColumns "+data );
				},
				error: function(data) {
					console.log ( "error dominoColumns "+data );
				}
			});
		}
			
	};
	
	
</script>
@stop
