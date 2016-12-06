<?php
	$currentSubmenu ='/dashboard';
?>

@extends('core.bsmain',['subMenus' => []])
@section('funtionName')
Dashboard
@stop

@section('script')
@parent
	<script src="/common/js/utils.js"></script>
	<script src="/common/js//base64.js"></script>	
	<script src="/common/js/jquery-ui-timepicker-addon.js"></script>

	<style type="text/css">
	div.container{
		padding:5px;
		position:absolute;
		border:1px solid #888888;
	}
	div.container span.title{
		display:block;
		position:absolute;
		border:0px solid #888888;
		//width:auto;
		//height:20px;
		background:#bbbbbb;
		opacity:0.8;
		padding:2px;
		cursor:pointer;
	}
	div.container iframe{
		border:0;
		margin:0;
		padding:0px;
		width:100%;
		height:100%;
	}
	</style>
@stop

@section('action_extra')
<div style="right:5px;top:5px;z-index:10;text-align:right">
	<b><span id="dashboard_name"><?php echo $dashboard_row->NAME; ?></span></b><br>
	<a style="font-size:8pt" href="javascript:loaddashboards()">Change Dashboard</a>
</div>
@stop

@section('content')
<div id="boxDashboardList" style="display:none">
</div>
<div id="boxImpLog" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:2">
	<div style="position: absolute;box-sizing: border-box;box-shadow: 0px 5px 30px rgba(0,0,0,0.5);left:10%;top:10%;padding:15px; width: 80%; height: 80%; z-index: 1; border:1px solid #999999; background:white">
	<input type="button" value="Close" onclick="$('#boxImpLog').fadeOut()" style="position:absolute;width:80px;height:30px;right:0px;top:-30px">
	<div id="logContent" style="width:100%;height:100%;overflow:auto">
	</div>
	</div>
</div>
<div id="pageheader" style="height:100px;"></div>

<div id="boxSelectChart" style="display:none">
<select id="cboSelectCharts"></select>
</div>
<div id="boxSelectWorkflow" style="display:none">
<select id="cboSelectWorkflows"></select>
</div>
<div id="boxSelectReport" style="display:none">
<select id="cboSelectReports"></select>
</div>

<script>
var dashboard_id=0<?php echo $dashboard_id; ?>;
<?php
if($dashboard_id>0){
	echo "var cf=JSON.parse('$dashboard_row->CONFIG'),d_bg='$dashboard_row->BACKGROUND';";
}
else
	echo "var cf=[],d_bg='';";
?>
for(var i=0;i<cf.length;i++){
	create_container(cf[i]);
}
function create_container(config, d_id){
	var html="";
	if(config.type=="1"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectChart()">Chart</span>'+
	'<iframe id="if'+d_id+'" src=""></iframe>'+
	'</div>';
	}
	else if(config.type=="2"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectWorkflow()">Workflow</span>'+
	'<iframe id="if'+d_id+'" src=""></iframe>'+
	'</div>';
	}
	else if(config.type=="3"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectReport()">Report</span>'+
	'<iframe id="if'+d_id+'" src="">'+
	'</div>';
	}
	else if(config.type=="5"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectReport()">Network Model</span>'+
	'<iframe id="if'+d_id+'" src="">'+
	'</div>';
	}
	else if(config.type=="6"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectReport()">Data View</span>'+
	'<iframe id="if'+d_id+'" src="">'+
	'</div>';
	}
	else if(config.type=="7"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectReport()">Storage Display</span>'+
	'<iframe id="if'+d_id+'" src="">'+
	'</div>';
	}
	else if(config.type=="8"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectReport()">Constraint</span>'+
	'<iframe id="if'+d_id+'" src="">'+
	'</div>';
	}
	else if(config.type=="4"){
		html='<div class="container">'+
		Base64.decode(config.obj)+
	//'<textarea style="width:100%;height:100%;border:none">'+config.obj+'</textarea>'+
	'</div>';
	}
	var $box = $(html);
	$box.attr("dashboard_id",d_id);
	$box.attr("d_type",config.type);
	$box.attr("d_title",config.title);
	$box.attr("d_obj",(config.type=="4"?"":config.obj));
	$box.css("left",config.pos[0]);
	$box.css("top",config.pos[1]);
	$box.css("width",config.size[0]);
	$box.css("height",config.size[1]);
	$("#mainContent").append($box);
}
var dashboardList = false;
function loaddashboards(){
	$("#boxDashboardList").dialog({
					height: 350,
					width: 500,
					modal: true,
					title: "Select dashboard",
				});
	if(dashboardList==false){
		$("#boxDashboardList").html("Loading...");
		postRequest( "/dashboard/all",
					 {},
					 function(data){
						dashboardList=data;
						$("#boxDashboardList").html("");
						var elist = $("<ul class='ListStyleNone'>");
						$.each(data, function( dindex, dvalue ) {
							var li = $("<li class='x_item'  style='cursor:pointer'></li>");
							li.attr("d_bg",dvalue.BACKGROUND);
							li.attr("dashboard_id",dvalue.ID);
							li.attr("config",dvalue.CONFIG);
							li.text(dvalue.NAME);
							li.click(function() {
								load_dash_board(li);
							});
							li.appendTo(elist);
						});
						elist.appendTo($("#boxDashboardList"));
					 }
				  );		
	}
}
function load_dash_board(obj){
	dashboard_id=Number($(obj).attr('dashboard_id'));
	d_bg=$(obj).attr('d_bg');
	$("#boxDashboardList").dialog("close");
	$(".container").remove();
	$("#dashboard_name").html($(obj).html());
	//updateName($(obj).html());
	//alert($(obj).attr("config"));
	var cf=JSON.parse($(obj).attr('config'));
	var d_id=$(obj).attr('dashboard_id');
	for(var i=0;i<cf.length;i++){
		create_container(cf[i],d_id);
	}
	reload();
}
function config(){
  //var win = window.open('config.php?dashboard_id='+dashboard_id, '_blank');
  //win.focus();
  location.href='config.php?dashboard_id='+dashboard_id;
}
function selectChart(){
	$("#boxSelectChart").dialog({
					height: 200,
					width: 400,
					modal: true,
					title: "Select chart",
					buttons: {
						"OK": function(){
							$("#ifChart").attr("chart_id",$("#cboSelectCharts").val());
							loadChart();
							$("#boxSelectChart").dialog("close");
	postRequest( 
	             "index.php?act=setchart",
	             {chart_id:$("#cboSelectCharts").val()},
	             function(data){
					 if(data!="") alert(data);
				 }
	          );
						},
						"Cancel": function(){
							$("#boxSelectChart").dialog("close");
						}
					}
				});
}
function selectWorkflow(){
	$("#boxSelectWorkflow").dialog({
					height: 200,
					width: 400,
					modal: true,
					title: "Select workflow",
					buttons: {
						"OK": function(){
							$("#ifWorkflow").attr("wf_id",$("#cboSelectWorkflows").val());
							loadWorkflow();
							$("#boxSelectWorkflow").dialog("close");
	postRequest( 
	             "index.php?act=setworkflow",
	             {workflow_id:$("#cboSelectWorkflows").val()},
	             function(data){
					 if(data!="") alert(data);
				 }
	          );
						},
						"Cancel": function(){
							$("#boxSelectWorkflow").dialog("close");
						}
					}
				});
}
function selectReport(){
	$("#boxSelectReport").dialog({
					height: 200,
					width: 400,
					modal: true,
					title: "Select report",
					buttons: {
						"OK": function(){
							$("#ifReport").attr("reportfile",$("#cboSelectReports").val());
							loadReport();
							$("#boxSelectReport").dialog("close");
	postRequest( 
	             "index.php?act=setreport",
	             {reportfile:$("#cboSelectReports").val()},
	             function(data){
					 if(data!="") alert(data);
				 }
	          );
						},
						"Cancel": function(){
							$("#boxSelectReport").dialog("close");
						}
					}
				});
}
var bgcolor="";
function loadChart(o){
	var date_begin 	= $("#date_begin").val();
	var date_end 	= $("#date_end").val();
	var iurl = 	"/loadchart?bgcolor="+bgcolor+
				"&date_begin="+date_begin+
				"&date_end="+date_end+
				"&chart_id="+$(o).parent().attr("d_obj")+
				"&nolegend";
	$(o).attr("src",iurl);
// 	$(o).attr("src","../graph/advgraph_loadchart.php?bgcolor="+bgcolor+"&date_begin="+$("#date_begin").val()+"&date_end="+$("#date_end").val()+"&chart_id="+$(o).parent().attr("d_obj")+"&nolegend");	
}
function loadWorkflow(o){
	//document.getElementById("ifWorkflow").contentWindow.document.write("<font family='Open Sans'>Loading...</font>");
	$(o).attr("src","../wf/wfshow.php?bgcolor="+bgcolor+"&wf_id="+$(o).parent().attr("d_obj")+"&onlyshow");	
}
var Months=["January","February","March","April","May","June","July","August","September","October","November","December"];
function loadReport(o){
	/* var ds=$("#date_begin").val().split("/");
	//document.getElementById("ifReport").contentWindow.document.write("<font family='Open Sans'>Loading...</font>");
	$(o).attr("src","../report/"+$(o).parent().attr("d_obj")+".php?bgcolor="+bgcolor+"&export=HTML&startDate="+ds[2]+"/"+ds[0]+"/01&report_time="+Months[Number(ds[0])-1]+"%20"+ds[2]+"&facility_id=");

 */

	var date_begin 	= $("#date_begin").val();
	var ds			=date_begin.split("/");
	var iurl = 	"/report/"+$(o).parent().attr("d_obj")+".blade.php?bgcolor="+bgcolor+
				"&startDate="+ds[2]+"/"+ds[0]+"/01"+
				"&export=HTML"+
				"&report_time="+Months[Number(ds[0])-1]+"%20"+ds[2]+
				"&facility_id=";
	$(o).attr("src",iurl);	
}
function loadNetworkModel(o){
	var ds=$("#date_begin").val().split("/");
	//document.getElementById("ifReport").contentWindow.document.write("<font family='Open Sans'>Loading...</font>");
	$(o).attr("src","../diagram/show.php?bgcolor="+bgcolor+"&id="+$(o).parent().attr("d_obj")+"&onlyshow&date="+$("#date_end").val());	
}
function loadDataView(o){
	var d1=$("#date_begin").val();
	var d2=$("#date_end").val();
	//document.getElementById("ifReport").contentWindow.document.write("<font family='Open Sans'>Loading...</font>");
	$(o).attr("src","../view/show.php?bgcolor="+bgcolor+"&v="+$(o).parent().attr("d_obj")+"&begin_date="+d1+"&end_date="+d2);
}
function loadStorageDisplay(o){
	var d1=$("#date_begin").val();
	var d2=$("#date_end").val();
	//document.getElementById("ifReport").contentWindow.document.write("<font family='Open Sans'>Loading...</font>");
	$(o).attr("src","../pd/storagedisplay_loadchart.php?bgcolor="+bgcolor+"&sdid="+$(o).parent().attr("d_obj")+"&date_begin="+d1+"&date_end="+d2);
}
function loadCons(o){
	var d1=$("#date_begin").val();
	var d2=$("#date_end").val();
	//document.getElementById("ifReport").contentWindow.document.write("<font family='Open Sans'>Loading...</font>");
	$(o).attr("src","../graph/choke_load.php?bgcolor="+bgcolor+"&cons_id="+$(o).parent().attr("d_obj")+"&date_begin="+d1+"&date_end="+d2);
}
function reload(){
	$("iframe").each(function(){
		var dtype=$(this).parent().attr("d_type");
		if(dtype=="1"){
			loadChart($(this));
		}
		if(dtype=="2"){
			loadWorkflow($(this));
		}
		if(dtype=="3"){
 			loadReport($(this));
		}
		if(dtype=="5"){
// 			loadNetworkModel($(this));
		}
		if(dtype=="6"){
// 			loadDataView($(this));
		}
		if(dtype=="7"){
// 			loadStorageDisplay($(this));
		}
		if(dtype=="8"){
// 			loadCons($(this));
		}
	});
	if(typeof d_bg != "undefined" && d_bg.length>1) 
		$('body').css("background-color",d_bg);
	else
		$('body').css("background-color","white");
}

$(function() {
 	actions.doLoad = reload;
 	reload();
});
</script>
@stop
