<?php
$currentSubmenu = '/allocrun';
?>

@extends('core.bsallocation')
<link rel="stylesheet" href="/common/css/admin.css">
<script src="/common/js/jquery-2.1.3.js"></script>
@section('title')
<div class="title">RUN ALLOCATION</div>
@stop 

@section('group') 
<div id="controlSearch">
	<div class="filter">
		<div><b>Network</b></div>
		<select id="cboNetworks" onchange="_runallocation.getListAllocJob();">
			@foreach($result as $re)
				<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> 
			@endforeach
		</select>
	</div>
	<div class="filter" > 	
		<div style="height: 22px;"><b>From Date </b></div> 
		<input id="begin_date" style="width: 140px; margin-top:0px; height: 26px;" type="text" class="datepicker"> 
	</div>
	<div class="filter" > 	
		<div style="height: 22px;"><b>To Date </b></div> 
		<input id="end_date" style="width: 140px; margin-top:0px; height: 26px;" type="text" class="datepicker"> 
	</div>
	<div class="filter" > 
		<div style="height: 22px;">&nbsp;</div>
		<input type = "button" value = "Run all allocation jobs" onclick="_runallocation.runAllAllocJob();">
	</div>
</div>
@stop

<script type="text/javascript">

$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	})
	
	var d = new Date();
	var today = zeroFill(1+d.getMonth(),2)+"/"+zeroFill(d.getDate(),2)+"/"+d.getFullYear();
	$('#begin_date, #end_date').val(today);
	$( "#begin_date, #end_date" ).datepicker({
	     changeMonth:true,
	     changeYear:true,
	     dateFormat:"mm/dd/yy"
	});

	_runallocation.today = today;

	$('#cboNetworks').change();

});

var _runallocation = {

		today : "",

		runningAllocID : 0,

		getListAllocJob : function(){
			param = {
				'NETWORK_ID' : $('#cboNetworks').val(),
			};
			
			sendAjaxNotMessage('/getJobsRunAlloc', param, function(data){
				_runallocation.listAllocJob(data);
			});
		},

		listAllocJob : function (data){
			var bgcolor="";
			var str = "";
			$('#bodyJobsList').html(str);
			for(var i = 0; i < data.length; i++){
				if(i%2==0){
					bgcolor="#eeeeee";
				}else{
					bgcolor="#f8f8f8";
				}
				str += '<tr bgcolor="'+ bgcolor +'" job_id="'+ data[i]['ID'] +'" id="Qrowjob_'+ data[i]['ID'] +'">';
				str += '	<td><span style="color:black;font-weight: normal;" id="QjobName_'+ data[i]['ID'] +'">'+ data[i]['NAME'] +'</span></td>';
				str += '	<td><input type="text" id="from_date'+ data[i]['ID'] +'" style="width:100px"></td>';
				str += '	<td><input type="text" id="to_date'+ data[i]['ID'] +'" style="width:100px"></td>';
				str += '	<td align="center"><input type="button" value="Run allocation" style="width:120px" onclick="_runallocation.runAllocJob('+ data[i]['ID'] +')"></td>';
				str += '</tr>';
			}
			$('#bodyJobsList').html(str);
			$("#allocLog").html("");
			$("input[type='text']").val(_runallocation.today);
			$( "input[type='text']" ).datepicker({
				changeMonth:true,
				changeYear:true,
				dateFormat:"mm/dd/yy"
			}); 
		},
		checkAllocDate : function(d1,d2)
		{
			var d = new Date("January 01, 2016 00:00:00");
			if(d1<d || d2<d){
				alert("Can not run allocation for the date earlier than 01/01/2016.");
				return false;
			}
			return true;
		},
		runAllocJob : function(job_id, is_append)
		{
			if(_runallocation.runningAllocID==job_id)
			{
				_alert("Allocation job is in progress. Please wait until it was completed.");
				return;
			}
			var d1 = $("#from_date"+job_id).datepicker('getDate');
			var d2 = $("#to_date"+job_id).datepicker('getDate');
			if(!_runallocation.checkAllocDate(d1,d2)){
				return;
			}
			_runallocation.runningAllocID = job_id;
			var jobname=$("#QjobName_"+job_id).html();
			if(is_append)
				$("#allocLog").append("Allocation job '"+jobname+"' has started. Please wait...<br>");
			else
				$("#allocLog").html("Allocation job '"+jobname+"' has started. Please wait...<br>");

			param = {
				'act' : 'run',
				'job_id' : job_id,
				'from_date' : $("#from_date"+job_id).val(),
				'to_date' : $("#to_date"+job_id).val()
			};
			
			sendAjaxNotMessage('/run_runner', param, function(data){
				if(is_append)
            	 	$("#allocLog").append(data);
				else
            	 	$("#allocLog").html(data);
				_runallocation.runningAllocID = 0;
             	alert("Allocation job completed");
			});
		},
		
		runAllAllocJob : function()
		{
			var count=$("#bodyJobsList tr").length;
			if(count>0)
			{
				var d1 = $("#begin_date").datepicker('getDate');
				var d2 = $("#end_date").datepicker('getDate');
				if(!_runallocation.checkAllocDate(d1,d2)){
					return;
				}
				$("#bodyJobsList input[id^='from']").val($("#begin_date").val());
				$("#bodyJobsList input[id^='to']").val($("#end_date").val());
				if(!confirm("Do you want to run all "+count+" allocation job"+(count>1?"s":"")+" in the list?")) return;
				$("#allocLog").html("");
				$("#bodyJobsList tr").each(function(){
					var job_id=$(this).attr("job_id");
					_runallocation.runAllocJob(job_id, true);
				});
			}
			else
				alert("No allocation job to run");
		}
}
</script>

 

@section('content')
<body style="margin: 0px">
	<div id="container" style="width:1322px;">
		<div id="boxJobsList"
			style="background: #f8f8f8; padding: 0px; border-bottom: 0px solid #ccc">
			<table border="0" cellpadding="4" cellspacing="0" id="table5"
				width="100%">
				<thead>
					<tr>
						<td bgcolor="#609CB9"><b><font color="#FFFFFF">Job name</font></b></td>
						<td width="100" bgcolor="#609CB9"><b><font color="#FFFFFF">From
									date</font></b></td>
						<td width="100" bgcolor="#609CB9"><b><font color="#FFFFFF">To date</font></b></td>
						<td width="120" bgcolor="#609CB9"></td>
					</tr>
				</thead>
				<tbody id="bodyJobsList">
				</tbody>
			</table>

			<br>
			<div
				style="box-sizing: border-box; padding: 20px; position: relative; width: 100%; height: 450px; border: 1px solid #bbbbbb; background: #ffffff"
				id="boxRunAlloc">
				<b><span style="font-size: 13pt">Allocation log:</span></b><br>
				<div id="allocLog"
					style="box-sizing: border-box; width: 100% px; height: 340px; overflow: auto">....</div>
			</div>
	</div>
</body>
@stop
