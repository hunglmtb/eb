<?php
$currentSubmenu 	= isset($currentSubmenu)?$currentSubmenu:'/graph';
$functionName		= isset($functionName)?$functionName:"graph";
$useFeatures		= [
						['name'	=>	"filter_modify",
						"data"	=>	["isFilterModify"	=> true]],
					];
$subMenus = [
		array('title' => 'NETWORK MODELS', 'link' => 'diagram'),
		array('title' => 'DATA VIEWS', 'link' => 'dataview'),
		array('title' => 'REPORT', 'link' => 'workreport'),
		array('title' => 'ADVANCED GRAPH', 'link' => 'graph'),
		array('title' => 'TASK MANAGER', 'link' => 'approvedata'),
		array('title' => 'WORKFLOW', 'link' => 'workflow')
];
?>

@section('frequenceFilterGroupMore')
<table border="0" class="clearBoth" style="width: 100%;">
	<tr>
		<td>
			<b>Y axis: Position </b>
			<select id="cboYPos" style="width: auto">
				<option value="L">Left</option>
				<option value="R">Right</option>
			</select>
		</td>
		<td>
			<b> Text </b>
			<input name="txt_y_unit" id="txt_y_unit" value="">
		</td>
		<td align="right" colspan="1">
			<button class="myButton"onclick="_graph.addObject()" style="width: 61px">Add</button>
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
			<ul id="chartObjectContainer" class="ListStyleNone">
			</ul>
		</td>
		<td>@yield("graph_extra_view")</td>
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

@extends('core.bsmain',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])

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

._colorpicker{border:1px solid #bbbbbb;cursor:pointer;margin:2px;width:30px}

</style>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<link rel="stylesheet" href="/common/css/admin.css">
<link rel="stylesheet" href="/common/css/graph/style.css" />
<link rel="stylesheet" media="screen" type="text/css" href="/common/colorpicker/css/colorpicker.css" />
<script type="text/javascript" src="/common/colorpicker/js/colorpicker.js"></script>

<body style="margin: 0; min-width: 1000px;">
	<div id="listCharts" style="display: none; overflow: auto"></div>
	 <iframe id="frameChart" style="width:100%;border:none;height: 400px; margin-top: 10"></iframe>
</body>


<script>

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

	function setColorPicker(){
		$('._colorpicker').ColorPicker({
			onSubmit: function(hsb, hex, rgb, el) {
				$(el).val(hex);
				$(el).css({"background":"#"+hex,"color":"#"+hex});
				$(el).ColorPickerHide();
			},
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			}
		});
	}

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
				var dataStore		= {	
						LoProductionUnit	:	$("#LoProductionUnit").val(),
						LoArea				:	$("#LoArea").val(),
						Facility			:	$("#Facility").val(),
						CodeProductType		:	$("#CodeProductType").val(),
						IntObjectType		:	$("#IntObjectType").val(),
						ObjectName			:	$("#ObjectName").val(),
						ObjectDataSource	:	$("#ObjectDataSource").val(),
						ObjectTypeProperty	:	$("#ObjectTypeProperty").val(),
						CodeFlowPhase		:	$("#CodeFlowPhase").val(),
						CodeAllocType		:	$("#CodeAllocType").val(),
						CodePlanType		:	$("#CodePlanType").val(),
						CodeForecastType	:	$("#CodeForecastType").val(),
						cboYPos				:	$("#cboYPos").val(),
						txt_y_unit			:	$("#txt_y_unit").val(),
					};
				var x =  editBox.getObjectValue(dataStore);
				if($("span[object_value='"+x+"']").length==0){
					var color="transparent";
					var texts			= {
											ObjectName			:	$("#ObjectName option:selected").text(),
											IntObjectType		:	$("#IntObjectType option:selected").text(),
											ObjectDataSource	:	$("#ObjectDataSource option:selected").text(),
											ObjectName			:	$("#ObjectName option:selected").text(),
											ObjectTypeProperty	:	$("#ObjectTypeProperty option:selected").text(),
										};
					if($("#CodeFlowPhase").is(":visible")) 			texts["CodeFlowPhase"] = $("#CodeFlowPhase option:selected").text();
					
					editBox.addObjectItem(color,dataStore,texts,x);
				}
				else
				{
					$("span[object_value='"+x+"']").effect("highlight", {}, 1000);
				}
			}
		},
		editColumn	:  function(element){
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
				s += (s==""?"":",")+$(this).attr("object_value")+":"+$(this).children("select").val()+":"+$(this).children("span").text()+":#"+$(this).children("input").val();
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
					var color="transparent";
					var cc="";
					var k=2;
					if(vals[vals.length-1][0]=="#") {color=vals[vals.length-1];cc=color.substr(1);k=3;}
					var ct="<select class='x_chart_type' style='width:100px'><option value='line'>Line</option><option value='spline'>Curved line</option><option value='column'>Column</option><option value='area'>Area</option><option value='areaspline'>Curved Area</option></select><input type='text' maxlength='6' size='6' style='background:"+color+";color:"+color+";' class='_colorpicker' id='colorpicker_"+i+"' value='"+cc+"'>";
					ct=ct.replace("value='"+vals[vals.length-k]+"'","value='"+vals[vals.length-k]+"' selected");
					var x="",j;
					for(j=0;j<vals.length-k;j++) x+=(x==""?"":":")+vals[j];
					var s="<li class='x_item' object_value='"+x+"'>"+ct+" <span>"+vals[vals.length-k+1]+"</span> "+'<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="../img/x.png"><br></li>';
//  					$("#chartObjectContainer").append(s);


					var dataStore		= {	
// 							LoProductionUnit	:	$("#LoProductionUnit").val(),
// 							LoArea				:	$("#LoArea").val(),
// 							Facility			:	$("#Facility").val(),
// 							CodeProductType		:	$("#CodeProductType").val(),
							IntObjectType		:	vals[0],
							ObjectName			:	vals[1],
							ObjectDataSource	:	vals[2],
							ObjectTypeProperty	:	vals[3],
							CodeFlowPhase		:	vals[4],
// 							CodeAllocType		:	$("#CodeAllocType").val(),
// 							CodePlanType		:	$("#CodePlanType").val(),
// 							CodeForecastType	:	$("#CodeForecastType").val(),
							chartType			:	vals[vals.length-k],

						};
					editBox.addObjectItem(color,dataStore,vals[vals.length-k+1],x);
				}
			}
			setColorPicker();
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
@stop



@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = "/graph/filter";
</script>
@stop
