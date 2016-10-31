<?php
$currentSubmenu 	= '/graph';
$functionName		= "graph";
?>

@section('frequenceFilterGroupMore')
<table border="0" class="clearBoth" style="width: 100%;">
	<tr>
		<td > <select class="phase_type"	style="width: 100%; height: 22" id="cboEUFlowPhase" size="1" name="cboEUFlowPhase"></select></td>
		<td align="right" colspan="3">
			<button class="myButton"onclick="_graph.addObject()" style="width: 61">Add</button>
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
			style="width: 99%; height: 17px; padding: 2px;"></input></td>
	</tr>
</table>
@stop

@section('action_extra')
<table border="0" class="floatLeft" style="">
	<tr>
		<td id="tdObjectContainer" valign="top"
			style="min-width:420px;overflow:hidden;box-sizing: border-box; overflow: auto; height: 113px; padding: 5px; border: 1px solid #bbbbbb; background: #eeeeee">
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
@stop

@extends('core.bsdiagram')
@section('group') 
@include('group.production') 
@stop


@section('adaptData')
@parent
<script>
	filters.afterRenderingDependences	= function(dependence){
		if(dependence=="ObjectName") $('#title_'+dependence).text($("#IntObjectType").find(":selected").text());
		else if(dependence=="ObjectDataSource") filters.preOnchange("ObjectDataSource");
	};
	filters.preOnchange	= function(id, dependentIds,more){
		switch(id){
			case "IntObjectType":
				if($("#"+id).find(":selected").attr( "name")=="ENERGY_UNIT"||
						$("#"+id).find(":selected").attr( "name")=="EU_TEST") 
					$('.CodeFlowPhase').css("display","block");
				else $('.CodeFlowPhase').css("display","none");
// 				filters.preOnchange("ObjectDataSource");
				break;
			case "ObjectDataSource":
				var objectDataSource = $('#ObjectDataSource').val();
				if(objectDataSource!=null){
					objectDataSource=='EnergyUnitDataAlloc'?$('.CodeAllocType').show():$('.CodeAllocType').hide();
					objectDataSource.endsWith("Plan")?$('.CodePlanType').show():$('.CodePlanType').hide();
					objectDataSource.endsWith("Forecast")?$('.CodeForecastType').show():$('.CodeForecastType').hide();
					if($("#CodeFlowPhase").is(":visible")) {
						$('.CodePlanType').removeClass("clearBoth");
						$('.CodeAllocType').removeClass("clearBoth");
						$('.CodeForecastType').removeClass("clearBoth");
					}
					else {
						$('.CodePlanType').addClass("clearBoth");
						$('.CodeAllocType').addClass("clearBoth");
						$('.CodeForecastType').addClass("clearBoth");
					}
				}
				$("#tdObjectContainer").css({'height':($("#filterFrequence").height()+'px')});
				break;
		}
	};
</script>
@stop

@section('content')
<style>
#filterFrequence {
	clear: both;
}
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
	filters.afterRenderingDependences("ObjectName");
	filters.preOnchange("IntObjectType");
	filters.preOnchange("ObjectDataSource");
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
			'date_begin' : $('#date_begin').val(),
			'date_end' : $('#date_end').val(),
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
		$('#date_begin').val($('#h_date_begin').val());
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
	addObject : function(){
		if($("#ObjectName").val()>0){
			/* var d=$("#cboObjectType").val().split("/");
			var s1="", s2="", s3="";
			if(d.length>1)
			{
				s1=d[1]+"_";
			}
			if(d.length>2)
			{
				s2=d[2]+"_";
			} */
			var s3="";
			var d0 = $("#IntObjectType").val();
			if(d0=="ENERGY_UNIT"){
				s3+=":"+$("#CodeFlowPhase").val();
			}
			var x	= d0+":"+$("#ObjectName").val()+":"+
						$("#ObjectDataSource").val()+":"+
						$("#ObjectTypeProperty").val()+
						s3+"~"+$("#CodeAllocType").val()+"~"+
						$("#CodePlanType").val()+"~"+
						$("#CodeForecastType").val();
			if($("span[object_value='"+x+"']").length==0)
			{
				var sel="<select class='x_chart_type' style='width:100px'><option value='line'>Line</option><option value='spline'>Curved line</option><option value='column'>Column</option><option value='area'>Area</option><option value='areaspline'>Curved Area</option></select>";
				var s="<li class='x_item' object_value='"+x+
				"'>"+sel+" <span>"+$("#ObjectName option:selected").text()+
				"("+$("#IntObjectType option:selected").text()+
				"."+$("#ObjectDataSource option:selected").val()+
				($("#CodeFlowPhase").is(":visible")?"."+$("#CodeFlowPhase option:selected").text():"")+"."+
				$("#ObjectTypeProperty option:selected").val()+
				")</span> "+'<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png"><br></li>';
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
		if($(".x_item").length<=0) {
			alert("Please add object");
			return;
		}
		
		$("html, body").animate({ scrollTop: $(document).height() }, 1000);
		document.getElementById("frameChart").contentWindow.document.write("<font family='Open Sans'>Generating chart...</font>");
		
		var title = encodeURIComponent($("#chartTitle").val());
		if(title == "") title = null;
		
		var minvalue = $("#txt_min").val();
		if(minvalue == "") minvalue = null;
		
		var maxvalue = $("#txt_max").val();
		if(maxvalue == "") maxvalue = null;
		
		var date_begin = $("#date_begin").val();
		var date_end = $("#date_end").val();
		var input = encodeURIComponent(_graph.getChartConfig());
		var iurl = "/loadchart?title="+title+
								"&minvalue="+minvalue+
								"&maxvalue="+maxvalue+
								"&date_begin="+date_begin+
								"&date_end="+date_end+
								"&input="+input;	
		$("#frameChart").attr("src",iurl);
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
	<iframe id="frameChart" style="width:100%;border:none;height: 400px; margin-top: 10" onload="iframeOnload()"></iframe>
</body>
@stop
