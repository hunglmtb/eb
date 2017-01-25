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

		host:'',

		loadCondition : function(_id){
			var id = $('#'+_id).val();
			_report.resetCondition();
			
			switch (id){
				case "1":
				case "2":
				case "11":
				case "12":
				case "13":
					$('#conFacility').css('display','');
					var str="";				
					var months = [ "January", "February", "March", "April", "May", "June",
					               "July", "August", "September", "October", "November", "December" ];
	
					$('#condition1').html(str);
					str += "<td>Month</td>";
					str += "<td><select id='input_month' style='width:200px;'>";
					str += "<option value=''></option>";
					for(var i = 0; i < months.length; i++){
						var value = "00";
						if((i+1) < 10){
							value = "0"+(i+1);
						}else{
							value = i+1;
						}
						str += "<option value='"+(value+"")+"'>"+months[i]+"</option>";
					}
					str += "</select></td>";
					str += "<td>&nbsp;</td>";
					$('#condition1').html(str);

					var d = new Date();
					var yr = d.getFullYear();
					
					var year="";					
					$('#condition2').html(year);
					year += "<td>Year</td>";
					year += "<td><input type='text' style='width:199px;' id='input_year' value='"+yr+"'></td>";
					year += "<td>&nbsp;</td>";
					$('#condition2').html(year);				
					break;
					
				case "3":
					$('#conFacility').css('display','');
					
					var from = "";
					$('#condition1').html(from);	
					from += "<td>From date</td>";
					from += "<td><input type='text' style='width:199px;' id='SstartDate'></td>";
					from += "<td>&nbsp;</td>";
					$('#condition1').html(from);

					var to = "";	
					$('#condition2').html(to);
					to += "<td>To date</td>";
					to += "<td><input type='text' style='width:199px;' id='SendDate'></td>";
					to += "<td>&nbsp;</td>";
					$('#condition2').html(to);

					$("#SstartDate, #SendDate").datepicker({dateFormat:"yy/mm/dd"});
					_report.defaultDate();
					break;
					
				case "4":
					$('#conFacility').css('display','');
					var date = "";
					$('#condition1').html(date);
					date += "<td>Date</td>";
					date += "<td><input type='text' style='width:199px;' id='Date'></td>";
					date += "<td>&nbsp;</td>";
					$('#condition1').html(date);

					$("#Date").datepicker({dateFormat:"yy/mm/dd"});
					_report.defaultDate();
					break;
					
				case "5":
					$('#conScboGroup').css('display','');
					break;
			}
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
	<div id="pageheader" style="height: 100px;"></div>
	<div id="wraper">
		<div
			style="padding: 10px; background: #eee; border: 1px solid #bbb; width: 531px">
			<strong>Choose report&nbsp;&nbsp;</strong> <select size="1"
				style="font-size: 11pt; width: 400px" name="cboReports"
				id="cboReports" onchange="_report.loadCondition('cboReports')">
				<option value="1">Marine Production Summary Report</option>
				<option value="2">Marine Summary Report</option>
				<option value="11">Marine Monthly Production Report</option>
				<option value="12">Marine Monthly Injection Report</option>
				<option value="13">Welltest report</option>
				<option value="3">Welltest summary</option>
				<option value="4">Morning report</option>
				<option value="5">Formula report</option>
			</select>
		</div>
		<br>
		<div id='content'
			style="padding: 10px; border: 1px solid #bbb; width: 531px; height: 200px; background: #eee">
			<table style="width: 100%">
				<tr style="display: none;" id="conFacility">
					<td>Facility</td>
					<td><select size="1" name="cboFacility" id="cboFacility"
						style="width: 200px;"> 
						@foreach($facility as $t)
							<option value="{!!$t->ID!!}">{!!$t->NAME!!}</option> 
						@endforeach
					</select></td>
					<td>&nbsp;</td>
				</tr>

				<tr style="display: none;" id="conScboGroup">
					<td>Group</td>
					<td><select size="1" name="ScboGroup" id="ScboGroup"
						style="width: 200px;">
							<option value="0">(All)</option> 
							@foreach($fogroup as $t)
								<option value="{!!$t->ID!!}">{!!$t->NAME!!}</option> 
							@endforeach
					</select></td>
					<td>&nbsp;</td>
				</tr>

				<tr id="condition1">
				</tr>
				<tr id="condition2">
				</tr>
				<tr>
					<td></td>
					<td><input type="radio" name="reportType" value="PDF" checked>PDF <input
						type="radio" name="reportType" value="Excel">Excel <input
						type="radio" name="reportType" value="HTML">HTML</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td></td>
					<td><input type="button" value="View report" style="width: 100px;"
						id="showReport" onClick="_report.viewReport();"></td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</div>
	</div>

</body>
@stop
