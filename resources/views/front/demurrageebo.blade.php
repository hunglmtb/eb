<?php
	$currentSubmenu ='demurragreebo';
	$tables = ['Demurrage'	=>['name'=>'Load']];
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