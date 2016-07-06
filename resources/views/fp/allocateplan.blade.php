<?php
	$currentSubmenu =	'allocateplan';
	$key 			= 	'allocateplan';
 ?>

@extends('core.fp')
@section('funtionName')
MANUAL ALLOCATE PLAN
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/allocateplan/load";
	actions.saveUrl = "/allocateplan/save";
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

		var tab = {'{{config("constants.tabTable")}}'	:	'{{$key}}'
				};
		return tab;
	}

	actions.loadValidating = function (reLoadParams){
		return true;
	}

	/* actions.getNumberRender = function (columnName,data,cellData, type2, row) {
		return cellData;
	} */

	actions.getTableOption	= function(data){
		return {tableOption :	{searching		: false,
								ordering		: false,
// 								scrollY			: '350px',
								},
				invisible:[]};
		
	}
	
	actions.validating = function (reLoadParams){
		/* objs = "";
		$('#selected_objects span').each(function(){
			objs+=(objs==""?"":";")+$(this).attr("info");
		});
		if(objs==""){
			alert("Please add object");
			return false;
		}
		$("#result").css("display","none"); */
		return true;
	}
</script>
@stop

@section('content')
<table cellpadding="2" cellspacing="0" id="table1" style="margin-top:10px">
		<tr id="_rh" style="background:#E6E6E6;">
			<td><b>Occur Date</b></td>
			<td><b>Gross Vol</b></td>
			<td><b>Gross Mass</b></td>
			<td><b>Gross Energy</b></td>
			<td><b>Gross Power</b></td>
			<td><b>Record Frequency</b></td>
			<td></td>
		</tr>
		<tr style="background:#E6E6E6;height:40px">
			<td style=""></td>
			<td><input type="text" id="t_grs_vol" class="_numeric" style="width:100%;background:#ffff88"></td>
			<td><input type="text" id="t_grs_mass" class="_numeric" style="width:100%;background:#ffff88"></td>
			<td><input type="text" id="t_grs_energy" class="_numeric" style="width:100%;background:#ffff88"></td>
			<td><input type="text" id="t_grs_power" class="_numeric" style="width:100%;background:#ffff88"></td>
			<td><select id="cboDataFreq" style="width:100%;background:#ffff88"><option value="d">Daily</option><option value="w">Weekly</option><option value="m">Monthly</option></select></td>
			<td style="padding:3px;width:260">
				<input type="button" onClick="calculateAllocPlan()" style="width:80px;height:30px;" value="Calculate">
				<input type="button" onClick="deletePlan()" style="width:80px;height:30px;" value="Delete">
			</td>
		</tr>
	</table>
	
	<div id="container_{{$key}}" style="overflow-x:hidden">
		<table border="0" cellpadding="3" id="table_{{$key}}" class="fixedtable nowrap display"></table>
	</div>
@stop