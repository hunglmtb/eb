<?php
$currentSubmenu = '/workreport';
$host = ENV('DB_HOST');
?>
@extends('core.bsdiagram')
@section('title')
<div class="title">REPORTS</div>
@stop 

@section('content')
<link rel="stylesheet" href="/common/css/admin.css">
<script type="text/javascript">
var _report = {
	loadReportUrl: "/report/loadreports",
	loadParamUrl: "/report/loadparams",

		host:'',
		
		loadReports : function(){
			if(!($("#cboReportGroups").val() > 0)){
				alert("Please select group");
				return;
			}
			showWaiting();
			$("#cboReports").html("");
			$.ajax({
				url: this.loadReportUrl,
				type: "post",
				data: {group_id:$("#cboReportGroups").val()},
				success:function(data){
					hideWaiting();
					var html="";
					data.forEach(function(item, index){
						html += '<option value="'+item.ID+'" data-file="'+item.FILE+'">'+item.NAME+'</option>';
					});
					$("#cboReports").html(html);
					$('#cboReports').change();
				},
				error: function(data) {
					console.log ( "load reports error");
					hideWaiting();
				}
			});
		},

		loadParams : function(){
			$("#box_conditions").html("");
			if(!($("#cboReports").val() > 0)){
				//alert("Please select report");
				return;
			}
			showWaiting();
			$.ajax({
				url: this.loadParamUrl,
				type: "post",
				data: {report_id:$("#cboReports").val()},
				success:function(data){
					hideWaiting();
					var html="";
					data.forEach(function(item, index){
						html += '<tr id="condition_'+item.ID+'"><td>'+item.NAME+'</td><td>';
						if(item.VALUE_TYPE==1){
							if(item.REF_LIST){
								var list_html = '';
								item.REF_LIST.forEach(function(list_item, list_index){
									list_html += '<option value="'+list_item.ID+'">'+list_item.NAME+'</option>';
								});
								html += '<select class="param" data-type="1" name="param_'+item.CODE+'">'+list_html+'</select>';
							}
							else
								html += '<input class="param" type="number" data-type="1" name="param_'+item.CODE+'">';
						}
						else if(item.VALUE_TYPE==2){
							html += '<input class="param" type="text" data-type="2" name="param_'+item.CODE+'">';
						}
						else if(item.VALUE_TYPE==3){
							html += '<input class="param" type="date" data-type="3" name="param_'+item.CODE+'">';
						}
						else if(item.VALUE_TYPE==4){
							html += '<input class="param" type="date" data-type="3" name="param_'+item.CODE+'_from"> To <input class="param" type="date" data-type="3" name="param_'+item.CODE+'_to">';
						}
						else if(item.VALUE_TYPE==5){
							html += '<input class="param" type="month" data-type="5" name="param_'+item.CODE+'">';
						}
						html += '</td></tr>';
					});
					if(html != "")
						html = '<table style="width: 100%">' + html + '</table><hr>';
					$("#box_conditions").html(html);
				},
				error: function(data) {
					console.log ( "load params error");
					hideWaiting();
				}
			});
		},

		resetCondition : function(){
			var str = "";
			$('#conScboGroup').css('display','none');
			$('#conFacility').css('display','none');
			$('#condition1').html(str);
			$('#condition2').html(str);
		},

		defaultDate : function(){
			var d = new Date();
			var m=(1+d.getMonth());
			if(m<10)m="0"+m;
			
			$("#date_from, #SstartDate,#Date").val(""+d.getFullYear()+"/"+m+"/01");
			$("#date_to, #SendDate,#Date").val(""+d.getFullYear()+"/"+m+"/"+d.getDate());
		},

		viewReport : function(){
		 	var pexport = $('input[name=reportType]:checked').val();
			var file = $('#cboReports option:selected').data('file');
			var params = "";
			$(".param[name^='param_']").each(function(){
				var data_type = $(this).data("type");
				if(data_type == "5")
					params += '&'+$(this).attr("name").substr(6)+'__T_3='+$(this).val()+"-01";
				else
					params += '&'+$(this).attr("name").substr(6)+'__T_'+data_type+'='+$(this).val();
			});
			var url = _report.host+'/report/viewreport.php?export='+pexport+'&file='+file+params;
			//console.log(url);
			window.open(url, '_blank');
			return;
			
			var id = $('#cboReports').val();
			
			switch (id){
				case "1":
				 	var pexport = $('input[name=reportType]:checked').val();
					var pstartDate = $("#input_year").val()+"/"+$("#input_month").val()+"/01";
					var preport_time = $("#input_month option:selected").text()+" "+$("#input_year").val();
					var pfacility_id = $("#cboFacility").val();

					window.open(_report.host+'/report/summaryCPReport.blade.php?export='+pexport+'&startDate='+pstartDate+'&report_time='+preport_time+'&facility_id='+pfacility_id+'', '_blank');
					break;
					
				case "2":
					var pexport = $('input[name=reportType]:checked').val();
					var pstartDate = $("#input_year").val()+"/"+$("#input_month").val()+"/01";
					var preport_time = $("#input_month option:selected").text()+" "+$("#input_year").val();
					var pfacility_id = $("#cboFacility").val();

					window.open(_report.host+'/report/summaryVolumeReport.blade.php?export='+pexport+'&startDate='+pstartDate+'&report_time='+preport_time+'&facility_id='+pfacility_id+'', '_blank');
					break;
					
				case "3":
					var pexport = $('input[name=reportType]:checked').val();
					var pstartDate = $("#SstartDate").val();
					var pendDate = $("#SendDate").val();
					var pfacility_id = $("#cboFacility").val();

					window.open(_report.host+'/report/WelltestSummaryReport.blade.php?export='+pexport+'&startDate='+pstartDate+'&endDate='+pendDate+'&facility_id='+pfacility_id+'', '_blank');
					break;

				case "4":
					var pexport = $('input[name=reportType]:checked').val();
					var pdate = $("#Date").val();
					var pfacility_id = $("#cboFacility").val();

					window.open(_report.host+'/report/MorningReport_report.blade.php?export='+pexport+'&date='+pdate+'&facility_id='+pfacility_id+'', '_blank');
					break;

				case "5":
					var pexport = $('input[name=reportType]:checked').val();
					var pgroup_id = $("#ScboGroup").val();

					window.open(_report.host+'/report/FormulaReport_report.blade.php?export='+pexport+'&group_id='+pgroup_id+'', '_blank');
					break;

				case "11":
					var pexport = $('input[name=reportType]:checked').val();
					var pstartDate = $("#input_year").val()+"/"+$("#input_month").val()+"/01";
					var preport_time = $("#input_month option:selected").text()+" "+$("#input_year").val();
					var pfacility_id = $("#cboFacility").val();

					window.open(_report.host+'/report/monthlyProductionReport.blade.php?export='+pexport+'&startDate='+pstartDate+'&report_time='+preport_time+'&facility_id='+pfacility_id+'', '_blank');
					break;
					
				case "12":
					var pexport = $('input[name=reportType]:checked').val();
					var pstartDate = $("#input_year").val()+"/"+$("#input_month").val()+"/01";
					var preport_time = $("#input_month option:selected").text()+" "+$("#input_year").val();
					var pfacility_id = $("#cboFacility").val();

					window.open(_report.host+'/report/monthlyInjectionReport.blade.php?export='+pexport+'&startDate='+pstartDate+'&report_time='+preport_time+'&facility_id='+pfacility_id+'', '_blank');
					break;
					
				case "13":
					var pexport = $('input[name=reportType]:checked').val();
					var pstartDate = $("#input_year").val()+"/"+$("#input_month").val()+"/01";
					var preport_time = $("#input_month option:selected").text()+" "+$("#input_year").val();
					var pfacility_id = $("#cboFacility").val();

					window.open(_report.host+'/report/wellTestReport.blade.php?export='+pexport+'&startDate='+pstartDate+'&report_time='+preport_time+'&facility_id='+pfacility_id+'', '_blank');
					break;
			}
		}
}


$(function(){
	$('#pageheader').css('display', 'none');
	$('#cboReports').change();
	
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	});


	//_report.host = '<?php echo $host;?>';
});

</script>

<body style="margin: 0; overflow-x: hidden">
	<div id="wraper">
		<div style="padding: 10px; background: #eee; border: 1px solid #bbb; width: 531px">
		<p>
			<span style="display:inline-block;width:60px"><b>Group</b></span>
			<select size="1"
			style="font-size: 11pt; width: 400px" name="cboReportGroups"
			id="cboReportGroups" onchange="_report.loadReports()">
					@foreach($rpt_group as $t)
						<option value="{!!$t->ID!!}">{!!$t->NAME!!}</option> 
					@endforeach
			</select>
		</p>
		<p>
			<span style="display:inline-block;width:60px"><b>Report</b></span>
			<select size="1" style="font-size: 11pt; width: 400px" name="cboReports" id="cboReports" onchange="_report.loadParams()">
					@foreach($reports as $t)
						<option value="{!!$t->ID!!}" data-file="{!!$t->FILE!!}">{!!$t->NAME!!}</option> 
					@endforeach
			</select>
		</p>
		<hr>
		<span id="box_conditions">
		</span>
			<div style="margin:10px 0px">
				View as <input type="radio" name="reportType" value="PDF" checked>PDF <input
				type="radio" name="reportType" value="Excel">Excel <input
				type="radio" name="reportType" value="HTML">HTML
			</div>
			<input type="button" value="View report" style="width: 100px;" id="showReport" onClick="_report.viewReport();">
		</div>
	</div>

</body>
@stop
