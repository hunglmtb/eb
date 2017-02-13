<?php
$currentSubmenu = '/allocset';
?>
@extends('core.bsallocation')
@section('group')
<div id="controlSearch">

	<div>
		<b>&nbsp;</b>
	</div>
	<b>Allocation group</b>
	<select id="cboNetworks" onchange="_configallocation.bodyJobsList();">
		@foreach($result as $re)
		<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> @endforeach
	</select>
	<button onclick="cloneNetwork()"
		style="margin-left: 10px; width: 160px">Clone allocation group</button>
	<input style="min-width: 80px; float: right" type="button"
		onClick="cancelEdit();showEditJob();" value="Add job" />
	<button onclick="showJobDiagram()"
		style="float: right; margin-right: 5px">Show job diagram</button>
</div>
@stop
@section('content')
<link rel="stylesheet" href="/common/css/admin.css">
<link rel="stylesheet" href="/common/css/jquery-ui.css">
<link rel="stylesheet" href="/common/css/allocation/style.css"/>
<link rel="stylesheet" href="/common/css/allocation/reveal.css">
<script src="/common/js/jquery.js"></script>
<script src="/common/js/jquery-ui.js"></script>
<script src="/common/js/splitter.js"></script>
<script src="/common/js/allocset.js?6"></script>
<script type="text/javascript">
$().ready(function() {
	$("#MySplitter").height($(window).height()-150);
	$("#MySplitter").splitter({
		type: "h", 
	});
});
$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	})
	
	$("#tabs").tabs();
	
	var d = new Date();
	$("#date_begin").val(
			"" + zeroFill(1 + d.getMonth(), 2) + "/01/" + d.getFullYear());
	$("#date_end").val(
			"" + zeroFill(1 + d.getMonth(), 2) + "/" + zeroFill(d.getDate(), 2)
					+ "/" + d.getFullYear());
	$("#date_begin").datepicker({
		changeMonth : true,
		changeYear : true,
		dateFormat : "mm/dd/yy"
	});

	$("#date_end").datepicker({
		changeMonth : true,
		changeYear : true,
		dateFormat : "mm/dd/yy"
	});
	var objTypeName = [ "", "Flow", "EnergyUnit", "Tank", "Storage", "Tank" ];
	$('#cboObjType').change(function(e) {
		$('#cboFacility').change();
	});

	$('#cboFacility').change(function(e) {
		param = {
				'TABLE' : objTypeName[$("#cboObjType").val()],
				'value': $(this).val(),
				'keysearch' : "facility_id"
		}
		
		$("#cboObjects").prop("disabled", true);  
		sendAjaxNotMessage('/onChangeObj', param, function(data){
			loadCbo(data);
		});
	});
	
	$('#cboNetworks').change();
	
});

var _configallocation = {
	current_edit_job : -1,

	bodyJobsList : function(){
		param = {
			'NETWORK_ID' : $('#cboNetworks').val(),
		};
		
		sendAjaxNotMessage('/getJobsRunAlloc', param, function(data){
			_configallocation.listAllocJob(data);

			loadRunnersList(0);
			if(current_edit_job==-1)
				$("#bodyJobsList tr").eq(0).trigger("click");
			else
				$("#bodyJobsList").find("#Qrowjob_"+current_edit_job).trigger("click");
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

			var phase = "";
			if(data[i]['ALLOC_OIL'] == 1){
				phase = "Oil";
			}else if(data[i]['ALLOC_GAS'] == 1){
				phase = "Gas";
			}else if(data[i]['ALLOC_WATER'] == 1){
				phase = "Water";
			}else if(data[i]['ALLOC_GASLIFT'] == 1){
				phase = "Gas-lift";
			}else if(data[i]['ALLOC_CONDENSATE'] == 1){
				phase = "Condensate";
			}else if(data[i]['ALLOC_COMP'] == 1){
				phase = "Comp";
			}
			
			str += '<tr bgcolor="'+ bgcolor +'" id="Qrowjob_'+ data[i]['ID'] +'" daybyday="'+ data[i]['DAY_BY_DAY'] + '" style=\"cursor:pointer\" onclick=\"loadRunnersList('+data[i]['ID']+',\''+data[i]['NAME']+'\',true);loadConditionsList('+ data[i]['ID'] +');\">';
			str += '	<td><span style="color:black;font-weight: normal;" id="QjobName_'+ data[i]['ID'] +'">'+ data[i]['NAME'] +'</span></td>';
			str += '	<td><span style="color:black;font-weight: normal;" id="Qavt_'+ data[i]['ID'] +'" value="'+ data[i]['VALUE_TYPE'] +'">'+ data[i]['VALUE_TYPE_NAME'] +'</span></td>';
			str += '	<td><span style="color:black;font-weight: normal;" id="Qallocphase_'+ data[i]['ID'] +'">'+ phase +'</span></td>';
			str += '	<td align="center" style="font-size:8pt">&nbsp;';
			str += '		<a href=\"javascript:checkJob('+ data[i]['ID'] +')\">Simulate</a> | ';
			str += '		<a href=\"javascript:deleteJob('+ data[i]['ID'] +')\">Delete</a> | ';
			str += '		<a href=\"javascript:editJob('+ data[i]['ID'] +')\">Edit</a> | ';
			str += '		<a href=\"javascript:runJob('+ data[i]['ID'] +')\">Run</a> | ';
			str += '		<a href=\"javascript:clearAllocData('+ data[i]['ID'] +')\">Clear</a>';
			str += ' 	</td>';
			str += '</tr>';
		}
		$('#bodyJobsList').html(str);
	}
}
</script>
<body style="margin: 0px">
	<div id="container" style = "">
		<div id="dialog_edit_condition" title="Edit condition" style="display: none">
			<form>
				<span id="ele_x" class="brc_out" style="display: none"> <span
					class="brc_x" onclick="$(this).parent().remove()">x</span> <span
					class="brc_text" style="padding: 6px 5px 6px 5px">x:x</span>
				</span>
				<fieldset>
					<table width="100%">
						<tr>
							<td width="80"><label for="cond_name">Name</label>
							
							<td>
							
							<td><input type="text" name="cond_name" id="cond_name" value=""
								class="text ui-widget-content ui-corner-all"></td>
						</tr>
						<tr>
							<td><label for="cond_exp">Expression</label>
							
							<td>
							
							<td><input type="text" name="cond_exp" id="cond_exp" value=""
								class="text ui-widget-content ui-corner-all"></td>
						</tr>
						<tr>
							<td><label for="cond_exp">From runner</label>
							
							<td>
							
							<td><select name="cond_from_runner" id="cond_from_runner">
									<option value=""></option>
							</select>
						
						</tr>
						<tr>
							<td><label for="cond_exp">Conditions</label>
							
							<td>
							
							<td><div id="ele_container"></div>
								<table width="100%">
									<tr>
										<td>When</td>
										<td style="width: 180px"><input type="text"
											style="width: 180px" name="cond_to_when" id="cond_to_when"></td>
									</tr>
									<tr>
										<td>To runner</td>
										<td><select style="width: 130px" name="cond_to_runner"
											id="cond_to_runner">
												<option value=""></option>
										</select> <span class="brc_x" onclick="addConditionBlock()">Add</span>
										</td>
									</tr>
								</table></td>
						</tr>
					</table>
				</fieldset>
			</form>
		</div>

		<!-- Run allocation box  -->
		<div id="boxRunAlloc"
			style="display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 3">
			<div
				style="padding: 20px; position: fixed; width: 1100px; height: 450px; z-index: 100; left: 50%; top: 50%; margin-left: -550px; margin-top: -230px; border: 2px solid #999; background: #ffffff">
				<input onClick="hideAllocResult()"
					style="width: 100px; margin: 10 10 10 0px" type="button"
					value="Hide" name="B7"><br> <b><span style="font-size: 13pt">Allocation
						log:</span></b><br>
				<div id="allocLog"
					style="width: 1048px; height: 340px; overflow: auto">....</div>
			</div>
		</div>

		<!-- Diagram box  -->
		<div id="diagram_box"
			style="display: none; overflow: hidden; background: #eeeeee">
			<iframe style="width: 100%; height: 100%; border: 0px; margin: 0px"
				id="iframe_ceflow" name="I1" src=""> Your browser does not support
				inline frames or is currently configured not to display inline
				frames. </iframe>
		</div>

		<div id="boxRunAllocForm"
			style="display: none; text-align: center; padding-top: 20px">
			<table border="0" style="margin-left: 60px" cellpadding="2"
				cellspacing="1" id="table9">
				<tr>
					<td autofocus><b>&nbsp;From date</b></td>
					<td><input style="width: 121; height: 22" type="text"
						id="date_begin" name="date_begin" size="15" value="01/01/1900"></td>
				</tr>
				<tr>
					<td><b>&nbsp;To date</b></td>
					<td><input style="width: 121; height: 22" type="text" id="date_end"
						name="date_end" size="15" value="01/01/2100"></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="checkbox" id="chk_run_alloc_daybyday" disabled
						readonly> Allocation day by day</td>
				</tr>
			</table>
			<br> <a href="javascript:showAllocResult()">Show last allocation log</a>
			<br>
			<br> <input type="button" id="buttonRunAlloc" value="Run Allocation"
				onclick="runAllocation()" style="width: 150px; margin: 0px 10px"> <input
				type="button" value="Cancel" onclick="hideAllocationForm()"
				style="width: 100px; margin-right: 20px">
		</div>

		<!-- Content  -->

			<div id="MySplitter">
				<div id="TopPane">
					<table class="tab_list_table" border="0" cellpadding="4"
						cellspacing="0" id="tableJobsList" width="100%">
						<thead>
							<tr>
								<td><b><font color="black">Job name</font></b></td>
								<td width="120"><b><font color="black">Alloc value type</font></b></td>
								<td><b><font color="black">Alloc phase</font></b></td>
								<td width="200">&nbsp;</td>
							</tr>
						</thead>
						<tbody id="bodyJobsList">
						</tbody>
					</table>
				</div>
				<div id="BottomPane">
					<span id="current_job_name"
						style="display: none; position: absolute; z-index: 10; top: 0px; right: 0px; text-transform: uppercase"></span>
					<div id="tabs" style="border: 0px solid red; height: 100%">
						<ul>
							<li><a href="#tabs-1"><font size="2">RUNNERS</font></a></li>
							<li><a href="#tabs-2"><font size="2">CONDITIONS</font></a></li>
						</ul>
						<div id="tabs-1" class="tab_list_div" style="padding: 0;">
							<button onclick="showAddRunner()"
								style="position: absolute; z-index: 1; top: 5px; right: 5px;">Add
								Runner</button>
							<table class="tab_list_table" border="0" cellpadding="4"
								cellspacing="0" id="tableRunnersList" width="100%">
								<thead>
									<tr>
										<td width="60"><b><font color="black">Order</font></b></td>
										<td><b><font color="black">Name</font></b></td>
										<td width="100"><b><font color="black">Alloc Type</font></b></td>
										<td><font color="black"><b>Alloc from objects</b></font></td>
										<td><font color="black"><b>Alloc to objects</b></font></td>
										<td width="200">&nbsp;</td>
									</tr>
								</thead>

								<tbody id="bodyRunnersList">
								</tbody>
							</table>
						</div>
						<div id="tabs-2" class="tab_list_div" style="padding: 0;">
							<button onclick="editCondition(-1)"
								style="position: absolute; z-index: 10; top: 5px; right: 5px;">Add
								Condition</button>
							<table class="tab_list_table" border="0" cellpadding="4"
								cellspacing="0" id="tableConditionsList" width="100%">
								<thead>
									<tr>
										<td><b><font color="black">Name</font></b></td>
										<td width="100"><b><font color="black">Expression</font></b></td>
										<td><font color="black"><b>From runner</b></font></td>
										<td><font color="black"><b>To runner</b></font></td>
										<td width="150">&nbsp;</td>
									</tr>
								</thead>

								<tbody id="bodyConditionsList">
									<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp; <a href="javascript:deleteCondition()">Delete</a> |
											<a href="javascript:runCondition()"> Run allocation</a></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<!-- Add job box -->
			<div id="box_edit_job" style="display: none">
				Job name<br> <input id="txtJobName" style="width: 100%; height: 22"
					type="text" name="txtJobName" size="20"> <br> <input
					type="checkbox" id="chk_daybyday"> Allocation day by day <br> <br>
				Property <select id="cboAllocValueType"	style="width: 154; height: 22" size="1" name="cboAllocValueType">
					@foreach($CodeAllocValueType as $re)
						<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> 
					@endforeach
				</select>
				<br> <br> Flow phase <span width="20%" class="chk_box"> <span
					class="chk_phase"><input type="checkbox" id="chk_oil">Oil</span> <span
					class="chk_phase"><input type="checkbox" id="chk_gas">Gas</span> <span
					class="chk_phase"><input type="checkbox" id="chk_water">Water</span>
					<span class="chk_phase"><input type="checkbox" id="chk_gaslift">Gas
						lift</span> <span class="chk_phase"><input type="checkbox"
						id="chk_condensate">Condensate</span> <span class="chk_phase"
					style="display: none"><input type="checkbox" id="chk_comp">Composition</span>
				</span> <br>
				<div
					style="position: absolute; width: 400px; margin-left: -200px; left: 50%; bottom: 10px; text-align: center; padding-top: 10px">
					<button id="QAddJob" onclick="AddJob()">Add Job</button>
					<span id="QSaveJobEdit" style="display: none">
						<button id="QsaveEdit" onclick="saveEdit(null)">Save</button>
						<button id="QsaveEditNew" onclick="saveEdit(-1)">Save as New Job</button>
					</span>
					<button id="QCancelJobEdit" onclick="hide_edit_job()">Cancel</button>
				</div>
			</div>
			<!-- Add runner box -->
			<div id="addRunner_box" style="display: none">
				<div id="boxAddRunner" style="">
					<b id="Qaction" style="display: none">Add Runner</b>
					<table border="0" cellpadding="0" id="table7">
						<tr>
							<td width="100">Name</td>
							<td width="200"><input id="txtRunnerName" style="width: 400px"
								type="text" name="txtRunnerName" size="20"></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td width="100">Facility</td>
							<td width="200">
								<select id="cboFacility" style="width: 200; height: 22" size="1" name="cboFacility">
								@foreach($facility as $re)
									<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> 
								@endforeach
								</select>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Object type</td>
							<td><select id="cboObjType" style="width: 200; height: 22"
								size="1" name="cboObjType">
									<option selected value="1">Flow</option>
									<option value="2">Energy Unit</option>
									<option value="3">Tank</option>
									<option value="4">Storage</option>
									<option value="5">Ticket</option>
							</select></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Objects</td>
							<td colspan="2"><select id="cboObjects"
								style="width: 200; height: 22" size="1" name="cboObjects">
							</select> <span id="Qinsertnew"><button onclick="addRunnerFrom()">
										Insert to &quot;From&quot; list</button>
									<button onclick="addRunnerTo()">Insert to &quot;To&quot; list</button></span>
								<span id="Qinsertedit" style="display: none"><button
										onclick="editAddRunnerFrom()">Insert to &quot;From&quot; list</button>
									<button onclick="editAddRunnerTo()">Insert to &quot;To&quot;
										list</button></span></td>
						</tr>
					</table>


					<table id="table55" cellspacing="0" style="width:100%;margin-top:10px">
						<tr style="background: #e0e0e0;">
							<td width="50%"><font color="black"><span class="fixed"> Minus</span><b>From
										objects</b></font></td>
							<td width="50%"><font color="black"><span class="fixed"> Fixed</span><b>To
										objects</b></font></td>
						</tr>
						<tr style="background: #f8f8f8;">
							<td valign="top"><div style="overflow:auto;width:100%;height:200px"><span id="objsFrom">...</span></div></td>
							<td valign="top"><div style="overflow:auto;width:100%;height:200px"><span id="objsTo">...</span></div></td>
						</tr>
					</table>
					<table border="0" cellpadding="2">
						<tr>

							<td style="line-height:28px">Order <input id="txtRunnerOrder" style="width: 60px"
								type="number" name="txtRunnerOrder" size="20">
								&nbsp; &nbsp;From data source <select id="cboAllocFromOption" name="cboAllocFromOption">
								<option value="">Default (Alloc >> Standard >> Theor data)</option>
								@foreach($codeAllocFromOption as $re)
									<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> 
								@endforeach
							</select>
								<br>Allocation type <select id="cboRunnerAllocType"	name="cboRunnerAllocType">
								@foreach($codeAllocType as $re)
									<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> 
								@endforeach
							</select> 
								&nbsp; &nbsp;Theor. phase <select id="cboTheorPhase"	name="cboTheorPhase">
								<option value="">(No change)</option>
								@foreach($codeFlowPhase as $re)
									<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> 
								@endforeach
							</select> 
								&nbsp; &nbsp;Theor. value type <select id="cboTheorValueType" name="cboTheorValueType">
								<option value="">(No change)</option>
								@foreach($codeAllocValueType as $re)
									<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> 
								@endforeach
							</select>
								<div style="position: absolute; width: 400px; bottom: 10px; left: 50%; margin-left: -200px; text-align: center">
									<input onClick="addRunner()" type="button" value="Add Runner"
										name="B3" id="QaddRunner"> <input onClick="saveRunnerEdit()"
										type="button" value="Save" style="display: none"
										id="QsaveRunnerEdit"> <input onClick="saveRunnerClone()"
										type="button" value="Save as New Runner" style="display: none"
										id="QsaveRunnerCopy"> <input onClick="closeBoxEditRunner()"
										type="button" value="Cancel" name="B4">
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
	</div>
<!-- Runner list -->
<script>

$("#chk_gas").change(function() {
	var t = $(this).prop("checked");
	if (t == false)
		$("#chk_comp").prop("checked", false);
	t = (t ? '' : 'none');
	$("#chk_comp").parent().css("display", t);
});

function cloneNetwork(){
	var nid=$("#cboNetworks").val();
	if(!(nid>0)){
		alert("Please select allocation network to clone");
		return;
	}
	var network_name = prompt("Please enter new network name","[New network name]");
	network_name=network_name.trim();
	if(network_name=="")
	{
		return;
	}

	param = {
		'network_id' : nid,
		'network_name' : network_name
	}
	
	sendAjax('/clonenetwork', param, function(data){
		console.log(data);
		if(data.message.length > 0){
			alert(data.message);
		}
		if(data.success == true){
			$("#cboNetworks").append("<option value='"+data.new_network_id+"'>"+network_name+"</option>");
			$("#cboNetworks").val(data.new_network_id);
			$("#cboNetworks").change();
			//location.reload();
		}
	});
}
function hide_edit_job(){
	$("#box_edit_job").dialog("close");
}
function showEditJob(){
	$("#box_edit_job").dialog({
		width: 600,
		height: 350,
		modal: true,
		title: "Edit job"});
}
function showRunnersList()
{
	$("#buttonShowRunnersList").css({background:"#9DC5FF",cursor:""});
	$("#buttonShowConditionsList").css({background:"#eeeeee",cursor:"pointer"});
}
function showConditionsList()
{
	$("#buttonShowConditionsList").css({background:"#9DC5FF",cursor:""});
	$("#buttonShowRunnersList").css({background:"#eeeeee",cursor:"pointer"});
}
var current_condition_id=0;
function editCondition(id)
{
	current_condition_id=id;
	newConditionID=0;
	if(id>0)
	{
		parseConditions($("#Qcondition_out_"+id).html());
		dialog_edit_condition.dialog( "option", "title", "Edit condition" );
		dialog_edit_condition.dialog( "open" );
		$("#cond_name").val($("#Qcondition_name_"+id).html());
		$("#cond_exp").val($("#EXPRESSION_"+id).html());
		$("#cond_from_runner").val($("#RUNNER_FROM_ID_"+id).val());
		$("#cond_to_runner").val($("#RUNNER_TO_ID_"+id).val());
	}
	else
	{
		parseConditions("");
		dialog_edit_condition.dialog( "option", "title", "Add new condition" );
		dialog_edit_condition.dialog( "open" );
		$("#cond_name").val("");
		$("#cond_exp").val("");
		$("#cond_from_runner").val("");
		$("#cond_to_runner").val("");
	}
}
</script>
</body> @stop