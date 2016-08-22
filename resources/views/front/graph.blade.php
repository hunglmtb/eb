<?php
$currentSubmenu = '/graph';
$listControls = [ 
		'begin_date' => array (
				'label' => '<b>Begin date</b>',
				'ID' => 'begin_date',
				'TYPE' => 'DATE' 
		),
		
		'end_date' => array (
				'label' => 'End date',
				'ID' => 'end_date',
				'TYPE' => 'DATE' 
		),
		
		'LoProductionUnit' => array (
				'label' => 'Production Unit',
				'ID' => 'LoProductionUnit' 
		),
		
		'LoArea' => array (
				'label' => 'Area',
				'ID' => 'LoArea',
				'fkey' => 'production_unit_id' 
		),
		
		'Facility' => array (
				'label' => 'Facility',
				'ID' => 'Facility',
				'fkey' => 'area_id' 
		),
		
		'CodeProductType' => array (
				'label' => 'Product',
				'ID' => 'CodeProductType',
				'default' => 'All' 
		) 
];
?>

@extends('core.bsdiagram')

@section('title')
<div class="title">ADVANCED GRAPH</div>
@stop @section('group') @include('group.adminControl') @stop

@section('content')
<style>
.alloc_type {
	display: none
}

.plan_type {
	display: none
}

.forecast_type {
	display: none
}

#chartObjectContainer {
	list-style-type: none;
	margin: 0;
	padding: 0;
}

#chartObjectContainer li {
	padding: 1;
}

#chartObjectContainer li span {
	
}
</style>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<link rel="stylesheet" href="/common/css/admin.css">
<link rel="stylesheet" href="/common/css/graph/style.css" />
<script type="text/javascript">

$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	});

	$('#txtObjectName').val('Flow');
	$("#chartObjectContainer").sortable();

	$(".phase_type").hide();

	$('#cboObjectNameTable').change();
});

var _graph = {

	loadObjType : 1,

	currentChartID : 0,

	lastObjectType : "",
	
	loadObjecType : function(){
		var cbo = '';
		cbo += ' <div class="filter">';
		cbo += ' 	<div><b> Object type </b></div>';
		cbo += ' 	<select id = "cboObjectType" onchange="_graph.cboObjectTypeOnChange()">';	
		cbo += ' 		<option selected value="FLOW/FLOW/FL_DATA/Flow">Flow</option>';
		cbo += ' 		<option value="ENERGY_UNIT/ENERGY_UNIT/EU_DATA/Energy Unit">Energy Unit</option>';
		cbo += ' 		<option value="TANK/TANK/TANK/Tank">Tank</option>';
		cbo += ' 		<option value="STORAGE/STORAGE/STORAGE/Storage">Storage</option>';
		cbo += ' 		<option value="ENERGY_UNIT/EU_TEST/EU_TEST/Energy Unit">Well Test</option>';
		cbo += ' 	</select>';
		cbo += ' </div>';

		return cbo;
	},
	
	loadObjects : function(){
		var objectType = $("#cboObjectType").val();
		if(objectType == _graph.lastObjectType) return;
		var ss = objectType.split("/");
		$("#txtObjectName").html(ss[ss.length-1]);

		param = {
			'date_begin' : $('#begin_date').val(),
			'date_end' : $('#end_date').val(),
			'object_type' : objectType,
			'facility_id' : $("#Facility").val(),
			'product_type' : $('#Product').val()
		};
		
		$("#cboObjectName").prop("disabled", true); 
		sendAjaxNotMessage('/loadVizObjects', param, function(data){
			$('#txtObjectName').val($("#cboObjectType ").text());
			adminControl.reloadCbo('cboObjectName',data);
			_graph.loadCbo('cboObjectNameTable',data.tab);

			_graph.lastObjectType = objectType;
			if($("#cboObjectType").val().indexOf("ENERGY_UNIT") > -1)
			{
				_graph.loadEUPhase();
			}
		});
	},
	setValueDefault : function(){
		$('#ProductionUnit').val($('#h_production_unit_id').val());
		$('#Area').val($('#h_area_id').val());
		$('#Facility').val($('#h_facility_id').val());
		$('#begin_date').val($('#h_date_begin').val());
		$('#end_date').val($('#h_date_end').val());
	},
	loadEUPhase : function(){
		param = {
			'eu_id' : $('#cboObjectName').val(),
		};
		
		$("#cboEUFlowPhase").prop("disabled", true); 
		sendAjaxNotMessage('/loadEUPhase', param, function(data){
			adminControl.reloadCbo('cboEUFlowPhase',data);
		});
	},
	cboObjectTypeOnChange : function()
	{
		if($("#cboObjectType").val().indexOf("EU_TEST") > -1)
		{
			$(".eutest_table").show();
			$(".object_table").hide();
			$(".phase_type").show();
		}
		else
		{
			$(".eutest_table").hide();
			$(".object_table").show();
		}
		if($("#cboObjectType").val().indexOf("ENERGY_UNIT/") > -1 && $("#cboObjectType").val().indexOf("EU_TEST/") < 0){
			$(".phase_type").show();
		}else{
			$(".phase_type").hide();
		}
		
		/* $("#cboObjectNameTable").val($("#cboObjectNameTable").find('option:visible:first').attr("value"));
		$("#cboObjectNameProps").val($("#cboObjectNameProps").find('option:visible:first').attr("value")); */
		
		_graph.loadObjects();
	},
	ObjectNameTableChange : function(){
		if($("#cboObjectNameTable").val()=="DATA_ALLOC" && $("#cboObjectType").val().indexOf("ENERGY_UNIT/") > -1)
			$(".alloc_type").show();
		else 
			$(".alloc_type").hide();
		if($("#cboObjectNameTable").val().endsWith("_PLAN"))
			$(".plan_type").show();
		else 
			$(".plan_type").hide();
		if($("#cboObjectNameTable").val().endsWith("_FORECAST"))
			$(".forecast_type").show();
		else 
			$(".forecast_type").hide();

		param = {
			'table' : $("#cboObjectNameTable").val()
		};
		
		sendAjaxNotMessage('/getProperty', param, function(data){
			_graph.loadCbo('cboObjectNameProps', data);
		});
	},
	addObject : function()
	{
		if($("#cboObjectName").val()>0)
		{
			var d=$("#cboObjectType").val().split("/");
			var s1="", s2="", s3="";
			if(d.length>1)
			{1
				s1=d[1]+"_";
			}
			if(d.length>2)
			{
				s2=d[2]+"_";
			}
			if(d[0]=="ENERGY_UNIT")
			{
				s3=":"+$("#cboEUFlowPhase").val();
			}
			
			var x=d[0]+":"+$("#cboObjectName").val()+":"+$("#cboObjectNameTable").val()+":"+$("#cboObjectNameProps").val()+s3+"~"+$("#cboAllocType").val()+"~"+$("#cboPlanType").val()+"~"+$("#cboForecastType").val();
			if($("span[object_value='"+x+"']").length==0)
			{
				var sel="<select class='x_chart_type' style='width:100px'><option value='line'>Line</option><option value='spline'>Curved line</option><option value='column'>Column</option><option value='area'>Area</option><option value='areaspline'>Curved Area</option></select>";
				var s="<li class='x_item' object_value='"+x+"'>"+sel+" <span>"+$("#cboObjectName option:selected").text()+"("+$("#cboObjectType option:selected").text()+"."+$("#cboObjectNameTable option:selected").text()+($("#cboEUFlowPhase").is(":visible")?"."+$("#cboEUFlowPhase option:selected").text():"")+"."+$("#cboObjectNameProps option:selected").text()+")</span> "+'<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png"><br></li>';
				$("#chartObjectContainer").append(s);
			}
			else
			{
				$("span[object_value='"+x+"']").effect("highlight", {}, 1000);
			}
		}
	},
	draw : function()
	{
		$("html, body").animate({ scrollTop: $(document).height() }, 1000);
		document.getElementById("frameChart").contentWindow.document.write("<font family='Open Sans'>Generating chart...</font>");
		
		var title = encodeURIComponent($("#chartTitle").val());
		if(title == "") title = null;
		
		var minvalue = $("#txt_min").val();
		if(minvalue == "") minvalue = null;
		
		var maxvalue = $("#txt_max").val();
		if(maxvalue == "") maxvalue = null;
		
		var date_begin = $("#begin_date").val().replace(/\//g, '-');
		var date_end = $("#end_date").val().replace(/\//g, '-');
		var input = encodeURIComponent(_graph.getChartConfig());	
		$("#frameChart").attr("src","/loadchart/"+title+"/"+minvalue+"/"+maxvalue+"/"+date_begin+"/"+date_end+"/"+input);
	},
	newChart : function()
	{
		if($("#chartObjectContainer").children().length>0)
		{
			if(!confirm("Current chart will be clear. Do you want to continue?")) return;
		}
		_graph.currentChartID = 0;
		$("#chartObjectContainer").empty();
		$("#chartTitle").val("");
	},
	getChartConfig : function()
	{
		var s="";
		$(".x_item").each(function(){
	        s += (s==""?"":",")+$(this).attr("object_value")+":"+$(this).children("select").val()+":"+$(this).children("span").text();
	    });
		return s;
	},
	loadCharts : function()
	{
		$('#listCharts').html("Loading...")
		$( "#listCharts" ).dialog({
			height: 400,
			width: 600,
			modal: true,
			title: "Charts list",
		});
		
		param = {
		};
		
		sendAjaxNotMessage('/listCharts', param, function(data){
			_graph.showListChart(data);
		});
	},
	showListChart : function(_data){
		var data = _data.adv_chart;
		var str = "";
		$('#listCharts').html(str);
		/* for(var i =0; i < data.length; i++){
			str += "<span class='chart_info' id='chart_"+data[i]['ID']+"' min_value='"+data[i]['MIN_VALUE']+"' max_value='"+data[i]['MAX_VALUE']+"' chart_config='"+data[i]['CONFIG']+"' style='display:block;line-height:20px;margin:2px;'><a href='javascript:_graph.openChart("+data[i]['ID']+")'>"+data[i]['TITLE']+"</a> <img valign='middle' onclick='_graph.deleteChart("+data[i]['ID']+")' class='xclose' src='../img/x.png'></span>";
		}  */


		str += "<table width='100%' class='list table table-hover' cellpadding='5' cellspacing='0'>";
		str += "<tr>";
		str += "<td>#</td>";
		str += "<td><b>Chart title</b></td>";
		str += "<td><b>delete</b></td>";
		str += "</tr>";
		
		for(var i =0; i < data.length; i++){
		
			str += " <tr >";
			str += " <td>"+(i+1)+"</td>";
			str += " <td class='chart_info' id='chart_"+data[i]['ID']+"' min_value='"+checkValue(data[i]['MIN_VALUE'],'')+"' max_value='"+checkValue(data[i]['MAX_VALUE'],'')+"' chart_config='"+data[i]['CONFIG']+"' style='cursor:pointer;' onclick='_graph.openChart("+data[i]['ID']+");'><a href='#'>"+data[i]['TITLE']+"</a></td>";
			str += " <td align='center'><a href='#' class='action_del' onclick = '_graph.deleteChart("+data[i]['ID']+");'><img alt='Delete' title='Delete' src='/images/delete.png'></a></td>";
			str += " </tr>";
		}
		str += "</table>";


		
		$('#listCharts').html(str);
	},

	deleteChart : function(id)
	{
		if(!confirm("Do you want to delete this chart?")) return;
		param = {
				'ID' : id
		};
		sendAjaxNotMessage('/deleteChart', param, function(data){
			_graph.showListChart(data);
		});
	},
	openChart : function(id)
	{
		_graph.currentChartID=id;
		$("#chartTitle").val($("#chart_"+id).text());
		$("#txt_max").val(checkValue($("#chart_"+id).attr("max_value"),''));
		$("#txt_min").val(checkValue($("#chart_"+id).attr("min_value"),''));
		$("#chartObjectContainer").empty();
		var config=$("#chart_"+id).attr("chart_config");
		var cfs=config.split(',');
		var i=0;
		for(i=0;i<cfs.length;i++)
		{
			var vals=cfs[i].split(':');
			if(vals.length>=6)
			{
				var ct="<select class='x_chart_type' style='width:100px'><option value='line'>Line</option><option value='spline'>Curved line</option><option value='column'>Column</option><option value='area'>Area</option><option value='areaspline'>Curved Area</option></select>";
				ct=ct.replace("value='"+vals[vals.length-2]+"'","value='"+vals[vals.length-2]+"' selected");
				var x="",j;
				for(j=0;j<vals.length-2;j++) x+=(x==""?"":":")+vals[j];
				var s="<li class='x_item' object_value='"+x+"'>"+ct+" <span>"+vals[vals.length-1]+"</span> "+'<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="../img/x.png"><br></li>';
				$("#chartObjectContainer").append(s);
			}
		}
		$('#listCharts').dialog("close");
		_graph.draw();
	},

	loadCbo : function(id, data){
		var cbo = '';
		$('#'+id).html(cbo);
		for(var v in data){
			cbo += ' 		<option value="' + data[v].CODE + '">' + data[v].NAME + '</option>';
		}
		$('#'+id).html(cbo);
		$('#'+id).change();
	},
	saveChart : function(isAddNew)
	{
		var config = _graph.getChartConfig();
		if(config == ""){alert("Chart's settings is not ready");return;}
		var title = $("#chartTitle").val();
		
		if(title == ""){
			alert("Please input chart's title");
			$("#chartTitle").focus();
			return;
		}
		if(isAddNew == true)
		{
			title = prompt("Please input chart's title",title);
			title = title.trim();
			if(title == "") return;
		}

		param = {
				'id' : (isAddNew?-1:_graph.currentChartID),
				'title' : title,
				'maxvalue' : $("#txt_max").val(),
				'minvalue' : $("#txt_min").val(),
				'config' : config
		};
		sendAjaxNotMessage('/saveChart', param, function(data){
			if(data.substr(0,3)=="ok:")
			{
				alert("Chart saved successfully");
				_graph.currentChartID=data.substr(3);
				$("#chartTitle").val(title);
			}
			else{
				alert(data);
			}
		});
	}
}

function showChartList()
{
	$("#listCharts").show();
	$("#listFormulas").hide();
	$("#cbuttonChart").addClass("cbutton_active");
	$("#cbuttonFormula").removeClass("cbutton_active");
}
function showFormulaList()
{
	$("#listCharts").hide();
	$("#listFormulas").show();
	$("#cbuttonChart").removeClass("cbutton_active");
	$("#cbuttonFormula").addClass("cbutton_active");
}
var timeoutLoading=null;
function iframeOnload()
{
	if(timeoutLoading!=null)
		clearTimeout(timeoutLoading);
	timeoutLoading=null;
}
</script>
<body style="margin: 0; min-width: 1000px;">
	<div id="listCharts" style="display: none; overflow: auto"></div>
	<input type="hidden" id="h_facility_id" value="{!!$workSpace->W_FACILITY_ID!!}">
	<input type="hidden" id="h_date_begin" value="{!!$workSpace->DATE_BEGIN!!}">
	<input type="hidden" id="h_date_end" value="{!!$workSpace->DATE_END!!}">
	<input type="hidden" id="h_production_unit_id" value="{!!$workSpace->PRODUCTION_UNIT_ID!!}">
	<input type="hidden" id="h_area_id" value="{!!$workSpace->AREA_ID!!}">
	<div>
		<table border="0" width="100%" height="113px" cellspacing="0"
			cellpadding="0" style="margin-top: 10px;">
			<tr>
				<td
					style="box-sizing: border-box; padding: 5px; border: 1px solid #bbbbbb; background: #eeeeee; width: 560px">
					<table border="0" id="table1" width="100%" height="100%">
						<tr>
							<td><b><span id="txtObjectName">Flow</span></b></td>
							<td><b>Data source</b></td>
							<td width="115"><b>Property</b></td>
						</tr>
						<tr>
							<td width="200">
								<select style="width: 100%; height: 22"	id="cboObjectName" onchange="_graph.loadEUPhase()" size="1" name="cboObjectName">
								@foreach($result as $re)
									<option value="{!!$re->ID!!}">{!!$re->NAME!!}</option> 
								@endforeach
								</select>
							</td>
							<td width="120"><select style="width: 100%; height: 22"
								id="cboObjectNameTable" size="1" name="cboObjectNameTable" onchange="_graph.ObjectNameTableChange();">
									@foreach($datasource as $re)
										<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> 
									@endforeach
									<!-- <option class="eutest_table" value="DATA_STD_VALUE">STD Value</option>
									<option class="eutest_table" value="DATA_VALUE">Day Value</option>
									<option class="eutest_table" value="DATA_FDC_VALUE">FDC Value</option>
									<option selected class="object_table" value="DATA_VALUE">Day	Value</option>
									<option class="object_table" value="DATA_FDC_VALUE">FDC</option>
									<option class="object_table" value="DATA_THEOR">Theoretical</option>
									<option class="object_table" value="DATA_ALLOC">Allocation</option>
									<option class="object_table" value="DATA_PLAN">Plan</option>
									<option class="object_table" value="DATA_FORECAST">Forecast</option> -->
							</select></td>
							<td><select style="width: 100%; height: 22"
								id="cboObjectNameProps" size="1" name="cboObjectNameProps">
									<option selected class="object_table" value="GRS_VOL">Gross
										Volume</option>
									<option class="object_table" value="NET_VOL">Net Volume</option>
									<option class="eutest_table" value="LIQ_HC_VOL">Oil Volume</option>
									<option class="eutest_table" value="GAS_HC_VOL">Gas Volume</option>
									<option class="eutest_table" value="WTR_VOL">Water Volume</option>
									<option class="eutest_table" value="@GOR">GOR</option>
									<option class="eutest_table" value="@WATER_CUT">Water Cut</option>
							</select></td>
						</tr>
						<tr>
						<td ><span class="phase_type"><b>Flow phase</b> </span></td>
						<td >
							<span class="alloc_type"><b>Alloc type</b></span>
							<span class="plan_type"><b>Plan type</b></span>
							<span class="forecast_type"><b>Forecast type</b></span>
						</td>
						<td></td>
						</tr>
						<tr>
							<td > <select class="phase_type"	style="width: 100%; height: 22" id="cboEUFlowPhase" size="1" name="cboEUFlowPhase"></select></td>
							<td >
								<span class="alloc_type">
									<select style="width: 143px;" id="cboAllocType" size="1" name="cboAllocType">
									@foreach($code_alloc_type as $alloc)
										<option value="{!!$alloc->ID!!}">{!!$alloc->NAME!!}</option> 
									@endforeach
									</select>
								</span> 
								<span class="plan_type">
									<select style="width: 143px;" id="cboPlanType" size="1" name="cboPlanType">
										@foreach($code_plan_type as $plan)
											<option value="{!!$plan->ID!!}">{!!$plan->NAME!!}</option> 
										@endforeach
									</select>
								</span> 
								<span class="forecast_type">
									<select style="width: 143px;" id="cboForecastType" size="1" name="cboForecastType">
										@foreach($code_forecast_type as $forecast)
											<option value="{!!$forecast->ID!!}">{!!$forecast->NAME!!}</option> 
										@endforeach
									</select>
								</span>
							</td>
							<td align="right" colspan="3"><button class="myButton"
									onclick="_graph.addObject()" style="width: 61">Add</button>
							</td>
						</tr>
						<tr>
							
						</tr>
						<tr>
							<td><b>Chart title</b></td>
							<td colspan="2" align="right"><b>Min</b> <input name="txt_min"
								id="txt_min" value="" style="width: 100px; margin-right: 10px"><b>Max</b>
								<input name="txt_max" id="txt_max" value="" style="width: 100px">
							</td>
						</tr>
						<tr>
							<td colspan="3"><input type="text" id="chartTitle"
								name="chartTitle"
								style="width: 536px; height: 17px; padding: 2px;"></input></td>
						</tr>
					</table>
				</td>
				<td width="10">&nbsp;</td>
				<td valign="top"
					style="box-sizing: border-box; overflow: auto; height: 113px; padding: 5px; border: 1px solid #bbbbbb; background: #eeeeee">
					<ul id="chartObjectContainer">
					</ul>
				</td>
				<td rowspan="2" valign="top" align="center" width="180px">
					<button class="myButton" onClick="_graph.draw()"
						style="margin-bottom: 10px; width: 160px; height: 50px">Generate chart</button>
					<button class="myButton" onClick="_graph.saveChart()"
						style="display: none; margin-bottom: 3px; width: 160px; height: 30px">Save
						chart</button>
					<button class="myButton" onClick="_graph.newChart()"
						style="display:; margin-bottom: 3px; width: 78px; height: 35px">New</button>
					<button class="myButton" onClick="_graph.loadCharts()"
						style="display:; margin-bottom: 3px; width: 78px; height: 35px">Load</button>
					<button class="myButton" onClick="_graph.saveChart()"
						style="display:; margin-bottom: 0px; width: 78px; height: 35px">Save</button>
					<button class="myButton" onClick="_graph.saveChart(true)"
						style="display:; margin-bottom: 0px; width: 78px; height: 35px">Save
						as</button>
				</td>
			</tr>
		</table>
	</div>
	<iframe id="frameChart" style="width:100%;border:none;height: 400px; margin-top: 10" onload="iframeOnload()"></iframe>
</body>
@stop
