<?php
$currentSubmenu = 'formula';
?>

@extends('core.bsconfig')
@section('title')
<div class="title">FORMULA EDITOR</div>
@stop 
@section('group')
<div id="controlSearch">

	<div>
		<b>&nbsp;</b>
	</div>
	<b>Group</b>
	<select id="cboFormulaGroups" onchange="_formula.loadFormulasList();">
		@foreach($fo_group as $re)
		<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> @endforeach
	</select>
	
	<button onclick="_formula.renameGroup()">Rename</button>
	<button onclick="_formula.deleteGroup()">Delete</button>
	<button onclick="_formula.newGroup()">New group</button>
	
	<span style="float: right; margin-right: 5px">
		<button onclick="_formula.showAddFormula()">Add Formula</button>
		<button onclick="_formula.saveFormulaOrder()">Save sort order</button>
		<button onclick="_formula.editFormula()">Edit</button>
		<button onclick="_formula.testFormula()">Test</button>
		<button onclick="_formula.deleteFormula()">Delete</button>
	</span>
</div>
@stop
@section('content')
<link rel="stylesheet" href="/common/css/admin.css">
<link rel="stylesheet" href="/common/css/allocation/style.css"/>
<script type="text/javascript" src="/common/js/splitter.js"></script>
<script type="text/javascript">

$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	});

	$('#controlSearch').css('border-bottom', '0px'); 

	$("#boxEditVar").hide();
	
	$('#cboFormulaGroups').change();

	$("#tableFormula tbody").sortable({
		update: function( event, ui ) {
			var k=0;
			$("#tableFormula .formula_item").each(function(){
				k++;
				if($(this).attr('new_order')!=k){
					$(this).find('td:first-child').html(k);
					$(this).attr('new_order',k);
				}
			});
		}
	});
	$("#tableVars tbody").sortable({
		update: function( event, ui ) {
			var k=0;
			$("#tableVars .var_item").each(function(){
				k++;
				if($(this).attr('new_order')!=k){
					$(this).find('td:first-child').html(k);
					$(this).attr('new_order',k);
				}
			});
		}
	});

	var d = new Date();
	$("#test_formula_occur_date").val(""+zeroFill(1+d.getMonth(),2)+"/01/"+d.getFullYear());
	$('#txtBeginDate, #txtEndDate').val(""+zeroFill(1+d.getMonth(),2)+"/01/"+d.getFullYear());
	$( "#test_formula_occur_date, #txtBeginDate, #txtEndDate" ).datepicker({
	    changeMonth:true,
	     changeYear:true,
	     dateFormat:"mm/dd/yy"
	});	
});

var _formula = {

		curPUID : 0, 
		curAreaID : 0, 
		curFacilityID : 0, 
		curObjectID : 0,
		current_formula_id :0,
		current_var_id : 0,

		cboChange : function(id){
			var table = ""
			var cboSet = ""; 
				
			if(id == "cboUserPU"){
				table = "LoArea";
				cboSet = "cboUserArea"
			}

			if(id == "cboUserArea"){
				table = "Facility";
				cboSet = "cboUserFacility";
			}

			if(table != ""){
				value =  $('#'+id).val();
				_formula.cboOnchange(cboSet, value, table);
			}
		},
		loadObjects : function(){
			var objectType = $("#cboObjType").val();

			param = {
				'object_type' : objectType,
				'facility_id' : $("#cboUserFacility").val(),
				'product_type' : 0 //$('#cboUserPU').val()
			};
			
			$("#cboObjectName").prop("disabled", true); 
			sendAjaxNotMessage('/loadPlotObjects', param, function(data){
				_formula.loadObjectsName(data.objectName);
			});
		},
		loadObjectsName : function(data){
			var cbo = '';
			$('#cboObjName').html(cbo);
			cbo += ' <option value="" selected>(None)</option>';
			for(var v = 0; v < data.length; v++){
				cbo += ' 		<option value="' + data[v].ID + '">' + data[v].NAME + '</option>';
			}

			$('#cboObjName').html(cbo);
		},
		cancelEdit : function()
		{
			$("#boxEditFormula").dialog("close");
			$("#boxEditVar").hide();
		},
		renameGroup : function()
		{
			$("#d_group_name").val($("#cboFormulaGroups option:selected").text());
			var id=$("#cboFormulaGroups").val();
			
			$( "#dialog" ).dialog({
				width: 370,
				modal: true,
				title: "Rename Group",
				buttons: {
					"Rename": function(){
						var group_name=$("#d_group_name").val();
						if(group_name!=$("#cboFormulaGroups option[value="+id+"]").html())
						{														

							param = {
								'groupName': group_name, 
								'id': id
							};
							
							sendAjaxNotMessage('/editgroupname', param, function(data){
								_formula.cboFormulaGroups(data);
								$("#cboFormulaGroups").val(id);
							});							
						}
						$("#dialog").dialog("close");
						
					},
					"Cancel": function (){
						$("#dialog").dialog("close");
					}
				}
			});
		},
		newGroup : function()
		{
			$("#d_group_name").val('')
			$( "#dialog" ).dialog({
				width: 370,
				modal: true,
				title: "New Group",
				buttons: {
					"Create": function(){
						var group_name=$("#d_group_name").val();
						if(group_name!="")
						{	
							param = {
								'groupName': group_name
							};
							
							sendAjaxNotMessage('/addgroupname', param, function(data){
								_formula.cboFormulaGroups(data);
								$("#cboFormulaGroups").val(id);
							});							
						}
						$("#dialog").dialog("close");
						
					},
					"Cancel": function (){
						$("#dialog").dialog("close");
					}
				}
			})	
		},
		deleteGroup : function()
		{
			var id=$("#cboFormulaGroups").val();
			if(id)
			{
				if(!confirm("Are you sure to delete this group and all formula belong to it?")) return;

				param = {
					'id': id
				};
				
				sendAjaxNotMessage('/deletegroup', param, function(data){
					_formula.cboFormulaGroups(data);
					$('#cboFormulaGroups').change();
				});		
			}
		},
		cboFormulaGroups : function(data){
			var cbo = '';
			$('#cboFormulaGroups').html(cbo);
			for(var i = 0; i < data.length; i++){
				cbo += '<option value="'+ data[i].ID +'">'+ data[i].NAME +'</option>';
			}

			$('#cboFormulaGroups').html(cbo);
		},
		loadFormulasList : function()
		{
		    $('#bodyFormulasList').html('');

		    param = {
				'group_id': $("#cboFormulaGroups").val()
			};
			
			sendAjaxNotMessage('/getformulaslist', param, function(data){
				_formula.showFormula(data);
				$("#isvar").val(0);
				_formula.loadVarsList(0);
				$('#boxVarList').hide();
				var $r=$("#bodyFormulasList").find("#Qrowformula_"+_formula.current_formula_id);
				if(_formula.current_formula_id<=0)
				{
					_formula.current_formula_id=-1;
					_formula.current_var_id=-1;
					$("#bodyFormulasList tr").eq(0).trigger("click");
				} 
			});	
		},
		showFormula : function(data){
			var bgcolor="";
			var str = "";
			$('#bodyFormulasList').html(str);
			for(var i = 0; i < data.length; i++){
				if(i%2==0){
					bgcolor="#eeeeee";
				}else{
					bgcolor="#f8f8f8";
				}
				
				str += "<tr bgcolor="+bgcolor+" class='formula_item' rowid='"+data[i].ID+"' order='$row[ORDER]' new_order='"+checkValue(data[i].ORDER, -1)+"' id='Qrowformula_"+data[i].ID+"' style=\"cursor:pointer\" onclick=\"_formula.loadVarsList("+data[i].ID+",\'"+data[i].NAME+"')\">";
				str += "	<td align='center'>"+(i+1)+"</td>";
				str += "	<td><span id='Q_FormulaName_"+data[i].ID+"'>"+checkValue(data[i].NAME,"")+"</span>";

				str += "	<span style='display:none'>";
				str += "		<span id='Q_TableName_"+data[i].ID+"'>"+data[i].TABLE_NAME+"</span>";
				str += "		<span id='Q_ValueColumn_"+data[i].ID+"'>"+data[i].VALUE_COLUMN+"</span>";
				str += "		<span id='Q_IDColumn_"+data[i].ID+"'>"+data[i].OBJ_ID_COLUMN+"</span>";
				str += "		<span id='Q_ObjType_"+data[i].ID+"'>"+data[i].OBJECT_TYPE+"</span>";
				str += "		<span id='Q_ObjID_"+data[i].ID+"'>"+data[i].OBJECT_ID+"</span>";
				str += "		<span id='Q_FlowPhase_"+data[i].ID+"'>"+data[i].FLOW_PHASE+"</span>";
				str += "		<span id='Q_AllocType_"+data[i].ID+"'>"+data[i].ALLOC_TYPE+"</span>";
				str += "		<span id='Q_PUID_"+data[i].ID+"'>"+data[i].PRODUCTION_UNIT_ID+"</span>";
				str += "		<span id='Q_AreaID_"+data[i].ID+"'>"+data[i].AREA_ID+"</span>";
				str += "		<span id='Q_FAcilityID_"+data[i].ID+"'>"+data[i].FACILITY_ID+"</span>";
				str += "		<span id='Q_DateColumn_"+data[i].ID+"'>"+data[i].DATE_COLUMN+"</span>";
				str += "	</span>";
				str += "	</td>";
				str += "	<td>"+data[i].sLO+"</td>";
				str += "	<td>"+checkValue(data[i].TABLE_NAME,"")+"</td>";
				str += "	<td>"+checkValue(data[i].VALUE_COLUMN,"")+"</td>";
				str += "	<td><span id='Q_Formula_"+data[i].ID+"' style='word-wrap: break-word;'>"+checkValue(data[i].FORMULA,"")+"</span></td>";
				str += "	<td><span id='Q_BeginDate_"+data[i].ID+"'>"+checkValue(data[i].BEGIN_DATE,"")+"</span></td>";
				str += "	<td><span id='Q_EndDate_"+data[i].ID+"'>"+checkValue(data[i].END_DATE,"")+"</span></td>";
				str += "	<td><span id='Q_Comment_"+data[i].ID+"'>"+checkValue(data[i].COMMENT,"")+"</span></td>";
				str += "</tr>";
			}
			$('#bodyFormulasList').html(str);
		},
		
		loadVarsList : function(formula_id, formula_name)
		{
			if(_formula.current_formula_id==formula_id) return;
			_formula.current_formula_id=formula_id;
			
		    $('#bodyVarsList').html('');
			$("#formula_name").html(formula_name);
			
			if(formula_id<=0) return;
			_formula.reloadVarsList();
			$(".current_job").removeClass("current_job");
			$("#Qrowformula_"+formula_id).addClass("current_job");
		},
		reloadVarsList : function()
		{
			param = {
				'formula_id': _formula.current_formula_id
			};
			
			sendAjaxNotMessage('/getvarlist', param, function(data){
				_formula.showVariable(data);
			    $('#boxVarList').show();
			    $("#isvar").val(0);
			});			
		},
		showVariable : function(data){
			var str = "";
			var new_order = 0;
			$('#bodyVarsList').html(str);
			for(var i = 0; i < data.length; i++){
				if(data[i].ORDER) new_order = data[i].ORDER; else data[i].ORDER = -1;
				if(i % 2==0) bgcolor="#eeeeee"; else bgcolor="#f8f8f8";
				str += "<tr class='var_item' rowid='"+data[i].ID+"' order='"+data[i].ORDER+"' new_order='"+new_order+"' bgcolor='"+bgcolor+"' id='Qrowvar_"+data[i].ID+"'>";
				str += "<td align='center'>"+(i+1)+"</td><td>";
				str += "<span id='V_FormulaName_"+data[i].ID+"'>"+checkValue(data[i].NAME,"")+"</span>";
				str += "<span style='display:none'>";
				str += "<span id='V_Order_"+data[i].ID+"'>"+data[i].ORDER+"</span>";
				str += "<span id='V_StaticValue_"+data[i].ID+"'>"+data[i].STATIC_VALUE+"</span>";
				str += "<span id='V_TableName_"+data[i].ID+"'>"+data[i].TABLE_NAME+"</span>";
				str += "<span id='V_ValueColumn_"+data[i].ID+"'>"+data[i].VALUE_COLUMN+"</span>";
				str += "<span id='V_IDColumn_"+data[i].ID+"'>"+data[i].OBJ_ID_COLUMN+"</span>";
				str += "<span id='V_ObjType_"+data[i].ID+"'>"+data[i].OBJECT_TYPE+"</span>";
				str += "<span id='V_ObjID_"+data[i].ID+"'>"+data[i].OBJECT_ID+"</span>";
				str += "<span id='V_FlowPhase_"+data[i].ID+"'>"+data[i].FLOW_PHASE+"</span>";
				str += "<span id='V_AllocType_"+data[i].ID+"'>"+data[i].ALLOC_TYPE+"</span>";
				str += "<span id='V_PUID_"+data[i].ID+"'>"+data[i].PRODUCTION_UNIT_ID+"</span>";
				str += "<span id='V_AreaID_"+data[i].ID+"'>"+data[i].AREA_ID+"</span>";
				str += "<span id='V_FAcilityID_"+data[i].ID+"'>"+data[i].FACILITY_ID+"</span>";
				str += "<span id='V_DateColumn_"+data[i].ID+"'>"+data[i].DATE_COLUMN+"</span>";
				str += "</span>";
				str += "</td>";
				str += "<td>"+checkValue(data[i].STATIC_VALUE,"")+"</td>";
				str += "<td>"+checkValue(data[i].OBJECT_NAME,"")+"</td>";
				str += "<td>"+checkValue(data[i].TABLE_NAME,"")+"</td>";
				str += "<td>"+checkValue(data[i].VALUE_COLUMN,"")+"</td>";
				str += "<td><span id='V_Comment_"+data[i].ID+"'>"+checkValue(data[i].COMMENT,"")+"</span></td>";
				str += "<td style='font-size:9pt'><a href=\"javascript:_formula.deleteVar("+data[i].ID+")\">Delete</a> | <a href=\"javascript:_formula.editFormula("+data[i].ID+",true)\">Edit</a></td>";
				str += "</tr>";
			}

			$('#bodyVarsList').html(str);
		},
		deleteFormula : function(formula_id)
		{
			if(formula_id == undefined)
				formula_id = _formula.current_formula_id;
			if(!confirm("Are you sure to delete this formula?")) return;

			param = {
				'formula_id': _formula.current_formula_id
			};
			
			sendAjaxNotMessage('/deleteformula', param, function(data){
				//if(data!=="ok") alert(data);
             	_formula.loadFormulasList();
			});	
		},
		saveFormulaOrder : function(){
			var orders=[];
			$i=0;
			$("#tableFormula .formula_item").each(function(){
				if($(this).attr('order')!=$(this).attr('new_order')){
					orders.push([$(this).attr('rowid'),$(this).attr('new_order')]);
				}
			});
			if(orders.length>0){

				param = {
					'orders': orders
				};
				
				sendAjaxNotMessage('/saveformulaorder', param, function(data){
					if(data=="ok") 
						alert("Update formula sort order successfully");
					else
						alert(data);
				});	
			}
		},
		showAddFormula : function()
		{
			$('#cboUserArea').html('');
			$('#cboUserFacility').html('');
			$('#cboObjName').html('');
			_formula.editFormula(-1,false);
		},
		editFormula : function(formula_id,isVar)
		{
			if(formula_id==undefined)
				formula_id=(isVar?_formula.current_var_id:_formula.current_formula_id);
			var tt=(formula_id<=0?"New":"Edit");
			var pre=(isVar?"V_":"Q_");
			_formula.current_var_id = formula_id;
			
			$("#editBoxTitle").html(tt+(isVar?" variable":" formula"));
			$("#isvar").val(isVar?1:0);
			if(isVar)
			{
				$("#trFormula").hide();
				$("#trStaticValue").show();
				$("#trOrder").show();
				$("#trBeginDate").hide();
				$("#trEndDate").hide();
			}
			else
			{
				$("#trFormula").show();
				$("#trStaticValue").hide();
				$("#trOrder").hide();
				$("#trBeginDate").show();
				$("#trEndDate").show();
			}
			if(formula_id>0)
			{	
				_formula.curObjectID=$("#"+pre+"ObjID_"+formula_id).html();
				_formula.curPUID=$("#"+pre+"PUID_"+formula_id).html();
				_formula.curAreaID=$("#"+pre+"AreaID_"+formula_id).html();
				_formula.curFacilityID=$("#"+pre+"FacilityID_"+formula_id).html();

				$("#txtFormulaName").val($("#"+pre+"FormulaName_"+formula_id).html());
				$("#txtComment").val($("#"+pre+"Comment_"+formula_id).html());
				$("#txtStaticValue").val($("#"+pre+"StaticValue_"+formula_id).text());
				$("#txtOrder").val($("#"+pre+"Order_"+formula_id).html());
				$("#txtFormula").val($("#"+pre+"Formula_"+formula_id).text());
				$("#txtBeginDate").val($("#"+pre+"BeginDate_"+formula_id).html());
				$("#txtEndDate").val($("#"+pre+"EndDate_"+formula_id).html());
				$("#txtTableName").val($("#"+pre+"TableName_"+formula_id).html());
				$("#txtValueColumn").val($("#"+pre+"ValueColumn_"+formula_id).html());
				$("#txtIDColumn").val($("#"+pre+"IDColumn_"+formula_id).html());
				$("#cboObjType").val($("#"+pre+"ObjType_"+formula_id).html());

//				$("#cboObjName").val(xx);
				$("#cboFlowPhase").val($("#"+pre+"FlowPhase_"+formula_id).html());
				$("#cboAllocType").val($("#"+pre+"AllocType_"+formula_id).html());
				
				$("#txtDateColumn").val($("#"+pre+"DateColumn_"+formula_id).html());
				
				$("#cboUserPU").val(_formula.curPUID);
				$("#cboUserPU").change();

				setTimeout(function () {
					$("#cboUserArea").val(_formula.curAreaID);
					$("#cboUserArea").change();
				}, 3000);	
			}
			else
			{
				document.forms["frmEditFormula"].reset();
			}
			$( "#boxEditFormula" ).dialog({
				height: 430,
				width: 800,
				modal: true,
				title: tt+(isVar?" variable":" formula"),
			});
		},
		reloadCbo : function(id, data){
			$('#'+id).empty();
			
			var _data = data.result;
			var cbo = '';
			$('#'+id).html(cbo);
			//cbo += ' 		<option value="0">All</option>';
			for(var v in _data){
				cbo += ' 		<option value="' + _data[v].ID + '">' + _data[v].NAME + '</option>';
			}

			$('#'+id).html(cbo);
			$('#'+id).change();

			if(id == "cboUserFacility"){
				if(checkValue(_formula.curFacilityID, -1) != -1) return;
				var value = _formula.curFacilityID;
				var arg = value.split(",");
				alert(arg.length + "  ----  " + value);
			}
		},

		cboOnchange : function(cboSet, value, table){
			param = {
				'ID' :value,
				'TABLE' : table
			};		

			 $.ajax({
		    	url: '/am/selectedID',
		    	type: "post",
		    	dataType: 'json',
		    	data: param,
		    	success: function(_data){
		    		_formula.reloadCbo(cboSet, _data);
				}
			}); 
		},
		saveFormula : function(saveAsNew)
		{
			var isVar=$("#isvar").val();
			
			param = {
				'asnew' : (saveAsNew?'1':''),
				//'_id' : isVar?current_var_id, //(isVar?_formula.current_var_id+'&formula_id='+_formula.current_formula_id:_formula.current_formula_id+'&group_id='+$("#cboFormulaGroups").val()),
				'txtFormulaName' : $('#txtFormulaName').val(),
				'txtFormula' : $('#txtFormula').val(),
				'txtTableName' : $('#txtTableName').val(),
				'txtValueColumn' : $('#txtFormula').val(),
				'txtIDColumn' : $('#txtIDColumn').val(),
				'txtDateColumn' : $('#txtDateColumn').val(),
				'cboFlowPhase' : $('#cboFlowPhase').val(),
				'cboAllocType' : $('#cboAllocType').val(),
				'txtBeginDate' : $('#txtBeginDate').val(),
				'txtEndDate' : $('#txtEndDate').val(),
				'txtComment' : $('#txtComment').val(),
				'cboObjType' : $('#cboObjType').val(),
				'cboUserPU' : $('#cboUserPU').val(),
				'cboUserArea' : $('#cboUserArea').val(),
				'cboUserFacility' : $('#cboUserFacility').val(),
				'cboObjName' : $('#cboObjName').val(),
				'isvar' : isVar,
				'group_id' : $("#cboFormulaGroups").val(),
				'formula_id' : _formula.current_formula_id,
				'var_id' : _formula.current_var_id,
				'txtStaticValue' : $('#txtStaticValue').val(),
				'txtOrder' : $('#txtOrder').val()
			};
				
				sendAjaxNotMessage('/saveformula', param, function(data){
					if(data != "ok")
                		alert(data);
					else
					{
						alert("Save successfully");
						$("#boxEditFormula").dialog("close");
						$("#isvar").val(0);
						if(isVar)
							_formula.reloadVarsList();
						else
							_formula.loadFormulasList();
					}
				});			
		},
		testFormula : function(formula_id)
		{
			if(formula_id==undefined)
				formula_id=_formula.current_formula_id;
			
			if(formula_id>0) 
			{
				$("#test_formula_occur_date").val("");
				$("#div_edit_date").hide();
				$("#boxTest").dialog({
					width: 900,
					height: 500,
					modal: true,
					title: "Test formula"});

				$("#test_formula").html($("#Q_Formula_"+formula_id).html());
				$('#test_log').html("Processing...");

				param = {
					'fid' : formula_id,
					'occur_date' : $("#test_formula_occur_date").val()
				};		

				sendAjaxNotMessage('/testformula', param, function(data){
					if(data=="need_occur_date")
					{
						$("#test_formula_occur_date").val("");
						$("#div_edit_date").show();
						$('#test_log').html("Please select occur date");
						//alert("Please select occur date");
						$("#test_formula_occur_date").focus();
					}
					else
						$('#test_log').html(data);
				});		
			}
		},
		deleteVar : function(var_id)
		{
			if(!confirm("Are you sure to delete this variable?")) return;

			param = {
				'var_id' : var_id
			};		

			sendAjaxNotMessage('/deletevar', param, function(data){
				_formula.reloadVarsList();
			});		
		},
		saveVarsOrder:function(){
			var orders=[];
			$i=0;
			$("#tableVars .var_item").each(function(){
				if($(this).attr('order')!=$(this).attr('new_order')){
					orders.push([$(this).attr('rowid'),$(this).attr('new_order')]);
				}
			});
			//_alert(orders);
			if(orders.length>0)

				param = {
					'orders' : orders
				};		

				sendAjaxNotMessage('/savevarsorder', param, function(data){
					if(data=="ok") 
						alert("Update variables sort order successfully");
					else
						alert(data);
				});		
		},
		showAddVar : function()
		{
			$("#isvar").val(1);
			_formula.editFormula(-1,true);
		}
}
</script>

<body style="margin:0px">
	<div id="dialog" style="display:none; height:35px">
	    <div id="chart_change">
	    <table>
	    	<tr>
	        	<td>Group name:</td>
	        	<td><input type="text" size="" value="" id="d_group_name" style="width:250px"></td>
	        </tr>
	    </table>
	    </div>
	</div>
	<div id="boxTest" style="display: none; padding-top: 10px">
		<button onclick="hideTestFormula()"
			style="display: none; cursor: pointer; width: 60px; height: 28px; float: right">
			<b>Close</b>
		</button>
		<div id="div_edit_date"
			style="display: none; float: right; height: 30px;">
			<b>Occur date</b> <input style="width: 120px" type="text"
				id="test_formula_occur_date" name="test_formula_occur_date"
				size="15">
			<button onclick="_formula.testFormula(-1)" style="width: 100px;">
				<b>Run test</b>
			</button>
		</div>
		<i><span id="test_formula"
			style="font-family: Times New Roman; font-size: 12pt">Formula</span></i>
		<br> <br>
		<div id="test_log"
			style="overflow: auto; width: 850px; height: 380px; border-radius: 5px; border: 1px solid #dddddd; background: white; padding: 10px; font-family: Courier">
		</div>
	</div>
	<div id="container" style="width:1322px">
		<!-- Formula list box -->
		<div id="MySplitter">
			<div id="TopPane">
				<div
					style="display: none; height: 30px; border-bottom: 1px solid #bbbbbb; box-sizing: border-box; padding: 0px 2px">
					<span style="display: block; height: 30px; line-height: 30px;"><b>Formula
							list</b></span>
				</div>
				<div style="height: 100%; overflow-y: auto">
					<table class="tab_list_table" border="0" cellpadding="4"
						cellspacing="0" id="tableFormula"
						style="width: 100%; min-width: 800px;">
						<thead>
							<tr style="font-weight: bold;">
								<td width=30 align='center'>#</td>
								<td>Formula name</td>
								<td>Object name</td>
								<td>Table</td>
								<td>Column</td>
								<td>Formula</td>
								<td>Begin date</td>
								<td>End date</td>
								<td>Comment</td>
							</tr>
						</thead>
						<tbody id="bodyFormulasList">
							
						</tbody>
					</table>
				</div>
			</div>
			<div id="BottomPane">
				<div
					style="height: 30px; border-bottom: 1px solid #bbbbbb; box-sizing: border-box; padding: 0px 2px">
					<span
						style="display: block; float: right; height: 30px; line-height: 30px;">
						<button onclick="_formula.showAddVar()">Add Variable</button>
						<button onclick="_formula.saveVarsOrder()">Save sort order</button>
					</span> <span
						style="display: block; height: 30px; line-height: 30px;"><b>Variable
							list</b> &nbsp;(<span id="formula_name" style="font-weight: bold"></span>)</span>
				</div>
				<div id="boxVarsList"
					style="height: calc(100% - 30px); overflow-y: auto;">
					<table class="tab_list_table" border="0" cellpadding="4"
						cellspacing="0" id="tableVars"
						style="width: 100%; min-width: 800px">
						<thead>
							<tr style="font-weight: bold;">
								<td width=30 align='center'>#</td>
								<td>Name</td>
								<td>Expression</td>
								<td>Object name</td>
								<td>Table</td>
								<td>Column</td>
								<td>Comment</td>
								<td style="width: 80">&nbsp;</td>
							</tr>
						</thead>
						<tbody id="bodyVarsList">
							
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div id="boxEditFormula" class="boxEdit" style="display: none;">
			<table style="width: 100%; height: 100%" cellpadding="0"
				cellspacing="0">
				<tr style="display: none">
					<td tyle="height: 30;padding-left:10px"><b id="editBoxTitle">Edit
							formula</b></td>
				</tr>
				<tr>
					<td id="boxEditUserInfo" style="padding: 10px" valign="top">
						<table style="width: 760">
							<tr>
								<td valign="top">
									<form name="frmEditFormula" id="frmEditFormula" method="POST">
										<input type="hidden" name="isvar" id="isvar" value="0">
										<table border="0" cellpadding="0" id="table7"
											style="width: 100%">
											<tr>
												<td style="width: 150">Name</td>
												<td colspan="3"><input id="txtFormulaName"
													style="width: 542px" type="text" name="txtFormulaName"
													size="20"></td>
											</tr>
											<tr id="trStaticValue">
												<td>Expression</td>
												<td colspan="3"><input id="txtStaticValue"
													style="width: 542px" type="text" name="txtStaticValue"
													size="20"></td>
											</tr>
											<tr id="trOrder">
												<td>Order</td>
												<td colspan="3"><input id="txtOrder" style="width: 180px"
													type="text" name="txtOrder" size="20"></td>
											</tr>
											<tr id="trFormula">
												<td>Formula</td>
												<td colspan="3"><input id="txtFormula" style="width: 542px"
													type="text" name="txtFormula" size="20"></td>
											</tr>
											<tr>
												<td>Table name</td>
												<td style="width: 250"><input id="txtTableName"
													style="width: 180px" type="text" name="txtTableName"
													size="20"></td>
												<td>Object type</td>
												<td><select id="cboObjType" style="width: 180px;" size="1"
													name="cboObjType1">
														<option value="FLOW">Flow</option>
														<option value="ENERGY_UNIT">Energy Unit</option>
														<option value="TANK">Tank</option>
														<option value="STORAGE">Storage</option>
														<option value="EQUIPMENT">Equipment</option>
												</select></td>
											</tr>
											<tr>
												<td>Value column name</td>
												<td><input id="txtValueColumn" style="width: 180px"
													type="text" name="txtValueColumn" size="20"></td>
												<td>Production unit</td>
												<td>
													<select id="cboUserPU" style="width: 180px;" size="1" name="cboUserPU" onchange="_formula.cboChange('cboUserPU');">
														<option value="0"></option> 
														@foreach($loProductionUnit as $unit)
															<option value="{!!$unit->ID!!}">{!!$unit->NAME!!}</option>
														@endforeach
													</select>
												</td>
											</tr>
											<tr>
												<td>ID column name</td>
												<td><input id="txtIDColumn" style="width: 180px" type="text"
													name="txtIDColumn" size="20"></td>
												<td>Area</td>
												<td><select id="cboUserArea" style="width: 180px;" size="1" onchange="_formula.cboChange('cboUserArea');"
													name="cboUserArea"></select></td>
											</tr>
											<tr>
												<td>Date column name</td>
												<td><input type="text" id="txtDateColumn"
													name="txtDateColumn" style="width: 180px" size="20"></td>
												<td>Facility</td>
												<td><select id="cboUserFacility" style="width: 180px;" onchange="_formula.loadObjects();";
													size="1" name="cboUserFacility"></select></td>
											</tr>
											<tr height=22>
												<td>Flow phase</td>
												<td><select id="cboFlowPhase" style="width: 180px;" size="1" name="cboFlowPhase">
													<option value="0"></option> 
													@foreach($code_flow_phase as $re1)
														<option value="{!!$re1['ID']!!}">{!!$re1['NAME']!!}</option> 
													@endforeach
												</select></td>
												<td>Object name</td>
												<td rowspan=5><select multiple id="cboObjName"
													style="width: 180px; height: 150px" size="1"
													name="cboObjName[]"></select></td>
											</tr>
											<tr height=22>
												<td>Alloc type</td>
												<td><select id="cboAllocType" style="width: 180px;" size="1"
													name="cboAllocType">
													<option value="0"></option> 
													@foreach($code_alloc_type as $re2)
														<option value="{!!$re2['ID']!!}">{!!$re2['NAME']!!}</option> 
													@endforeach
													</select></td>
												<td>&nbsp;</td>
											</tr>
											<tr id="trBeginDate" height=22>
												<td>Begin date</td>
												<td><input type="text" id="txtBeginDate" name="txtBeginDate"
													style="width: 180px" size="20"></td>
												<td>&nbsp;</td>
											</tr>
											<tr id="trEndDate" height=22>
												<td>End date</td>
												<td><input type="text" id="txtEndDate" name="txtEndDate"
													style="width: 180px" size="20"></td>
												<td>&nbsp;</td>
											</tr>
											<tr>
												<td>Comment</td>
												<td><textarea id="txtComment" name="txtComment"
														style="width: 180px; height: 50px;" size="20"></textarea></td>
												<td>&nbsp;</td>
											</tr>
										</table>
									</form>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<table border="0" cellpadding="2" id="table5"
				style="position: absolute; bottom: 5px; left: 5px;">
				<tr>
					<td>
						<p>
							<input onClick="_formula.saveFormula()" type="button" value="Save"
								name="B3"> <input onClick="_formula.saveFormula(1)" type="button"
								value="Save as new" id="buttonSaveAsNew" name="B5"> <input
								onClick="_formula.cancelEdit()" type="button" value="Cancel" name="B4">
					
					</td>
				</tr>
			</table>
		</div>
	</div>
</body>
@stop