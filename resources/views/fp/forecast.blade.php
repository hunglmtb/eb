<?php
	$currentSubmenu ='forecast';
	$tables = ['EnergyUnitDataFdcValue'	=>['name'=>'FDC VALUE'],
	];
 	$active = 1;
 	$f_date_from 	= array('id'=>'f_date_from','name'=>'Forecast date from');
 	$f_date_to 		= array('id'=>'f_date_to','name'=>'to');
 	?>

@extends('core.fp')
@section('funtionName')
WELL FORECAST
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/forecast/load";
	actions.saveUrl = "/forecast/run";
	actions.type = {
					idName:['ID'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
							return 'ID';
						},
					};

	actions.initData = function(){
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
					cb_update_db	:	$("#chk_update_db").val(),
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
		return tab;
	}

	actions.getNumberRender = function (columnName,data,cellData, type2, row) {
		if(columnName=='T') {
			startDate = moment.utc(row.OCCUR_DATE);
			endDate = moment.utc(cellData);
			return startDate.diff(endDate, 'day');
		}
		if(columnName=='V') {
			rendered = 0;
			if(columnName!=null&&columnName!=''){
				rendered = parseFloat(cellData).toFixed(0);
				if(isNaN(rendered)) return 0;
			}
			return rendered;
		}
		return cellData;
	}

	actions.renderFirsColumn = null;
	actions.getTableOption	= function(data){
		return {tableOption :{searching: false},
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
		return true;
	}
	
</script>
@stop

@section('content')
<table cellspacing="5" cellpadding="0" style="background:#e0e0e0;margin-top:10px">
<tr>
<td valign="top" style="padding:5px">
	<table>
	<tr><td align='right'>Data frequency</td><td><select id="cboFreq"><option selected value="0">Day</option><option value="1">Month</option><option value="2">Year</option></select></td></tr>
	<tr><td colspan='2' align='center'><button onClick="actions.doLoad(true)" style="width:100%;height:30px;margin-bottom:10px">Load data</button></td></tr>
	<tr><td align='right'>Equation type</td><td><select id="cboEquationType"><option value="0">Exponential</option><option value="1">Harmonic</option><option selected value="2">Hyperbolic</option></select></td></tr>
	<tr><td align='right'>Lower time</td><td><input type="text" id="t_l" value="" style="width:50px"></td></tr>
	<tr><td align='right'>Upper time</td><td><input type="text" id="t_u" value="" style="width:50px"></td></tr>
	<tr><td align='right'>Middle time</td><td><input type="text" id="t_m" value="" style="width:50px"></td></tr>
	<tr><td align='right'><b>d = </b></td><td><input type="text" id="c1" value="0.35" style="width:50px"></td></tr>
	<tr><td align='right'><b>b = </b></td><td><input type="text" id="c2" value="0.4" style="width:50px"></td></tr>
	<tr><td align='right'></td><td>
		{{ Helper::selectDate($f_date_from)}}
		</td></tr>
	<tr><td align='right'></td><td>
		{{ Helper::selectDate($f_date_to)}}
	</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr id="box_use_modify_data" style="display:none"><td align='right'>Modify input data</td><td><input onclick='chk_modify_data_click(this);' type="checkbox" name="chk_modify_data" id="chk_modify_data"></td></tr>
	<tr><td align='right'>Update database</td><td><input type="checkbox" name="chk_update_db" id="chk_update_db"></td></tr>
	<tr style="display:none"><td align='right'>Generate chart</td><td><input type="checkbox" name="chk_gen_chart" id="chk_gen_chart" checked></td></tr>
	<tr><td colspan='2' align='center'><button onClick="actions.doSave(true)" style="width:100%;height:30px;margin:20px 0px">Run Forecast</button></td></tr>
	</table>
</td>
<td id="box_load_input_data" valign="top" style="padding:5px">
	<div id="container_forecast" style="overflow-x:hidden">
			<table border="0" cellpadding="3" id="table_forecast" class="fixedtable nowrap display">
			</table>
		</div>
</td>
<td valign="top" style="background:#e0e0e0;padding:5px">
<div id="boxOutputData" style="width:850px;height:400px;overflow:auto">
</div>
</td>
</tr>
</table>
@stop