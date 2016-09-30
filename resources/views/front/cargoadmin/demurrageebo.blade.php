<?php
	$currentSubmenu ='/pd/demurrageebo';
	$tables = ['PdDemurageEbo'	=>['name'=>'Load']];
	
	$isAction = false;
?>

@extends('core.pd')
@section('funtionName')
DEMURRAGE/EBO
@stop

@section('adaptData')
@parent
<script>

	actions.loadUrl = "/demurragreebo/load";
	actions.saveUrl = "/demurragreebo/save";
	actions.type = {
					idName:['ID'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
						return 'ID';
					},
				};

	source['ACTIVITY_NAME']	={	dependenceColumnName	:	['START_TIME', 'END_TIME', 'ELAPSE_TIME'],
			url						: 	'/demurragreebo/loadsrc'
		};

	source.initRequest = function(tab,columnName,newValue,collection, rowData){
		postData = actions.loadedData[tab];
		srcData = {						
 					row_id : rowData['DT_RowId']
				};
		return srcData;
	}

	actions.dominoColumnSuccess = function(_data, dependenceColumnNames, rowData, tab){
		var data = _data.dataSet;

		var table = $('#table_Demurrage').DataTable();

		rowData['START_TIME'] = data['START_TIME'];
		rowData['END_TIME'] = data['END_TIME'];
		rowData['ELAPSE_TIME'] = data['ELAPSE_TIME'];

    	row = table.row( '#'+rowData['DT_RowId']);
		row.data(rowData).draw(false);
			
	};

	actions.isDisableAddingButton	= function (tab,table) {
		return true;
	};

	$(function(){
		var ebtoken = $('meta[name="_token"]').attr('content');
		$.ajaxSetup({
			headers: {
				'X-XSRF-Token': ebtoken
			}
		});
		
		$('#buttonSave').prop('onclick',null).off('click');

		$('#buttonSave').click( function () { 

			var table = $('#table_Demurrage').dataTable();
			var xx = table.api().data();

			var sData = [];	
			for(var i = 0; i < xx.length; i++){
				sData.push(xx[i]); 
			}
			
		 	param = {
					'data' : sData
			}
			
			sendAjax('/demurragreebo/save', param, function(data){
				if(data == 'ok'){
					alert("Save successfully");
				}else{
					alert(data);
				}
			}); 
		});
	});
</script>
@stop