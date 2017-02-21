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
	<div style="margin-left:10px;">
		<table style="float:left">
			<tr>
				<td colspan="2"><p class="function_title">USER INFORMATION</p>	</td>
			</tr>
			<tr>
				<td><font color="gray">Fullname</font></td>
				<td><b>{{$user->FIRST_NAME}} {{$user->MIDDLE_NAME}} {{$user->LAST_NAME}}</b></td>
			</tr>
			<tr>
				<td><font color="gray">Title</font></td>
				<td><b>{{$user->NAME}}</b></td>
			</tr>
			<tr>
				<td><font color="gray">Email</font></td>
				<td><b>{{$user->EMAIL}}</b></td>
			</tr>
			<tr>
				<td colspan="2"><p class="function_title">Change password</p></td>
			</tr>
			<tr>
				<td>Old password</td>
				<td><input type="password" name="txt_old_password" id="txt_old_password" style="width:200px"></td>
			</tr>
			<tr>
			<td>New password</td>
			<td><input type="password" name="txt_new_password" id="txt_new_password" style="width:200px"></td>
			</tr>
			<tr>
			<td>Confim password</td>
			<td><input type="password" name="txt_confirm_password" id="txt_confirm_password" style="width:200px"></td>
			</tr>
			<tr>
			<td></td>
			<td><input type="button" style="width:120px;margin-top:5px" value="Apply" onclick="submit()"></td>
			</tr>
			<tr>
				<td colspan="2"><p class="function_title">Change Date/Time Format</p></td>
			</tr>			
			<tr>
				<td>Date format</td>
				<td><a href="#" id="dateformat">{{$configuration["sample"]["DATE_FORMAT"]}}</a></td>
			</tr>
			<tr>
				<td width="120">Time format</td>
				<td><a href="#" id="timeformat">{{$configuration["sample"]["TIME_FORMAT"]}}</a></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="button" style="width:120px;margin-top:5px" value="Apply" onclick="submitDateTimeFormat()"></td>
			</tr>
			
			<tr>
				<td colspan="2"><p class="function_title">Change Decimal Mark</p></td>
			</tr>
			
			<tr>
				<td >Decimal mark</td>
				<td><a href="#" id="decimalMark">{{$configuration["sample"]["DECIMAL_MARK"]}}</a></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="button" style="width:120px;margin-top:5px" value="Apply" onclick="submitDecimalMarkConfiguration()"></td>
			</tr>
		</table>
		<div style="float:left">
			<p class="function_title">Change language</p>
			<div class="dropdown">
				<a data-toggle="dropdown" class="dropdown-toggle" href="#">
					<img width="32" height="32" alt="{{ session('locale') }}"  src="{!! asset('img/' . session('locale') . '-flag.png') !!}" />
					&nbsp; {{ Config::get('app.languageNames')[session('locale')] }}
					<b class="caret"></b>
				</a>
				<ul class="dropdown-menu">
				@foreach ( config('app.languages') as $user)
					@if($user !== config('app.locale'))
						<li><a href="{!! url('language') !!}/{{ $user }}">
							<img width="32" height="32" alt="{{ $user }}" src="{!! asset('img/' . $user . '-flag.png') !!}">
							&nbsp; {{ Config::get('app.languageNames')[$user] }}
						</a></li>
					@endif
				@endforeach
				</ul>
			</div>
		</div>
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


