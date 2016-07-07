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
					idName	:	function (){
									var postData = actions.loadedData['allocateplan'];
									if(postData.IntObjectTypeName=='FLOW') return ['FLOW_ID','OCCUR_DATE'];
									if(postData.IntObjectTypeName=='ENERGY_UNIT') return ['EU_ID','EU_FLOW_PHASE','OCCUR_DATE'];
									return ['ID'];
								},
					keyField:'DT_RowId',
					saveKeyField : function (model){
							return 'ID';
						},
					};
	actions.renderFirsColumn = null;
	actions.getTableHeight	=	function(tab){
		headerOffset = $('#container_allocateplan').offset();
		hhh = $(document).height() - (headerOffset?(headerOffset.top):0) - $('#ebFooter').outerHeight() -135;
		tHeight = ""+hhh+'px';
		return tHeight;
	};
	/* actions.getExtendWidth	= function(data,autoWidth,tab){
		return 280;
	} */
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

		var tab = {'{{config("constants.tabTable")}}'	:	'{{$key}}',
					IntObjectTypeName :		$("#IntObjectType option:selected").attr('name')
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
	<div id="container_{{$key}}">
		<table border="0" id="table_{{$key}}" class="fixedtable nowrap display" cellspacing="0">
			<thead>
				<tr id="_rh" style="background:#E6E6E6;" role="row">
					<th rowspan="1" colspan="1" style="position: relative; left: 0px; background-color: rgb(230, 230, 230);"><b>Occur Date</b>	</th>
					<th rowspan="1" colspan="1"><b>Gross Vol</b>	</th>
					<th rowspan="1" colspan="1"><b>Gross Mass</b>	</th>
					<th rowspan="1" colspan="1"><b>Gross Energy</b>	</th>
					<th rowspan="1" colspan="1"><b>Gross Power</b>	</th>
				</tr>
				<tr style="background:#E6E6E6;height:40px" role="row">
					<th style="position: relative; left: 0px; background-color: rgb(230, 230, 230);" rowspan="1" colspan="1"></th>
					<th rowspan="1" colspan="1"><input type="text" id="t_grs_vol" class="_numeric" style="width:100%;background:#ffff88">	</th>
					<th rowspan="1" colspan="1"><input type="text" id="t_grs_mass" class="_numeric" style="width:100%;background:#ffff88">	</th>
					<th rowspan="1" colspan="1"><input type="text" id="t_grs_energy" class="_numeric" style="width:100%;background:#ffff88"></th>
					<th rowspan="1" colspan="1"><input type="text" id="t_grs_power" class="_numeric" style="width:100%;background:#ffff88">	</th>
				</tr>
				<tr style="background:#E6E6E6;height:40px;display:none">
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
			</thead>
		</table>
	</div>
	<div>
		<table border="0" id="table_{{$key}}_action" class="fixedtable nowrap display" cellspacing="0">
			<thead>
				<tr id="_rh" style="background:#E6E6E6;" role="row">
					<th><b>Record Frequency</b></th>
					<th></th>
				</tr>
				<tr style="background:#E6E6E6;height:40px" role="row">
					<th><select id="cboDataFreq" style="width:100%;background:#ffff88"><option value="d">Daily</option><option value="w">Weekly</option><option value="m">Monthly</option></select></th>
					<th style="padding:3px;width:260">
						<input type="button" onClick="calculateAllocPlan()" style="width:80px;height:30px;" value="Calculate">
						<input type="button" onClick="deletePlan()" style="width:80px;height:30px;" value="Delete">
				<!-- <input type="button" onClick="save()" style="width:80px;height:30px;" value="Save">-->
					</th>
				</tr>
			</thead>
		</table>
	</div>
@stop