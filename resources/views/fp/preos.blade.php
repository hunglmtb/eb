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

	actions.initData = function(){
		 	/* table:$('#cboSourceType').val(),
		 	objs:objs,
		 	occur_date:$('#date_begin').val(),
		 	phase_type:$('#cboPhaseType').val(),
		 	value_type:$('#cboValueType').val(),
		 	data_source:$('#cboDataSource').val() */
		
		
		var a=$("#cboEquationType").val();
		var b=$("#cboFreq").val();
		var u=$("#t_u").val();
		var l=$("#t_l").val();
		var m=$("#t_m").val();
		var date_from=$("#f_date_from").val();
		var date_to=$("#f_date_to").val();
		var c1=$("#c1").val();
		var c2=$("#c2").val();
		
		var tab = {'{{config("constants.tabTable")}}':'forecast',
					cb_update_db	:	$("#chk_update_db").is(":checked"),
 					'a':a,
 					'b':b,
 					'f_from_date':date_from,
 					'f_to_date':date_to,
 					'u':u,
 					'l':l,
 					'm':m,
 					'c1':c1,
 					'c2':c2
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

	actions.getNumberRender = function (columnName,data,cellData, type2, row) {

		if(columnName=='T') {
			if($.isNumeric(cellData)) tvalue =  cellData;
			else{
				startDate = moment.utc(row.OCCUR_DATE);
				endDate = moment.utc(cellData);
				tvalue = startDate.diff(endDate, 'day');
			}
			row.T = tvalue;
			return tvalue;
		}
		if(columnName=='V') {
			rendered = 0;
			if(columnName!=null&&columnName!=''){
				rendered = parseFloat(cellData).toFixed(0);
				if(isNaN(rendered)) return rendered = 0;
			}
			row.V = rendered;
			return rendered;
		}
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
		var a=$("#cboEquationType").val();
		var b=$("#cboFreq").val();
		var u=$("#t_u").val();
		var l=$("#t_l").val();
		var m=$("#t_m").val();
		if(a==2)
		{
			var c1=$("#c1").val();
			if(c1=="")
			{
				alert("Please input C1");
				$("#c1").focus().select();
				return false;
			}
			if(!(c1>0 && c1<1))
			{
				alert("C1 must be in range (0,1)");
				$("#c1").focus().select();
				return false;
			}
			var c2=$("#c2").val();
			if(c2=="")
			{
				if(m==""|| l=="" || u=="")
				{
					alert("Please input value for Upper time, Lower time and Middle time");
					return false;
				}
	/*
				if(!(m>l && m<u))
				{
					alert("Middle time "+m+" must be in greater than Lower time "+l+" and lower than Upper time "+u);
					$("#t_m").focus().select();
					return false;
				}
	*/
			}
			else if(c2>=1 || c2<=0)
			{
				alert("C2 must be in range (0,1) or equal 0");
				$("#c2").focus().select();
				return false;
			}
			else if(c2>0 && c2<1)
			{
				m=0;l=0;u=0;
			}
		}
		var date_from=$("#f_date_from").val();
		var date_to=$("#f_date_to").val();
		if(date_from=="")
		{
			alert("Please input Forecast date from");
			$("#f_date_from").focus().select();
			return false;
		}
		if(date_to=="")
		{
			alert("Please input Forecast date to");
			$("#f_date_to").focus().select();
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
	
</script>
@stop
