<?php
$currentSubmenu = '/workflow';

$cur_diagram_id = 0;
?>

@extends('core.bsdiagram')

@section('content')

<script type="text/javascript">

$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	});

	$('#surveillanceSetting').css('height', 'auto');

	$("#tdShowHideToolBox").click(function(){
		if($("#tdToolBox").is(":visible"))
		{
			$("#tdToolBox").hide();
			$("#imgShowHideToolBox").attr("src","/images/arrow_right.png");
			$("#graph").css("width",$(window).width()-30);
		}
		else
		{
			$("#tdToolBox").show();
			$("#imgShowHideToolBox").attr("src","/images/arrow_left.png");
			$("#graph").css("width",$(window).width()-$("#tdToolBox").width()-30);
		}
	});

	$('.sur_tabs_holder').skinableTabs({
		effect: 'basic_display',
		skin: 'skin4',
		position: 'top'
	});

	//$("#outlineContainer").css("height",150);
	
	$( "input" ).css( "height",'auto' );
	$( "input[type=text]" ).css( "width",200 );

	$('#cbo_datetype').change(function(){
		if($(this).val()=='date'){
			$('#date_config').show();
		}else{
			$('#date_config').hide();
		}
		$('input[type=date]').css('height', 'auto');
		$('input[type=date]').css('width', 130);
	})

	_workFlow.formFormula = $('#formula').html();


	//$("#outlineContainer").css("height",$(window).height()-$("#tdToolBox").height()-70);
	 $("#graph").css("width",$(window).width()-$("#tdToolBox").width()-30);
	 $("#graph").css("height",$(window).height()-170);

});

var _workFlow = {

		newWorkFlow : function(){
			_workFlow.clearGraph();
			setCurrentDiagramId(0);
		    setCurrentDiagramName(defaultDiagramName);
		},

		loadWorkFlow : function()
		{
			$("#listSavedDiagrams").html("Loading...");
			_workFlow.showBoxDiagrams();

			param = {}

			sendAjax('/getListWorkFlow', param, function(data){
				_workFlow.listData(data);
			});
		},

		listData : function(data){
			var str = "";
			var result = data.result;			
			
			$("#listSavedDiagrams").html(str);
			if(result.length > 0){
				str += "<table width='100%' class='list table table-hover' cellpadding='5' cellspacing='0'>";
				str += "<tr>";
				str += "<td>#</td>";
				str += "<td><b>Name</b></td>";
				str += "<td><b>Status</b></td>";
				str += "<td align='center'><b>Run/Stop</b></td>";
				str += "<td align='center'><b>Delete</b></td>";
				str += "</tr>";
				
				for(var i = 0; i < result.length; i++){
					var status='Stopped';
					if(result[i].isrun == 'yes') status='Running';

					str += " <tr id='"+result[i].id+"' wf_data='"+JSON.stringify(result[i])+"'>";
					str += " <td>"+(i+1)+"</td>";
					str += " <td style='cursor:pointer;' onclick='loadSavedDiagram("+result[i].id+");'>"+result[i].name+"</td>";
					str += " <td><font color='"+(result[i].isrun == 'yes'?"#34b6e4":"#d68d00")+"'>"+status+"</font></td>";
					if(result[i].isrun == 'yes'){
						str += " 	<td align='center'><a href='#' class='action_stop' onclick = 'stopWorkFlow("+result[i].id+");' id='"+result[i].id+"'><img alt='Stop' title='Stop' src='/images/stop.png'></a></td>";
					}else{
						str += " 	<td align='center'><a href='#' onclick = 'runWorkFlow("+result[i].id+");' class='action_run' id='"+result[i].id+"'><img alt='Run' title='Run' src='/images/run.png'></a></td>";
						str += " <td align='center'><a href='#' class='action_del' onclick = 'deleteWorkFlow("+result[i].id+");' id='"+result[i].id+"'><img alt='Delete' title='Delete' src='/images/delete.png'></a></td>";
						str += " </tr>";
					}
				}
				str += "</table>";
				$("#listSavedDiagrams").html(str);
			}
		},

		exportImage : function(type){
			saveSvgAsPng($('#graph svg')[0], 'diagram.png');
		},

		showXML : function(){
			var enc = new mxCodec();
			var node = enc.encode(ed.graph.getModel());
			var currentXML=mxUtils.getPrettyXml(node);
			_workFlow.showBoxDiagrams();
			currentXML=currentXML.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
			$("#listSavedDiagrams").html("<pre>"+currentXML+"</pre>");
		},

		showBoxDiagrams : function(){
			$( "#listSavedDiagrams" ).dialog({
				height: 400,
				width: 550,
				modal: true,
				title: "Workflows list",
			});
		},
		
		hideBoxDiagrams:function()
		{
			$("#listSavedDiagrams").dialog("close");
		},
		
		clearGraph : function()
		{
		    ed.graph.removeCells(ed.graph.getChildVertices(ed.graph.getDefaultParent()));
		},
		
		changeCboRunTask : function (){
			param = {
				'PARENT_CODE' : $('#choice_task_group').val()
			}
			
			$("#choice_task").prop("disabled", true);  
			sendAjax('/changeRunTask', param, function(data){
				var cbo = '';
				var result = data.ebfunctions;
				$("#choice_task").html(cbo);
				
				for(var v in result){
					cbo +='<option value="'+result[v].FUNCTION_CODE+'">'+result[v].FUNCTION_NAME+'</option>';
				}

				$("#choice_task").html(cbo);
				$("#choice_task").prop("disabled", false);  

				if(result.length > 0){
					_workFlow.loadFormSetting();
				}
			});
		},

		listUser : function(){
			$('.chk').each(function(){ this.checked = false; });
			$( "#listUser" ).dialog({
				width: 550,
				modal: false,
				title: "Select user",
				buttons: {
					Save: function() {
						chk=document.getElementsByName('chk');
						var str_users='';
						for(i=0;i<chk.length;i++){
							if(chk[i].checked==true){
								str_users+=chk[i].value+',';
							}
						}
						$('#txt_user').val(str_users);
						str_users='';						
						$( this ).dialog( "close" );
					},
					Close: function() {
						$( this ).dialog( "close" );
					}
				}
			});

			var vuser = $('#txt_user').val().split(',');
			chk=document.getElementsByName('chk');
			for(var j = 0; j < vuser.length; j++){
				for(i=0;i<chk.length;i++){
					if(chk[i].value == vuser[j]){
						chk[i].checked = true;
					}
				}
			}
			
			$("input[type=checkbox]").css('width','auto');
		},
		
		loadFormSetting : function(){
			$('#task_config').html('loading...');	
			var _value=$('#choice_task').val();
			var task_id = curent_object.getAttribute('task_id');
			param = {
				'value' : _value,
				'task_id' : task_id
			}
			
			sendAjaxNotMessage('/loadFormSetting', param, function(data){
				switch(data.result.value){
					case 'ALLOC_CHECK':
					case 'ALLOC_RUN':
						_workFlow.formAlloc(data);					
						break;
					case 'VIS_REPORT':
						_workFlow.formReport(data);
						break;
					case 'FDC_EU':
						_workFlow.formFDCEU(data);			
						break;
					case 'FDC_FLOW':
						_workFlow.formFDCFLOW(data);
						break;
					case 'INT_IMPORT_DATA':
						_workFlow.formIntImportData(data);
						break;
					default:
						$('#task_config').html('');
				}
			});
		},
		formAlloc : function(_data){
			var str = '';
			var data = _data.result.network;
			var allocJob = _data.result.allocJob;
			var task = _data.result.task;
			
			$('#task_config').html(str);	
			str += ' <div class="form-group">';
			str += ' <label class="col-md-2 control-label">Select jobs:</label>';
			str += ' <div class="col-md-5">';
			str += ' 	<select name="cbo_network" id="cbo_network" class="form-control" >';
			for(var x in data){
				str += '	<option value="'+data[x].ID+'">'+data[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' <div class="col-md-5">';
			str += ' 	<select name="cbo_jobs" id="cbo_jobs" class="form-control" >';
			for(var x in allocJob){
				str += '	<option value="'+allocJob[x].ID+'">'+allocJob[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' <div class="form-group">';
			str += ' <label class="col-md-2 control-label">Set date:</label>';
			str += ' <div class="col-md-5">';
			str += ' 	<select id="cbo_datetype">';
			str += ' 		<option value="date">Date</option>';
			str += ' 		<option value="day" selected>This Day</option>';
			str += ' 		<option value="month">This Month</option>';
			str += ' 	</select><br>';
			str += ' 	<span id="date_config" style="display:none;">';
			str += ' 	<input type="date" class="form-control" name="txt_from" id="txt_from" >';
			str += ' 	<input type="date" class="form-control" name="txt_to" id="txt_to">';
			str += ' 	</span>';
			str += ' </div>';
			str += ' </div>';
			str += ' <div class="form-group">';
			str += ' 	<label class="col-md-2 control-label">Send logs:</label>';
			str += ' 	<div class="col-md-10">';
			str += ' 		<input type="text" class="form-control" name="txt_email" id="txt_email" placeholder="email">';
			str += ' 	</div>';
			str += ' </div>';
			str += ' </div>';
			
			$('#task_config').html(str);

			$('input[type=text]').css('height', 'auto');
			$('input[type=text]').css('width', 200);
			$('input[type=date]').css('width', 130);
			$('#task_config select').css('width', 185);

			$('#cbo_datetype').change(function(){
				if($(this).val()=='date'){
					$('#date_config').show();
				}else{
					$('#date_config').hide();
				}
				$('input[type=date]').css('height', 'auto');
			});

			$('#cbo_network').change(function(){
				var value = $('#cbo_network').val();				
				_workFlow.loadCbo(value, 'NETWORK_ID', 'AllocJob', 'cbo_jobs');
			});

			if(task[0].task_config == 'undefined' && task[0].task_config == '{}') return;
			
			var param = JSON.parse(task[0].task_config);

			$('#cbo_network').val(param.network);
			
			$('#cbo_network').change();

			setTimeout(function () {
				$('#cbo_jobs').val(param.jobid);
				
				$('#cbo_datetype').val(param.type);
				$('#cbo_datetype').change();

				if(param.from != ""){
					$('#txt_from').val(param.from);
					$('#txt_to').val(param.to);
				}
				$('#txt_email').val(param.email);
		    }, 3000);			
		},

		loadCbo : function(value, key, table, cbo){
			param = {
				'VALUE' : value,
				'KEY' : key,
				'TABLE' : table
			}
			
			$('#'+cbo).prop("disabled", true);
			sendAjaxNotMessage('/getEntity', param, function(_data){
				
				var data = _data.result;
				var str = '';
				$('#'+cbo).html(str);
				for(var x in data){
					str += '	<option value="'+data[x].ID+'">'+data[x].NAME+'</option>';
				}
				$('#'+cbo).html(str);
				$('#'+cbo).prop("disabled", false);
			});
		},
		
		formReport : function(_data){
			var str = '';
			var data = _data.result;
			$('#task_config').html(str);
				
			str += ' <div class="form-group">';
			str += ' <label class="col-md-2 control-label">Choose Report:</label>';
			str += ' <div class="col-md-5">';
			str += ' 	<select size="1" name="cbo_Reports" id="cbo_Reports" class="form-control">';
			str += '         <option value="4">Daily Production report</option>';
			str += ' 		<option value="1">Daily Allocation report</option>';
			str += ' 		<option value="2">Well summary</option>';
			str += ' 		<option value="3">Welltest summary</option>';
			str += ' 	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' <hr/>';
			str += ' <div class="form-group">';
			str += ' <label class="col-md-2 control-label">Facility:</label>';
			str += ' <div class="col-md-5">';
			str += ' 	<select name="cbo_Facility" id="cbo_Facility" class="form-control" >';
			for(var x in data){
				str += '	<option value="'+data[x].ID+'">'+data[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' <div class="form-group">';
			str += ' <label class="col-md-2 control-label">Set date:</label>';
			str += ' <div class="col-md-5">';
			str += ' 	<select id="cbo_datetype">';
			str += ' 		<option value="date">Date</option>';
			str += ' 		<option value="day" selected>This Day</option>';
			str += ' 		<option value="month">This Month</option>';
			str += ' 	</select><br>';
			str += ' 	<span id="date_config" style="display:none;">';
			str += ' 	<input type="date" class="form-control" name="txt_from" id="txt_from" >';
			str += ' 	<input type="date" class="form-control" name="txt_to" id="txt_to">';
			str += ' 	</span>';
			str += ' </div>';
			str += ' </div>';
			str += ' <div class="form-group">';
			str += ' 	<label class="col-md-2 control-label">Send logs:</label>';
			str += ' 	<div class="col-md-10">';
			str += ' 		<input type="text" class="form-control" name="txt_email" id="txt_email" placeholder="email">';
			str += ' 	</div>';
			str += ' </div>';
	
			$('#task_config').html(str);
			
			$('input[type=text]').css('height', 'auto');
			$('input[type=text]').css('width', 200);
			$('#task_config select').css('width', 185);

			$('#cbo_datetype').change(function(){
				if($(this).val()=='date'){
					$('#date_config').show();
				}else{
					$('#date_config').hide();
				}
				$('input[type=date]').css('height', 'auto');
				$('input[type=date]').css('width', 130);
			})
			
			var task = _data.result.task;  

			if(task[0].task_config == '{}') return;
			
			var param = JSON.parse(task[0].task_config);
			
			$('#cbo_Reports').val(param.reportid);
			
			$('#cbo_Facility').val(param.facility);

			$('#cbo_datetype').val(param.type);			
			$('#cbo_datetype').change();

			if(param.from != ""){
				$('#txt_from').val(param.from);
				$('#txt_to').val(param.to);
			}
			$('#txt_email').val(param.email);
		},
		formFDCEU : function(_data){
			var str = '';
			var facility = _data.result.Facility;
			var codereadingfrequency = _data.result.CodeReadingFrequency;
			var codeflowphase = _data.result.CodeFlowPhase;
			var energyunitgroup = _data.result.EnergyUnitGroup;
			var codeeventype = _data.result.CodeEventType;
			var codealloctype = _data.result.CodeAllocType;
			var codeplantype = _data.result.CodePlanType;
			var codeforecasttype = _data.result.CodeForecastType;
			$('#task_config').html(str);
			
			str += ' 	<div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">Facility:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_Facility" id="cbo_Facility" class="form-control" >';
			str += ' 		<option value="0">(All)</option>';
			for(var x in facility){
				str += '	<option value="'+facility[x].ID+'">'+facility[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' 	<div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">EU Group:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_eugroup" id="cbo_eugroup" class="form-control" >';
			str += ' 		<option value="0">(All)</option>';
			for(var x in energyunitgroup){
				str += '	<option value="'+energyunitgroup[x].ID+'">'+energyunitgroup[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' 	<div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">Reading frequency:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_freq" id="cbo_freq" class="form-control" >';
			str += ' 		<option value="0">(All)</option>';
			for(var x in codereadingfrequency){
				str += '	<option value="'+codereadingfrequency[x].ID+'">'+codereadingfrequency[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' 	<div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">Flow phase:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_phasetype" id="cbo_phasetype" class="form-control" >';
			str += ' 		<option value="0">(All)</option>';
			for(var x in codeflowphase){
				str += '	<option value="'+codeflowphase[x].ID+'">'+codeflowphase[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' 	<div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">Event type:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_eventtype" id="cbo_eventtype" class="form-control" >';
			str += ' 		<option value="0">(All)</option>';
			for(var x in codeeventype){
				str += '	<option value="'+codeeventype[x].ID+'">'+codeeventype[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' 	<div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">Alloc type:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_alloctype" id="cbo_alloctype" class="form-control" >';
			str += ' 		<option value="0">(All)</option>';
			for(var x in codealloctype){
				str += '	<option value="'+codealloctype[x].ID+'">'+codealloctype[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' 	<div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">Plan type:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_plantype" id="cbo_plantype" class="form-control" >';
			str += ' 		<option value="0">(All)</option>';
			for(var x in codeplantype){
				str += '	<option value="'+codeplantype[x].ID+'">'+codeplantype[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' 	<div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">Forecast type:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_forecasttype" id="cbo_forecasttype" class="form-control" >';
			str += ' 		<option value="0">(All)</option>';
			for(var x in codeforecasttype){
				str += '	<option value="'+codeforecasttype[x].ID+'">'+codeforecasttype[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' <div class="form-group">';
			str += ' 	<label class="col-md-2 control-label">Set date:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select id="cbo_datetype">';
			str += ' 			<option value="date">Date</option>';
			str += ' 			<option value="day" selected>This Day</option>';
			str += ' 			<option value="month">This Month</option>';
			str += ' 		</select><br>';
			str += ' 		<span id="date_config" style="display:none;">';
			str += ' 		<input type="date" class="form-control" name="txt_from" id="txt_from">';
			str += ' 		<input type="date" class="form-control" name="txt_to" id="txt_to" >';
			str += ' 		</span>';
			str += ' 	</div>';
			str += ' </div>	<br>';
			str += ' <div class="form-group">';
			str += ' 	<label class="col-md-2 control-label">Send report to:</label>';
			str += ' 	<div class="col-md-10">';
			str += ' 		<input type="text" class="form-control" name="txt_email" id="txt_email" placeholder="email">';
			str += ' 	</div>';
			str += ' </div>';
	
			$('#task_config').html(str);

			$("#cbo_datetype").change(function(){
				if($(this).val()=="date"){
					$("#date_config").show();
				}else{
					$("#date_config").hide();
				}
				$('input[type=date]').css('height', 'auto');
				$('input[type=date]').css('width', 130);
			})

			$('input[type=text]').css('height', 'auto');
			$('#task_config input[type=text]').css('width', 400);
			$('#task_config select').css('width', 185);

			var task = _data.result.task;  

			if(task[0].task_config == '{}') return;
			
			var param = JSON.parse(task[0].task_config);
			
			$('#cbo_Facility').val(param.facility);
			$('#cbo_eugroup').val(param.eugroup_id);
			$('#cbo_freq').val(param.freq);
			$('#cbo_phasetype').val(param.phase_type);
			$('#cbo_eventtype').val(param.event_type);
			$('#cbo_alloctype').val(param.alloc_type);
			$('#cbo_plantype').val(param.plan_type);
			$('#cbo_forecasttype').val(param.forecast_type);
			
			$('#cbo_datetype').val(param.type);			
			$('#cbo_datetype').change();

			if(param.from != ""){
				$('#txt_from').val(param.from);
				$('#txt_to').val(param.to);
			}
			$('#txt_email').val(param.email);
		},
		formFDCFLOW : function(_data){
			var str = '';
			var facility = _data.result.Facility;
			var codereadingfrequency = _data.result.CodeReadingFrequency;
			var codeflowphase = _data.result.CodeFlowPhase;
			$('#task_config').html(str);
			
			str += ' 	<div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">Facility:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_Facility" id="cbo_Facility" class="form-control" >';
			for(var x in facility){
				str += '	<option value="'+facility[x].ID+'">'+facility[x].NAME+'</option>';
			}
			str += ' 			</select>';
			str += ' 		</div>';
			str += ' 	</div>';
			str += ' 	<div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">Reading frequency:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_freq" id="cbo_freq" class="form-control" >';
			str += ' 		<option value="0">(All)</option>';
			for(var x in codereadingfrequency){
				str += '	<option value="'+codereadingfrequency[x].ID+'">'+codereadingfrequency[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' <div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">Flow phase:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_phasetype" id="cbo_phasetype" class="form-control" >';
			str += ' 		<option value="0">(All)</option>';
			for(var x in codeflowphase){
				str += '	<option value="'+codeflowphase[x].ID+'">'+codeflowphase[x].NAME+'</option>';
			}
			str += ' 		</select>';
			str += ' 	</div>';
			str += ' </div>';
			str += ' <div class="form-group allocation">';
			str += ' 	<label class="col-md-2 control-label">Set date:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select id="cbo_datetype">';
			str += ' 			<option value="date">Date</option>';
			str += ' 			<option value="day" selected>This Day</option>';
			str += ' 			<option value="month">This Month</option>';
			str += ' 		</select>';
			str += ' 		<span id="date_config" style="display:none;">';
			str += ' 		<input type="date" class="form-control" name="txt_from" id="txt_from">';
			str += ' 		<input type="date" class="form-control" name="txt_to" id="txt_to" >';
			str += ' 		</span>';
			str += ' 	</div>';
			str += ' </div> <br>';
			str += ' <div class="form-group">';
			str += ' 	<label class="col-md-2 control-label">Send logs:</label>';
			str += ' 	<div class="col-md-10">';
			str += ' 		<input type="text" class="form-control" name="txt_email" id="txt_email" placeholder="email">';
			str += ' 	</div>';
			str += ' </div>';

			$('#task_config').html(str);

			$('#cbo_datetype').change(function(){
				if($(this).val()=='date'){
					$('#date_config').show();
				}else{
					$('#date_config').hide();
				}
				$('input[type=date]').css('height', 'auto');
				$('input[type=date]').css('width', 130);
			})
			
			$('input[type=text]').css('height', 'auto');
			$('input[type=text]').css('width', 200);
			$('#task_config select').css('width', 140);

			var task = _data.result.task;  

			if(task[0].task_config == '{}') return;
			
			var param = JSON.parse(task[0].task_config);
			
			$('#cbo_Facility').val(param.facility);
			$('#cbo_freq').val(param.freq);			
			$('#cbo_phasetype').val(param.phase_type);
			
			$('#cbo_datetype').val(param.type);			
			$('#cbo_datetype').change();

			if(param.from != ""){
				$('#txt_from').val(param.from);
				$('#txt_to').val(param.to);
			}
			$('#txt_email').val(param.email);
			
		},
		formIntImportData : function(_data){
			var str = '';
			var data = _data.result.IntConnection;
			var IntTagSet = _data.result.IntTagSet;
			$('#task_config').html(str);
			str += ' <div class="form-group">';
			str += ' <label class="col-md-2 control-label">Import Type:</label>';
			str += ' <div class="col-md-5">';
			str += ' 	<input type="radio" class="form-control" name="txt_connect" id="txt_pi" value="pi" checked="true"> PI Connection';
			str += ' 	<input type="radio" class="form-control" name="txt_connect" id="txt_scada" value="scada"> SCADA Connection';
			str += ' </div>';
			str += ' </div>';
			str += ' <div class="form-group">';
			str += ' <label class="col-md-2 control-label">Connection:</label>';
			str += ' <div class="col-md-5">';
			str += ' 	<select name="cbo_connection" id="cbo_connection" >';
			for(var x in data){
				str += '	<option value="'+data[x].ID+'">'+data[x].NAME+'</option>';
			}
			str += ' 	</select>';
			str += ' </div>';
			str += ' <div class="col-md-5">';
			str += ' 	<select name="cbo_tag" id="cbo_tag">';
			for(var x in IntTagSet){
				str += '	<option value="'+IntTagSet[x].ID+'">'+IntTagSet[x].NAME+'</option>';
			}
			str += '	</select>';
			str += ' </div>';
			str += ' </div>';
			str += ' <div class="form-group">';
			str += ' 	<label class="col-md-2 control-label">Method:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<select name="cbo_method" id="cbo_method">';
			str += ' 			<option value="last">Last</option>';
			str += ' 			<option value="first">First</option>';
			str += ' 			<option value="max">Max</option>';
			str += ' 			<option value="min">Min</option>';
			str += ' 			<option value="average">Average</option>';
			str += ' 		</select>';
			str += ' 	</div>';
			str += ' </div>';
			str += ' <div class="form-group">';
			str += ' 	<label class="col-md-2 control-label">Set date:</label>';
			str += ' 	<div class="col-md-5">';
			str += ' 		<input type="date" class="form-control" name="txt_from" id="txt_from">';
			str += ' 		<input type="date" class="form-control" name="txt_to" id="txt_to">';
			str += ' 	</div>';
			str += ' </div>';
			str += ' <div class="form-group">';
			str += ' 	<label class="col-md-2 control-label">Send logs:</label>';
			str += ' 	<div class="col-md-10">';
			str += ' 		<input type="text" class="form-control" name="txt_email" id="txt_email" placeholder="email">';
			str += ' 	</div>';
			str += ' </div>';
	
			$('#task_config').html(str);
			
			$('input').css('height', 'auto');
			$('input[type=radio]').css('width', 'auto');
			$('input[type=text]').css('height', 'auto');
			$('#task_config input[type=text]').css('width', 400);
			$('input[type=date]').css('width', 130);
			$('#task_config select').css('width', 185);

			$('#cbo_connection').change(function(){
				var value = $('#cbo_connection').val();				
				_workFlow.loadCbo(value, 'CONNECTION_ID', 'IntTagSet', 'cbo_tag');
			});
		},

		setDataTaskConfig : function(_data){
			var data = _data.flowTask;
			var style = _data.style;
			if(data == null) return ;
			$('#txt_task_name').val(data.name);
			$('#txt_task_id').val(data.id);
			$('#task_run_type').val(data.runby);
			$('#txt_user').val(data.user);
			$('#choice_task_group').val(data.task_group);
			$('#choice_task_group').change();

			setTimeout(function () {
				$('#choice_task').val(data.task_code);			
				$('#choice_task').change();
		    }, 3000);

		    if(data.task_code == "NODE_CONDITION"){
		    	var task_config = JSON.parse(data.task_config);
		    	setTimeout(function () {
		    		$('#choose_formula_group').val(task_config['formula_group']);	
					$('#choose_formula_group').change();
			    }, 3000);
		    	
		    	$('#choose_formula').val(task_config['formula_id']);
		    	$('#cbo_datetype').val(task_config['type']);
		    	$('#cbo_datetype').change();
		    	if(task_config['type'] == 'date'){
		    		$('#txt_from').val(task_config['from']);
		    		$('#txt_to').val(task_config['to']);
		    	}

		    	var conds = task_config['condition'].length;
		    	var str = '';
		    	$('#form-group-condition').html(str);
		    	for(var i = 0; i < conds; i++){
			    	var _value = task_config['condition'][i];
			    	var s = _workFlow.loadCboFormula(_value['target_task_id']);
		    		str += '<div class="condition_item" id="condition0" style="display: block">';
		    		str += '<input type="text" value = '+_value['condition']+' class="form-control" name="txt_condition" id="txt_condition" placeholder="$r>1 and $r<=10" style="height: auto; width: 200px;"> &gt;&gt;'; 
		    		str += '<select class="cbo_target_task" >'+s+'</select>'; 
		    		str += '<input type="button" onclick="deleteCondition(this)" style="margin: 2px; width: 70px; height: auto;" value="Delete">';
		    		str += '</div>';
			    }

			    $('#form-group-condition').append(str);
			}else if(data.task_code == "EMAIL"){
				var task_config = JSON.parse(data.task_config);
				$('#fromEmail').val(task_config['from']);
				$('#toEmail').val(task_config['to']);
				$('#txt_subject').val(task_config['subject']);
				$('#txt_mess').val(task_config['content']);
			}
		},

		loadCboFormula : function(id){
			var s = "";
			var n=curent_object.getEdgeCount();
			for(i=0;i<n;i++){
				var se = '';
				var e=curent_object.getEdgeAt(i);
				var to=e.getTerminal(false);
				if(to.id!=curent_object.id){
					if(to.getAttribute('task_id') == id){
						se = "selected";
					}
					s+="<option value='"+to.getAttribute('task_id')+"' "+se+">"+to.getAttribute('label')+"</option>";
				}
			}
			return s;
		},

		formFormula : '',

		resetFormConfig : function(){
			$('#txt_task_name').val('');
			$('#txt_task_id').val('');
			$('#task_run_type').val(1);
			$('#txt_user').val('');
			$('#choice_task_group').val('normal-fun');
			$('#choice_task').html('');
			$('#task_config').html('');
			$('#formula').html(_workFlow.formFormula);
			
			$('#choose_formula_group').change(function(){
				var value = $('#choose_formula_group').val();				
				_workFlow.loadCbo(value, 'GROUP_ID', 'Formula', 'choose_formula');
			});
			
		}
}

var ed;
var curent_task=0;
var curent_object=null;
function onInit(editor){
    ed=editor;
    // Enables rotation handle
    mxVertexHandler.prototype.rotationEnabled = true;

    // Enables guides
    mxGraphHandler.prototype.guidesEnabled = true;

    // Alt disables guides
    mxGuide.prototype.isEnabledForEvent = function(evt)
    {
        return !mxEvent.isAltDown(evt);
    };

    // Enables snapping waypoints to terminals
    mxEdgeHandler.prototype.snapToTerminals = true;

    // Defines an icon for creating new connections in the connection handler.
    // This will automatically disable the highlighting of the source vertex.
    mxConnectionHandler.prototype.connectImage = new mxImage('/images/connector.gif', 16, 16);

    // Enables connections in the graph and disables
    // reset of zoom and translate on root change
    // (ie. switch between XML and graphical mode).
    editor.graph.setConnectable(true);

    editor.graph.setPanning(true);
    //editor.graph.panningHandler.useLeftButtonForPanning = true;


    // Clones the source if new connection has no target
    editor.graph.connectionHandler.setCreateTarget(false);
    editor.graph.setAllowDanglingEdges(false);
	
	mxGraph.prototype.multigraph=false;
	mxGraph.prototype.alreadyConnectedResource="Already connected";

    var cellAddedListener = function(sender, evt)
    {
        var cells = evt.getProperty('cells');
        var cell = cells[0];
        if(editor.graph.isSwimlane(cell)){
            var DiagramName = mxUtils.prompt('Enter swimlane name', 'Swimlane');
            if(!DiagramName)
            {
                editor.graph.removeCells([cell]);
                return;
            }
            cell.setAttribute("label",DiagramName);
            addSubnetworkListItem(cell);
        }
    };

    var cellRemovedListener=function(sender, evt){
        updateSubnetworksList();
    }
    editor.graph.addListener(mxEvent.CELLS_ADDED, cellAddedListener);
    editor.graph.addListener(mxEvent.CELLS_REMOVED, cellRemovedListener);
    editor.graph.selectionModel.addListener(mxEvent.CHANGE, function(){
		if(ed.graph.selectionModel.cells.length==1){
			onObjectSelected(ed.graph.selectionModel.cells[0]);
		}
		else
			curent_object=null;
	});

    //outlineContainer
    if(!outline){
        outline = document.getElementById('outlineContainer');
        if (mxClient.IS_IE)
        {
            new mxDivResizer(outline);
        }
        var outln = new mxOutline(editor.graph, outline);
    }
}

function buttonActionClick(act){
	if(act=="print"){
		var pageCount=1;
		var scale = mxUtils.getScaleForPageCount(pageCount, ed.graph);
		var preview = new mxPrintPreview(ed.graph, scale);
		var oldRenderPage = mxPrintPreview.prototype.renderPage;
		
		var title=$("#diagramName").text();
		var sur_date=$("#Qoccurdate").val()+"";
		preview.title=title+(sur_date!=""?" - Surveillance date: "+sur_date:"");
		preview.print();
		preview.close();
	}
    else{
    	if(act=="rotate"){
    		ed.graph.toggleCellStyles(mxConstants.STYLE_HORIZONTAL,"1",ed.graph.selectionModel.cells);
    	}else{ 
        	ed.execute(act);
    	}
    } 
}

function changeLineColor(color)
{
    ed.graph.model.beginUpdate();
    try
    {
        var c;
        for (c in ed.graph.selectionModel.cells)
        {
            if(ed.graph.selectionModel.cells[c].isEdge())
            {
                if (color=='') color='black';
                ed.graph.setCellStyles("strokeColor", color, [ed.graph.selectionModel.cells[c]]);
            }
        }
    }
    finally
    {
        ed.graph.model.endUpdate();
    }
}

function cellMovedListener(sender, evt){
	var cells = evt.getProperty('cells');
 	for (i = 0; i < cells.length; i++) {
	  	var cell=cells[i];
	  	if(cell.id!==null)
	  	if(cell.id.substr(0,8)=='sur_val_'){
	   		updateSurPhaseCellPosition(cell);
	  	}
 	}

	ed.graph.refresh();
}

function updateSurPhaseCellPosition(baseCell){
	var ind=Number(baseCell.getAttribute("sur_phase_index"));
	var id1=baseCell.id.substr(0,baseCell.id.lastIndexOf('_')+1);
	for(i=0;i<30;i++){
		var cell=ed.graph.model.getCell(id1+i);
		if(typeof cell!=='undefined'){
			var ind2=Number(cell.getAttribute("sur_phase_index"));
			if(ind2!=ind){
				cell.geometry.y=baseCell.geometry.y+(ind2-ind)*baseCell.geometry.height;
				cell.geometry.x=baseCell.geometry.x;
			}
		}
	}
}

function isCellStyle(cell,style){
	if(cell.style!=undefined)
		return cell.style.indexOf(style)>-1;
	return false;
}

function onObjectSelected(obj){
	if(isCellStyle(obj,'beginpoint') || isCellStyle(obj,'endpoint') || isCellStyle(obj,'style_plus') || isCellStyle(obj,'rhombus')){
		var tmp=obj.getAttribute('task_id',-1);
		if(tmp<0)
			$.post('/getKey',function(taskid){
				obj.setAttribute('task_id',taskid);
			});
	}
	if(!isConfigurableNode(obj)){
		//$('#taskconfig').html('');
		curent_object=null;
		$("#button_config_task").hide();
		if($( "#boxtaskconfig" ).is(":visible"))
			$('#taskconfig').html('Please select a task node to config');
	}else{
		curent_object=obj;
		$("#button_config_task").show();
		if($( "#boxtaskconfig" ).is(":visible")){
			loadTaskConfig();
		}
	}
}

function loadTaskConfig(){
	if(!curent_object) return;
	var style=curent_object.style;
	curent_task=curent_object.getAttribute('task_id');	
	if(curent_task == undefined){
		curent_task = -1;
	}

	param = {
		'style' : style,
		'taskid' : curent_task
	}

	sendAjaxNotMessage('/loadConfigTask', param, function(_data){
		if(_data.flowTask != null){
			_workFlow.setDataTaskConfig(_data);
		}	
	});
}

function highlightContainer(a)
{
    ed.graph.model.beginUpdate();
    try
    {
        var c;
        for (c in ed.graph.model.cells)
        {
            if(ed.graph.isSwimlane(ed.graph.model.cells[c]))
            {
                ed.graph.setCellStyles("highlight", a?"1":"0", [ed.graph.model.cells[c]]);
            }
        }
    }
    catch(err)
    {
        alert(err.message);
    }
    finally
    {
        ed.graph.model.endUpdate();
    }
}

function addSubnetworkListItem(cell)
{
    var DiagramName=cell.getAttribute("label");
    var list=document.getElementById("listSubnetwork");
    var entry = document.createElement('option');
    entry.appendChild(document.createTextNode(DiagramName));
    entry.setAttribute("cell_id",cell.id);
    entry.addEventListener('click',function(){
        for(var i=0;i<entry.parentElement.children.length;i++)
        {
            var cID=entry.parentElement.children[i].getAttribute("cell_id");
            if(cID!=cell.id)
            {
                ed.graph.setCellStyles("highlight", "0", [ed.graph.model.getCell(cID)]);
            }
        }

        currentSubnetworkID=cell.id;
        justclicksubnetwork=true;
        ed.graph.setCellStyles("highlight", true, [cell]);
    },false);
    list.appendChild(entry);
}

function updateSubnetworksList()
{
    var elements = document.getElementById("listSubnetwork").options;
    for(var i = 0; i < elements.length; i++)
    {
        var cID=elements[i].getAttribute("cell_id");
        if(!ed.graph.model.getCell(cID))
        {
            document.getElementById("listSubnetwork").remove(i);
        }
    }
}

var outline;
function deleteWorkFlow(sId)
{
    if(!confirm("Are you really want to delete this diagram?")) return;

    param = {
			'ID' : sId
	}

	sendAjax('/deleteWorkFlow', param, function(data){
		_workFlow.listData(data);
	});
}

function stopWorkFlow(sId)
{
    param = {
			'ID' : sId
	}

	sendAjax('/stopWorkFlow', param, function(data){
		_workFlow.listData(data);
	});
    if(parent) parent.loadTasksCounting();
}


function runWorkFlow(sId)
{
    param = {
			'ID' : sId
	}

	sendAjax('/runWorkFlow', param, function(data){
		_workFlow.listData(data);
	});

    if(parent) parent.loadTasksCounting();
}

function loadSavedDiagram(sId,sName){
	
	setCurrentDiagramId(sId);
	param = {
		'ID' : sId
	} 

 	sendAjax('/getXMLCodeWF', param, function(data){
 		setCurrentDiagramName(data.result.NAME);
 		setCurrentDiagramIntro(data.result.INTRO);
 		//setCurrentDiagramIsrun(data.result.ISRUN);
		loadDiagramFromXML(data.result.DATA);
		_workFlow.hideBoxDiagrams();
	}); 
}

function loadDiagramFromXML(xmlcode){
	var doc = mxUtils.parseXml(xmlcode);
	var dec = new mxCodec(doc);
	_workFlow.clearGraph();
	dec.decode(doc.documentElement, ed.graph.getModel());
	ed.graph.center(true,true);
	ed.graph.refresh();
}

var defaultDiagramName="[Untitled Diagram]";
var defaultDiagramId= 0;
var defaultDiagramIntro= "";
var isrun= false;
var currentDiagramName=defaultDiagramName;
var currentDiagramId = defaultDiagramId;
var currentDiagramIntro = defaultDiagramIntro;
var currentDiagramIsrun = isrun;

function setCurrentDiagramName(s)
{
    currentDiagramName=s;
    document.getElementById("diagramName").innerHTML=currentDiagramName;
}

function setCurrentDiagramId(i)
{
    currentDiagramId = i;
}

function setCurrentDiagramIntro(info)
{
	currentDiagramIntro = info;
}

function setCurrentDiagramIsrun(isrun)
{
	currentDiagramIsrun = isrun;
}

var currentSubnetworkID;
var justclicksubnetwork=false;
function listSubnetworkClick()
{
    if(justclicksubnetwork){justclicksubnetwork=false; return;}
    if(currentSubnetworkID)
    {
        ed.graph.setCellStyles("highlight", "0", [ed.graph.model.getCell(currentSubnetworkID)]);
    }
    var elements = document.getElementById("listSubnetwork").options;
    for(var i = 0; i < elements.length; i++){
        elements[i].selected = false;
    }
}

function saveDiagram(saveAs){
    try{
		$("#listSavedDiagrams").html("Loading...");
		
		if(saveAs == "addNew"){
			loadSaveForm(true);
		}else{
			loadSaveForm(false);
		}
    }
    catch(err){
        alert(err.message);
    }
}

function doButtonSaveWorkflow(isSaveAs){
	currentDiagramName=$('#txt_name').val();
	currentDiagramId=$('#txt_id').val();
	currentDiagramIntro=$('#txt_intro').val();
	
	if(currentDiagramName=="" || !currentDiagramName){
		return false;
	}
	var isbegin=0; var isend=0;
	for (var c in ed.graph.model.cells){
		var Cell=ed.graph.model.cells[c];
		if(Cell.isVertex()){
			Cell.setAttribute('prev_task_config','');
			Cell.setAttribute('next_task_config','');
		}
	}
	for (var c in ed.graph.model.cells){
		var Cell=ed.graph.model.cells[c];
		if(Cell.style=='endpoint'){
			isend++;
			Cell.setAttribute('isend',1);
		} 
		if(Cell.style=='beginpoint'){
			isbegin++;
			Cell.setAttribute('isbegin',1);
		}
		if(Cell.isEdge()){
			_source=Cell.source.getAttribute('task_id');
			_target=Cell.target.getAttribute('task_id');

			var next_=Cell.source.getAttribute('next_task_config','')+_target+',';
			Cell.source.setAttribute('next_task_config',next_);

			var prev_=Cell.target.getAttribute('prev_task_config','')+_source+',';
			Cell.target.setAttribute('prev_task_config',prev_);
		}
	}
	var enc = new mxCodec();
	var node = enc.encode(ed.graph.getModel());
	var currentXML=mxUtils.getPrettyXml(node);
	
	if(isbegin==0 || isend==0){
		alert('Not found begin node or end node');
		return false;
	}
	if(isbegin>1){
		alert('Workflow can not containt more than one begin node')
		return false;
	}			
	var id = (isSaveAs)?null:currentDiagramId;
	var add = 0;
	if(isSaveAs){
		add = 1
	}
	
	param = {
		'ID' : id,
		'NAME' : currentDiagramName,
		'INTRO' : currentDiagramIntro,
		'ADD' : add,
		'KEY' : currentXML
	}

	sendAjax('/workflowSave', param, function(data){
		if($.isNumeric(data)){
			setCurrentDiagramId(data);
			loadSavedDiagram(data);
		}
		setCurrentDiagramName(currentDiagramName);
		$("#boxSavedDiagrams").html('');
		$("#boxSavedDiagrams").dialog('close');
	});
}

function isConfigurableNode(obj){
	return !(obj.isEdge() || obj.style.indexOf("swimlane") > -1 || obj.style.indexOf("beginpoint") > -1 || obj.style.indexOf("endpoint") > -1);
}

function find_taskid_byid(node_task,id){
	for(var j=0;j<node_task.length;j++){
		if($(node_task[j]).parent().attr('id')==id){
			return $(node_task[j]).parent().attr('task_id');
		}
	}
	return false;
}

function showBoxTaskConfig(){
	_workFlow.resetFormConfig();
	
	if(!curent_object) return;
	if(currentDiagramId<=0 || currentDiagramId==""){
		_alert("You need to save workflow first");
		return;
	}
	if(isConfigurableNode(curent_object)){
		$("#task_config").html('');
		$( "#boxtaskconfig" ).dialog({
			width: 550,
			modal: false,
			title: "Config a task",
			buttons: {
				Save: function() {
					if(!curent_object) {
						alert("Nothing to save");
						return;
					}
					
					//get next/prev config using XML
					var encoder = new mxCodec();
					var node = encoder.encode(ed.graph.getModel());
					var xml=mxUtils.getPrettyXml(node);
					var node_task=$(xml).find('mxCell[vertex=1]');
					var relation_task=$(xml).find('mxCell[edge=1]');
					var n=relation_task.length;
					var id=curent_object.id;
					var next_config='';
					var prev='';
					var _task={};
					for(var i=0;i<n;i++){
						var this_r=$(relation_task[i]);
						if(id==this_r.attr('source')){
							var _target=find_taskid_byid(node_task,this_r.attr('target'));
							if(_target)
							next_config+=_target+',';
						}
					}
					curent_object.setAttribute('next_task_config',next_config);
					_task['next_task_config']=next_config;
					
					for(var i=0;i<n;i++){
						var this_r=$(relation_task[i]);
						if(id==this_r.attr('target')){
							var _source=find_taskid_byid(node_task,this_r.attr('source'));
							if(_source)
							prev+=_source+',';
						}
					}
					curent_object.setAttribute('prev_task_config',prev);
					_task['prev_task_config']=prev;
					
					_task['id']=$('#txt_task_id').val();
					_task['name']=$('#txt_task_name').val();
					if(isCellStyle(curent_object,'style_plus') || isCellStyle(curent_object,'rhombus') || isCellStyle(curent_object,'style_email')){
						_task['runby']=1;
						_task['user']='';
						_task['task_group']='';
						if(isCellStyle(curent_object,'style_email')){
							_task['task_code']= 'EMAIL';
						}else{
							_task['task_code']=(isCellStyle(curent_object,'style_plus')?'NODE_COMBINE':'NODE_CONDITION');
						}
					}else{
						_task['runby']=$('#task_run_type').val();
						_task['user']=$('#txt_user').val();
						_task['task_group']=$('#choice_task_group').val();
						_task['task_code']=$('#choice_task').val();
					}
					task_config={};
					if(_task['task_code']=='ALLOC_CHECK' || _task['task_code']=='ALLOC_RUN'){
						task_config['network']=$('#cbo_network').val();
						task_config['jobid']=$('#cbo_jobs').val();
						task_config['type']=$('#cbo_datetype').val();
						if(task_config['type']=='date'){
							task_config['from']=$('#txt_from').val();
							task_config['to']=$('#txt_to').val();
						}
						task_config['email']=$('#txt_email').val();
					}else if(_task['task_code']=='VIS_REPORT'){
						task_config['reportid']=$('#cbo_Reports').val();
						task_config['facility']=$('#cbo_Facility').val();
						task_config['type']=$('#cbo_datetype').val();
						if(task_config['type']=='date'){
							task_config['from']=$('#txt_from').val();
							task_config['to']=$('#txt_to').val();
						}
						task_config['email']=$('#txt_email').val();
					}else if(_task['task_code']=='FDC_EU'){
						task_config['facility']=$('#cbo_Facility').val();
						task_config['eugroup_id']=$('#cbo_eugroup').val();
						task_config['freq']=$('#cbo_freq').val();
						task_config['phase_type']=$('#cbo_phasetype').val();
						task_config['event_type']=$('#cbo_eventtype').val();
						task_config['alloc_type']=$('#cbo_alloctype').val();
						task_config['plan_type']=$('#cbo_plantype').val();
						task_config['forecast_type']=$('#cbo_forecasttype').val();
						task_config['type']=$('#cbo_datetype').val();
						if(task_config['type']=='date'){
							task_config['from']=$('#txt_from').val();
							task_config['to']=$('#txt_to').val();
						}
						task_config['email']=$('#txt_email').val();
					}else if(_task['task_code']=='FDC_FLOW'){
						task_config['facility']=$('#cbo_Facility').val();
						task_config['freq']=$('#cbo_freq').val();
						task_config['phase_type']=$('#cbo_phasetype').val();
						task_config['type']=$('#cbo_datetype').val();
						if(task_config['type']=='date'){
							task_config['from']=$('#txt_from').val();
							task_config['to']=$('#txt_to').val();
						}
						task_config['email']=$('#txt_email').val();
					}else if(_task['task_code']=='NODE_CONDITION'){
						
						//========== config data for condition block ====================
						task_config['formula_group']=$('#choose_formula_group').val();
						task_config['formula_id']=$('#choose_formula').val();
						task_config['type']=$('#cbo_datetype').val();
						
						if(task_config['type']=='date'){
							task_config['from']=$('#txt_from').val();
							task_config['to']=$('#txt_to').val();
						}
						var arr=[]	;
						$(".condition_item").each(function(){
							if($(this).find("#txt_condition").val()!='')
							arr.push({condition:$(this).find("#txt_condition").val(),target_task_id:$(this).find("select").val()});
						})
						task_config['condition']=arr;
					}else if(_task['task_code']=='EMAIL'){	

						upfile();
					    task_config['from'] = $('#fromEmail').val();
						task_config['to'] = $('#toEmail').val();
						task_config['subject'] = $('#txt_subject').val();
						task_config['content'] = $('#txt_mess').val();
						task_config['file'] = $('#hiddenFile').val();
											    
					}else{

					}
					
					task_config=JSON.stringify(task_config);
					console.log(task_config);
					curent_object.setAttribute('task_config',task_config);
					
					task=JSON.stringify(_task);
					curent_object.setAttribute('task_data',task);
					curent_object.setAttribute('label',$('#txt_task_name').val());
					if(currentDiagramName=="" || !currentDiagramName || currentDiagramName ==defaultDiagramName){
						
					}else{
						var enc = new mxCodec();
						var node = enc.encode(ed.graph.getModel());
						var currentXML=mxUtils.getPrettyXml(node);
						
						param = {
							'wfid' : currentDiagramId,
							'taskdata' : task,
							'taskconfig' : task_config,
							'key' : currentXML
						}

						sendAjax('/workflowSaveTask', param, function(_data){
							var data = _data.result;
							curent_object.setAttribute('task_id',data.ID);
						});
					}
					ed.graph.refresh();										
					$( this ).dialog( "close" );
				},
				Close: function() {					
					$( this ).dialog( "close" );
				}
			},
		});
		var style=curent_object.style; 
		if(style == 'rhombus' || style == 'style_plus'){
		 	$('#userRun').css('display','none');
			$('#runtask').css('display','none');
			$('#formula').css('display','block');	
			$('#frm_email').css('display','none');
			$('#cbo_datetype').change(function(){
				if($(this).val()=='date'){
					$('#date_config').show();
				}else{
					$('#date_config').hide();
				}
				$('input[type=date]').css('height', 'auto');
				$('input[type=date]').css('width', '130');
			});		
		}else{
			if(style == 'style_email'){
				$('#userRun').css('display','block');
				$('#runtask').css('display','none');
				$('#formula').css('display','none');	
				$('#frm_email').css('display','');				
			}else{
				$('#formula').css('display','none');
				$('#userRun').css('display','block');
				$('#runtask').css('display','block');
				$('#frm_email').css('display','none');
			}	
		}

		$('#txt_file').on('change', prepareUpload);

		var n=curent_object.getEdgeCount();
		var s="";
		for(i=0;i<n;i++){
			var e=curent_object.getEdgeAt(i);
			var to=e.getTerminal(false);
			if(to.id!=curent_object.id)
				s+="<option value='"+to.getAttribute('task_id')+"'>"+to.getAttribute('label')+"</option>";
		}
		$(".cbo_target_task").html(s);
		var target_task=$(".cbo_target_task");
		for(i=0;i<target_task.length;i++){
			var id=$(target_task[i]).attr('id');
			$(target_task[i]).val(id)
		}
		
		loadTaskConfig();
	}else{
		$("#task_config").html('Please select a task to config!');
	}
}

function upfile(){
	if(filesToUpload){
		
	    var formData = new FormData();

	    // Add selected files to FormData which will be sent
        $.each(filesToUpload, function(key, value){
            formData.append(key,filesToUpload[1]);
        }); 

        $.ajax({
	        type: "POST",
	        url: '/upFile',
	        data: formData,
	        processData: false,
	        contentType: false,
	        dataType: 'json',
	        success: function(data)
			{
				$('#hiddenFile').val(data.files);
				
			}
    	}); 
	}
}

function loadSaveForm(isAddNew){
	var title=(isAddNew?'Add New Workflow':'Edit Workflow: '+currentDiagramName);
	$( "#frmSave" ).dialog({
		height: 270,
		width: 550,
		modal: true,
		title: title,
		buttons: {
			Save: function() {
				doButtonSaveWorkflow(isAddNew);
				$( this ).dialog( "close" );
			},
			Close: function() {
				$( this ).dialog( "close" );
			}
		},
	});
	$( "input, textarea" ).css( "height",'auto' );	
	$( "input, textarea" ).css( "width",300 );
	$( "input[type=radio]" ).css( "width",'auto' );
	
	if(currentDiagramName !== "[Untitled Diagram]"){
		$("#txt_name").val(currentDiagramName);
	}else{
		$("#txt_name").val("");
	}

	if(currentDiagramId != 0){
		$("#txt_id").val(currentDiagramId);
	}else{
		$("#txt_id").val(0);
	}

	if(currentDiagramIntro != ""){
		$("#txt_intro").val(currentDiagramIntro);
	}else{
		$("#txt_intro").val("");
	}

	if(currentDiagramIsrun == "yes"){
		$("#opt_yes").prop("checked", true);
	}else{
		$("#opt_no").prop("checked", true);
	}
}

window.onbeforeunload = function() { return mxResources.get('changesLost'); };
</script>
<body onLoad="new mxApplication('/config/diagrameditor-workflow.xml?2');">
	<div id="box_cell_image" style="display: none">
		<span id="box_cell_image_input"> <br> Input image URL <input
			type="text" id="txt_cell_image_url" style="width: 470px"> <br> <br>
			or Upload from your computer <input type="file" name="files[]"
			multiple id="file_cell_image_url" style="width: 390px"> <br> <br> or
			<input type="button" onclick="pick_cell_image()"
			value="Pick available image">
		</span>
		<div id="box_pick_cell"
			style="display: none; width: 100%; height: 100%"></div>
	</div>


	<td valign="top" align="center">
		<table border="0" cellpadding="0" cellspacing="0" id="table1"
			width="100%">
			<tr>
				<td style="display: none" height="20">
					<div style="display: none" id="header_">&nbsp;</div>



					<div id="mainActions"
						style="display: none; width: 100%; padding-top: 8px; padding-left: 24px; padding-bottom: 8px;">
					</div>
					<div style="display: none; float: right; padding-right: 36px;">
						<input id="source" type="checkbox" />Source
					</div>
					<div id="selectActions"
						style="display: none; width: 100%; padding-left: 54px; padding-bottom: 4px;">
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="0" cellspacing="0" id="table2"
						width="100%">
						<tr>
							<td style="border: none; width: 280px;"><span
								style="font-size: 10pt; padding-left: 10px;" id="diagramName">[Untitled
									Diagram]</span></td>
							<td>

								<table border="0" cellpadding="0" id="table17" cellspacing="4"
									height="30">
									<tr>
										<td onClick="_workFlow.newWorkFlow()" width="60"
											class="xbutton">New</td>
										<td onClick="_workFlow.loadWorkFlow()" width="60"
											class="xbutton">Load</td>
										<td id="buttonSave" onMouseOut="$('#buttonSaveAs').hide();"
											onMouseOver="$('#buttonSaveAs').show();" width="60"
											class="xbutton"><span id="buttonSave_text"
											onClick="saveDiagram()">Save</span>
											<div class="xbutton"
												style="padding: 5px; display: none; position: absolute; width: 64px; z-index: 101; margin-left: 0px; margin-top: 4px; border: 2px solid #666"
												id="buttonSaveAs">
												<span onClick="saveDiagram('addNew')">Save As</span>
											</div></td>
										<td onClick="buttonActionClick('print')" width="60"
											class="xbutton">Print</td>
										<td style="display: none"
											onClick="buttonActionClick('exportImage')" width="60"
											class="xbutton">Export</td>
										<td align="right" width="70"><span style="font-size: 8pt">
												Line color</span></td>
										<td onClick="changeLineColor('red')" width="40"
											class="xbutton" style="background-color: #FF0000"></td>
										<td onClick="changeLineColor('blue')" width="40"
											class="xbutton" style="background-color: #0066CC"></td>
										<td onClick="changeLineColor('#008800')" width="40"
											class="xbutton" style="background-color: #008800"></td>
										<td onClick="changeLineColor('#CC6600')" width="40"
											class="xbutton" style="background-color: #CC6600"></td>
										<td style="display:; text-align: center" width="100"
											class="xbutton"><span
											onClick="$('#boxSubnetworks').toggle();">Swimlanes</span>
											<div
												style="display: none; position: absolute; width: 174px; height: 133px; z-index: 100; margin-left: -0px; margin-top: 5px; border: 2px solid #666"
												id="boxSubnetworks">
												<table border="0" cellpadding="0" cellspacing="0"
													width="100%" id="table22" height="100%">
													<tr>
														<td bgcolor="#c0c0c0" style="border: 1px solid #666"><select
															onclick="listSubnetworkClick()" id="listSubnetwork"
															style="width: 100%; height: 100%; border: 0px solid #ffffff; overflow: auto; background: #c0c0c0; font-family: Verdana; font-size: 8pt; color: #000"
															name="sometext" multiple="multiple">
														</select></td>
													</tr>
												</table>
											</div></td>
										<td width="50">&nbsp;</td>
										<td class="ebutton" onClick="buttonActionClick('copy')"><img
											src="/images/copy.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('cut')"><img
											src="/images/cut.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('paste')"><img
											src="/images/paste.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('delete')"><img
											src="/images/delete.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('undo')"><img
											src="/images/undo.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('redo')"><img
											src="/images/redo.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('rotate')"><img
											src="/images/rotate.png"></td>
										<td width="70" align="right">Export as</td>
										<td class="xbutton" width="30"
											onClick="_workFlow.exportImage()">PNG</td>
										<td id="button_config_task" class="xbutton" width="50"
											onClick="showBoxTaskConfig();">Config</td>
										<td class="xbutton" width="30" onClick="_workFlow.showXML();">XML</td>
									</tr>
								</table>

							</td>
							<td>&nbsp;</td>
						</tr>
					</table>
					<table border="0" cellpadding="0" cellspacing="0" id="table21"
						width="100%">
						<tr>
							<td>&nbsp;</td>
							<td id="tdToolBox" width="260" valign="top" style="border: none">
								<script>
	var filesToUpload = [];
	$('input[type=file]').on('change', prepareUpload);
	function prepareUpload(event)
	{
	  var files = event.target.files || event.originalEvent.dataTransfer.files;
	    // Itterate thru files (here I user Underscore.js function to do so).
	    // Simply user 'for loop'.
	    $.each(files, function(key, value) {
	        filesToUpload.push(key, value);
	    });
	}
	function pick_cell_image(){
		$("#box_cell_image_input").hide();
		$("#box_pick_cell").show();
		$("#box_pick_cell").html("Loading...");
		
		$("#box_pick_cell").html('No available image');
	}
	function set_cell_image(cell,url){
		cell.style="shape=image;html=1;verticalLabelPosition=bottom;verticalAlign=top;imageAspect=1;image="+url;
		ed.graph.refresh();
	}
	function setCellImage(cell){
		$("#box_cell_image_input").show();
		$("#box_pick_cell").hide();
		$("#txt_cell_image_url").val("");
		$("#file_cell_image_url").val("");
		$( "#box_cell_image" ).dialog({
			height: 300,
			width: 600,
			modal: true,
			title: "Set Image",
			buttons: {
				"OK": function(){
					var url=$("#txt_cell_image_url").val().trim();
					if(url!=""){
						set_cell_image(cell,url);
						$("#box_cell_image").dialog("close");
					}else{					
						if(filesToUpload){							
						    var formData = new FormData();

						    // Add selected files to FormData which will be sent
						    if (filesToUpload) {
						        $.each(filesToUpload, function(key, value){
						            formData.append(key, value);
						        });        
						    }
						    
						    showWaiting("Uploading image...");
							$.ajax({
						        type: "POST",
						        url: '/uploadImg',
						        data: formData,
						        processData: false,
						        contentType: false,
						        dataType: 'json',
						        success: function(data, textStatus, jqXHR)
								{
									hideWaiting();
									if(typeof data.error === 'undefined')
									{
										// Success so call function to process the form
										set_cell_image(cell,data.files);
										filesToUpload = [];
										$("#box_cell_image").dialog("close");
									}
									else
									{
										// Handle errors here
										alert('Error: ' + data.error);
										console.log('ERRORS: ' + data.error);
									}
								},
								error: function(jqXHR, textStatus, errorThrown)
								{
									filesToUpload = [];
									hideWaiting();
									// Handle errors here
									alert('Error: ' + textStatus);
									console.log('ERRORS: ' + textStatus);
									// STOP LOADING SPINNER
								}
						        
						    });
						}
					}
				},
				"Cancel": function(){
					$("#box_cell_image").dialog("close");
					filesToUpload = [];
				}
			}
		});
	}
	function showIcons()
	{
		$("#buttonShowIcons").attr('class','tabselected');
		$("#buttonShowProperties").attr('class','tabnormal');
		$("#properties").hide();
		$("#icons").show();
	}
	function showProperties()
	{
		$("#buttonShowIcons").attr('class','tabnormal');
		$("#buttonShowProperties").attr('class','tabselected');
		$("#icons").hide();
		$("#properties").show();
	}

	function addCondition(){
		o=$("#condition0").clone().css("display","block");
		$("#form-group-condition").append(o);
		$("#taskconfig").scrollTop($("#taskconfig")[0].scrollHeight);

	}
	function deleteCondition(o){
		$(o).parent().remove();
	}
</script>
								<table border="0" cellpadding="0" cellspacing="0" width="100%"
									id="table3" height="100%">
									<tr>
										<td height="20" bgcolor="#666">
											<table border="0" cellpadding="0" width="100%" id="table10"
												cellspacing="1" height="100%">
												<tr>
													<td id="buttonShowIcons" width="46" onClick="showIcons()"
														class="tabselected" bgcolor="#959596">Icons</td>
													<td id="buttonShowProperties" class="tabnormal"
														onClick="showProperties()" width="79">&nbsp; Properties</td>
													<td>&nbsp;</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td height="300" style="border: 1px solid #666"
											bgcolor="#C0C0C0" valign="top">
											<div id="properties" style="display: none;"></div>
											<div id="icons"
												style="width: 260px; height: 100%; overflow: auto;">
												<div style="padding: 10px;" id="toolbar"></div>
											</div>
										</td>
									</tr>
									<tr>
										<td height="10"></td>
									</tr>
									<tr>
										<td>
											<table border="0" cellpadding="0" cellspacing="0"
												width="100%" id="table20" height="100%">
												<tr>
													<td height="15px" bgcolor="#666">
														<table border="0" cellpadding="0" width="100%"
															id="table21" cellspacing="1">
															<tr style="height: 10px;">
																<td><font size="1" color="#F8F8F8"> &nbsp;<b>Zoom</b></font></td>
																<td act="zoomIn" id="buttonZoomIn"
																	onClick="buttonActionClick('zoomIn')" width="30"
																	height="15" class="abutton">in</td>
																<td act="zoomOut" onClick="buttonActionClick('zoomOut')"
																	width="30" height="15" class="abutton">out</td>
																<td act="actual"
																	onClick="buttonActionClick('actualSize')" width="30"
																	height="15" class="abutton">1:1</td>
																<td act="fit" onClick="buttonActionClick('fit')"
																	width="30" height="15" class="abutton">fit</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td	style="display:; background: #fff; border: 1px solid #666; height: 140px;">
														<div id="outlineContainer"	style="background: #fff;  height: 137px;"></div>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
							<td width="10" style="cursor: pointer" id="tdShowHideToolBox"><img
								id="imgShowHideToolBox" width=10 src='/images/arrow_left.png'></td>
							<td width="1000">
								<div id="graph"
									style="position: relative; height: 499px; width: 1051px; cursor: default; overflow: hidden; border: 1px solid #666; background-image: url('/images/bg.png')">
									<!-- Graph Here -->
									<center id="splash" style="padding-top: 230px;">
										<img src="/images/loading.gif">
									</center>
								</div>
							</td>
							<td>&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
	<div id='boxtaskconfig' style="display: none">
		<div id="taskconfig"
			style="overflow-y: auto; height: 430px; padding: 5px;">
			<div id="tab_aplication" class="tab-pane fade">
				<fieldset>
					<legend>General Task</legend>
					<div id="tab_general_task" class="tab-pane fade in active">
						<div class="form-group">
							<label class='col-md-2 control-label'>Name:</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="txt_task_name"
									value=''> <input type="hidden" class="form-control"
									id="txt_task_id" value=''>
							</div>
						</div>

						<div class="form-group" id="userRun">
							<label class='col-md-2 control-label'>Run Type*:</label>
							<div class="col-md-4">
								<select class='form-control' id='task_run_type'>
									<option value='1'>Run by system</option>
									<option value='2'>Run by user</option>
								</select>
							</div>
							<div class='col-md-6' id='box-user'>
								<div class='row'>
									<label class='col-md-3 control-label'>User List*:</label>
									<div class='col-md-9'>
										<div class="btn-group">
											<input class='form-control' id='txt_user' name='txt_user'
												value ='' style="width:350px;"/>
											<button type="button" class="btn btn-primary"
												onclick="_workFlow.listUser();" id='btn_choice_user'>Select
												user</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</fieldset>

				<fieldset>
					<legend>Configure Task</legend>
					<div id="tabbtn_choice_user" class="tab-pane fade">
						<div class="form-group">
							<div id='runtask'>
								<div>
									<label class='col-md-2'>Run Task:</label>
								</div>
								<select id='choice_task_group' class='form-control'
									onchange="_workFlow.changeCboRunTask();">
									<option value='normal-fun'>Select Task Group</option>
									@foreach($ebfunctions as $t)
									<option value="{!!$t->FUNCTION_CODE!!}">{!!$t->FUNCTION_NAME!!}</option>
									@endforeach

								</select> <select id='choice_task' class='form-control'
									onchange="_workFlow.loadFormSetting();">
								</select>

								<div id='config_panel'>
									<fieldset>
										<legend>Setting</legend>
										<div id='task_config'></div>
									</fieldset>
								</div>
							</div>

							<div id='formula'>
								<div>
									<label class='col-md-2'>Formula:</label>
								</div>
								<select id='choose_formula_group' class='form-control'>
									<option value='normal-fun'>Select a formula</option>
									@foreach($foGroup as $fo)
									<option value="{!!$fo->ID!!}">{!!$fo->NAME!!}</option>
									@endforeach

								</select> <select id='choose_formula' class='form-control'>
								</select>

								<div class="form-group">
									<label class='col-md-2 control-label'>Set date:</label>
									<div class="col-md-5">
										<select id='cbo_datetype'>
											<option value='date'>Date</option>
											<option value='day' selected>This Day</option>
											<option value='month'>This Month</option>
										</select> <span id='date_config' style="display: none"> 
										<input type="date" class="form-control" name="txt_from"	id="txt_from"> 
										<input type="date" class="form-control"	name="txt_to" id="txt_to">
										</span>
									</div>
								</div>

								<div class="form-group" id="form-group-condition">
									<label class='col-md-2 control-label'>Condition</label>
									<div class="condition_item" id="condition0"
										style="display: block">
										<input type="text" class="form-control" name="txt_condition"
											id="txt_condition" placeholder='$r>1 and $r<=10'> >> <select
											class='cbo_target_task'>
										</select> <input type="button" onclick="deleteCondition(this)"
											style="margin: 2px 2px; width: 70px;" value="Delete">
									</div>
								</div>
								<input type="button" onclick="addCondition()"
									style="margin: 2px 0px; width: 70px;" value="Add">
							</div>
							
							<div id="frm_email" style="display: none;">
		<div class="form-group">
			<label class='col-md-2 control-label'>From</label>
			<div class="col-md-10">
				<input type="text" class="form-control" name="txt_from"
					id="fromEmail">
			</div>
		</div>
		<div class="form-group">
			<label class='col-md-2 control-label'>To:</label>
			<div class="col-md-10">
				<input type="text" class="form-control" name="txt_to" id="toEmail">
			</div>
		</div>
		<div class="form-group">
			<label class='col-md-2 control-label'>Subject:</label>
			<div class="col-md-10">
				<input type="text" class="form-control" name="txt_subject"
					id="txt_subject">
			</div>
		</div>
		<div class="form-group">
			<label class='col-md-2 control-label'>Contents:</label>
			<div class="col-md-10">
				<textarea id='txt_mess' style="width:440px;" class='form-control' name="txt_mess"
					rows='4'></textarea>
			</div>
		</div>
		<div class="form-group">
			 <label class='col-md-2 control-label'>Attach file:</label>
			<div class="col-md-10">
				<input type="hidden" class="form-control" name="hiddenFile"id="hiddenFile">
				<input type="file" name="files[]" multiple id="txt_file" style="width: 390px; height: auto;">
			</div>
		</div>
	</div>
						</div>
					</div>
				</fieldset>
			</div>
		</div>
	</div>
	</div>
	<div id="listSavedDiagrams" style="display: none;"></div>
	<div id="listUser" style="display: none;">
		<ul>
			@foreach($user as $t)
			<li><label clas='username'><input type='checkbox' name='chk'
					class='chk' value='{!!$t->USERNAME!!}' /> {!!$t->USERNAME!!}</label>
				(<span class='name'>{!!$t->NAME!!}</span>)</li> @endforeach
		</ul>
	</div>

	<div id="frmSave" style="display: none;">
		<div class="form-group">
			<input type="hidden" id="txt_id" value=''> <label
				class='col-md-2 control-label'>Name*:</label>
			<div class="col-md-10">
				<input type="text" class="form-control" id="txt_name">
			</div>
		</div>
		<div class="form-group">
			<label class='col-md-2 control-label'>Description:</label>
			<div class="col-md-10">
				<textarea class='form-control' rows=3 id='txt_intro'></textarea>
			</div>
		</div>
	</div>
</body>
@stop
