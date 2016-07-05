<?php
	$currentSubmenu =	'preos';
	$key 			= 	'preos';
	/* $tables = ['EnergyUnitDataFdcValue'	=>['name'=>'FDC VALUE'],
	]; */
 	$active = 1;
 	$f_date_from 	= array('id'=>'f_date_from','name'=>'Forecast date from');
 	$f_date_to 		= array('id'=>'f_date_to','name'=>'to');
 ?>

@extends('core.fp')
@section('funtionName')
PENG-ROBINSON EQUATION OF STATE
@stop

@section('action_extra')
<div class="action_filter">
	<input type="button" value="Add" id="buttonAdd" name="buttonAdd" onClick="addObject()" style="width: 85px; height: 26px;foat:left;">
</div>
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/preos/load";
	actions.saveUrl = "/preos/run";
	actions.type = {
					idName:['ID'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
							return 'ID';
						},
					};

	var objs="";

	function addObject()
 	{
 		var id=$("").val();
 		var s='<span style="display:block;margin:1px 0px" info="'+
 		$("#IntObjectType option:selected").attr('name')+
 		':'+
 		$("#ObjectName").val()+
 		':'+
 		$("#ObjectName option:selected").text()+
 		'">'+$("#IntObjectType option:selected").text()+
 		':'+
 		$("#ObjectName option:selected").text()+
 		' <img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="../img/x.png">';
 		
 		$("#selected_objects").append(s);
 	}
 	
	actions.initData = function(){

		var tab = {'{{config("constants.tabTable")}}':'{{$key}}',
					'objs':objs
				};
		if(!jQuery.isEmptyObject(actions.editedData)){
			var table = $('#table_forecast').dataTable();
			tbdata = table.api().data();
			txt_modify_data = '';
			$.each(tbdata, function( index, value ) {
				txt_modify_data+= value.T+','+value.V+'\n';
             });
			tab['forecast'] = txt_modify_data;
		}
		return tab;
	}

	actions.loadValidating = function (reLoadParams){
		objs = "";
		$('#selected_objects span').each(function(){
			objs+=(objs==""?"":";")+$(this).attr("info");
		});
		if(objs==""){
			alert("Please add object");
			return false;
		}
		$("#result").css("display","none");
		$('#container_{{$key}}').html('<table border="0" cellpadding="3" id="table_{{$key}}" class="fixedtable nowrap display"></table>');
		return true;
	}


	//----------------------------------------------------------------------------------------
	actions.getNumberRender = function (columnName,data,cellData, type2, row) {
		return cellData;
	}

	actions.renderFirsColumn = null;
	actions.getTableOption	= function(data){
		return {tableOption :	{searching: false,
								ordering: false
								},
				invisible:[]};
		
	}

	
	
	actions.validating = function (reLoadParams){
		objs = "";
		$('#selected_objects span').each(function(){
			objs+=(objs==""?"":";")+$(this).attr("info");
		});
		if(objs==""){
			alert("Please add object");
			return false;
		}
		$("#result").css("display","none");
		return true;
	}

	$("#result").css("display","none");

	actions.saveSuccess =  function(data){
		actions.editedData = {};
		actions.deleteData = {};
		$("#result_data").html(data.data);
		$("#result_time").html(data.time);
		$("#result_params").html(data.params);
		$("#result_warning").html(data.warning);
		$("#result_error").html(data.error);

		resultHtml = '';
		$.each(data.result, function( index, value ) {
			resultHtml+= value.value+', '+value.date+'<br/>';
			if(value.hasOwnProperty('sql')&&value.sql!=null) {
				resultHtml+= 'sql: '+value.sql+'<br/>';
			}
         });
		$("#result_result").html(resultHtml);
		$("#result").css("display","block");
		
		/* if(data.hasOwnProperty('lockeds')){
			alert(JSON.stringify(data.lockeds));
		} */
 	};

</script>
@stop
