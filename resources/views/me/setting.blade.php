<?php
if (!isset($subMenus)) $subMenus = [];
if (!isset($active)) $active =1;
if (!isset($currentSubmenu)) $currentSubmenu ='';
$configuration				=	$user->getConfiguration();
$df 						= 	new \App\Models\DateTimeFormat;
$dateformatSource			=	$df->getFormat('DATE_FORMAT');
$timeformatSource			=	$df->getFormat('TIME_FORMAT');
$decimalMarkFormatSource	=	$df->getFormat('DECIMAL_MARK');

$currentSubmenu ='/me/setting';

?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])

@section('main')
<div class="rootMain {{$currentSubmenu}}">
	<div style="margin:10px;">
	<p class="function_title">USER INFORMATION</p>	
	<h3><font color="gray">Fullname:</font>{{$user->FIRST_NAME}} {{$user->MIDDLE_NAME}} {{$user->LAST_NAME}}</h3>
	<h3><font color="gray">Title:</font> {{$user->NAME}}</h3>
	<h3><font color="gray">Email:</font> {{$user->EMAIL}}</h3>
	<h2 style="color:#378de5">Change password</h2>
		<table border="0">
			<tr>
			<td width="120">Old password</td>
			<td><input type="password" name="txt_old_password" id="txt_old_password" style="width:200px"></td>
			</tr>
			<tr>
			<td width="120">New password</td>
			<td><input type="password" name="txt_new_password" id="txt_new_password" style="width:200px"></td>
			</tr>
			<tr>
			<td width="120">Confim password</td>
			<td><input type="password" name="txt_confirm_password" id="txt_confirm_password" style="width:200px"></td>
			</tr>
			<tr>
			<td width="120"></td>
			<td><input type="button" style="width:120px;margin-top:10px" value="Submit" onclick="submit()"></td>
			</tr>
		</table>
	</div>
	<div id="datetimeDiv" style="float:left;padding:10px">
		<h2 style="color:#378de5">setting date time format</h2>
		<table border="0">
			<tr>
				<td width="120">Date format</td>
				<td><a href="#" id="dateformat">{{$configuration["sample"]["DATE_FORMAT"]}}</a></td>
			</tr>
			<tr>
				<td width="120">Time format</td>
				<td><a href="#" id="timeformat">{{$configuration["sample"]["TIME_FORMAT"]}}</a></td>
			</tr>
			<tr>
				<td width="120"></td>
				<td><input type="button" style="width:120px;margin-top:10px" value="Commit" onclick="submitDateTimeFormat()"></td>
			</tr>
		</table>
	</div>
	
	<div id="datetimeDiv" style="float:left;padding:10px">
		<h2 style="color:#378de5">Number configuration</h2>
		<table border="0">
			<tr>
				<td width="120">Decimal mark</td>
				<td><a href="#" id="decimalMark">{{$configuration["sample"]["DECIMAL_MARK"]}}</a></td>
				<td width="60"></td>
				<td><input type="button" style="width:120px;margin-top:10px" value="Commit" onclick="submitDecimalMarkConfiguration()"></td>
			</tr>
		</table>
	</div>
	
</div>
	
<script>
function submit(){
	if($("#txt_old_password").val()==""){
		alert("Please input old password");
		$("#txt_old_password").focus();
		return;
	}
	if($("#txt_new_password").val()==""){
		alert("Please input new password");
		$("#txt_new_password").focus();
		return;
	}
	if($("#txt_confirm_password").val()!=$("#txt_new_password").val()){
		alert("Confirm password does not match");
		$("#txt_confirm_password").focus();
		return;
	}
	postRequest( 
			 "settings.php?act=changepassword",
			 {old_password:$("#txt_old_password").val(),new_password:$("#txt_new_password").val()},
			 function(data) {
				 if(data=="ok")
					 alert("Password changed successfully");
				 else
					alert(data); 
			 }
		  );
}

$(function() {
	boxEditUserInfo_html = $('#boxEditUserInfo').html();
	$('#boxEditUser').hide();
	$("#pageheader").load("../home/header.php?menu=user");
	var d = new Date();
	$("#txtExpireDate").val("12/31/"+d.getFullYear());
	$( "#txtExpireDate" ).datepicker({
	    changeMonth:true,
	     changeYear:true,
	     dateformat:"mm/dd/yy"
	});

	var dateformatSource =  <?php echo json_encode($dateformatSource); ?>;
	$('#dateformat').editable({
    	type : 'checklist',
//     	name : 'dateformat',
        value: ["{{$configuration['time']['DATE_FORMAT']}}"],
        source:    dateformatSource, 
    });
    
	var timeformatSource =  <?php echo json_encode($timeformatSource); ?>;
	$('#timeformat').editable({
    	type : 'checklist',
        value: ["{{$configuration['time']['TIME_FORMAT']}}"],
        source:    timeformatSource, 
    });

	var decimalMarkFormatSource =  <?php echo json_encode($decimalMarkFormatSource); ?>;
	$('#decimalMark').editable({
    	type : 'checklist',
        value: ["{{$configuration['number']['DECIMAL_MARK']}}"],
        source:    decimalMarkFormatSource, 
    });
});

function submitDateTimeFormat(){
	showWaiting();
	dateformat = $('#dateformat').editable('getValue', true);
	dateformat = dateformat[0];
	if(dateformat==null){
		dateformat = ["{{$configuration['time']['DATE_FORMAT']}}"];
	}

	timeformat = $('#timeformat').editable('getValue', true);
	timeformat = timeformat[0];
	if(timeformat==null){
		timeformat = ["{{$configuration['time']['TIME_FORMAT']}}"];
	}
	
	$.ajax({
		url: '/me/setting/save',
		type: "post",
		data: 	{	dateformat	:	dateformat,
					timeformat	:	timeformat
				},
		success:function(data){
			hideWaiting();
			alert("update success");
		},
		error: function(data) {
			hideWaiting();
		}
	});
}

function submitDecimalMarkConfiguration(){
	showWaiting();
	numberformat = $('#decimalMark').editable('getValue',true);
	numberformat = numberformat[0];
	if(numberformat==null||numberformat==''){
		numberformat = ["{{$configuration['number']['DECIMAL_MARK']}}"];
	}
	
	$.ajax({
		url: '/me/setting/save',
		type: "post",
		data: 	{
					numberformat	:	{ DECIMAL_MARK	: numberformat }
				},
		success:function(data){
			hideWaiting();
			alert("update success");
		},
		error: function(data) {
			hideWaiting();
			console.log ( "submitDecimalMarkConfiguration error " );
		}
	});
}
</script>
@stop


@section('script')
	<link href="/common/css/bootstrap.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-responsive.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-datetimepicker.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-editable.css" rel="stylesheet"/>
	
	<link href="/jqueryui-editable/css/jqueryui-editable.css" rel="stylesheet"/>
	<link href="/common/css/fixedHeader.dataTables.min.css" rel="stylesheet"/>
<!-- 	<link href="/common/css/select.dataTables.min.css" rel="stylesheet"/>
 -->	
	<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
	<!-- <script src="/jqueryui-editable/js/jqueryui-editable.js"></script> -->
	<script src="/common/js/tableHeadFixer.js"></script>

	<script src="/common/js/moment.js"></script>
	<script src="/common/js/bootstrap.js"></script>
	<script src="/common/js/bootstrap-datetimepicker.js"></script>
	<script src="/common/js/bootstrap-editable.js"></script>
	<!-- <script src="/common/js/datetime.js"></script> -->
	
<!-- 	<script src="/common/js/dataTables.select.min.js"></script>
 -->	
@stop


