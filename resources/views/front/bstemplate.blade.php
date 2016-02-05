<!DOCTYPE html>

<?php
/* include_once('../lib/db.php');
include_once('../lib/utils.php');

$RIGHT_CODE="FDC_FLOW";
checkRight($RIGHT_CODE); */

$chkall="";//"<td bgcolor='#C6DFEA' class='CTV' width='50'><input type='checkbox' class='chkall'><abbr title='Calculated to standard value'>CTV</abbr></td>";
?>
<html lang="en">
<head>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type"
	content="text/html; charset=windows-1252">
<title>Energy Builder - Data Capture</title>
<link rel="stylesheet" href="../common/css/jquery-ui.css" />
<link rel="stylesheet" href="../common/css/style.css" />

<script src="cdn/jquery-1.10.2.min.js"></script>
<!--    
    <script src="../common/lm/colResizable-1.3.min.js"></script>
-->
<script src="../common/js/jquery-ui.js"></script>
<script src="../config/js/jquery-ui-timepicker-addon.js"></script>
<script src="../common/utils.js"></script>
<script src="../config/js/numericInput.min.js"></script>

<meta name="_token"
	content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />

</head>


    
<style>
table td {font-size:10pt}
.readonlytext {border:0px solid #fff;background:none}
input {border:1px solid #888}
</style>
<!-- Inputmask 
<script src="cdn/jquery.inputmask.js"></script>
 -->
<!-- Freeze column and row -->
<link rel="stylesheet" href="cdn/jquery.dataTables.css" />
<script type="text/javascript" charset="utf8" src="cdn/jquery.dataTables.js"></script>
<script type="text/javascript" src="cdn/dataTables.fixedColumns.js"></script>
</head>

<body style="margin:0; overeu-x:hidden">

<userbox>
		@yield('userbox')
</userbox>
	
<div id="pageheader" style="height:100px;">
&nbsp;</div>
<div style="padding-left:10px">
<div style="padding:10px 10px 10px 0px;font-size:16pt;">FLOW DATA CAPTURE</div>
<form name="form_fdc" id="form_fdc" action="saveeufdc.php" method="POST"> 
<input name="fields_fdc" value="" type="hidden">
<input name="fields_value" value="" type="hidden">
<input name="fields_theor" value="" type="hidden">
<input name="fields_alloc" value="" type="hidden">
<input name="fields_plan" value="" type="hidden">
<input name="fields_forecast" value="" type="hidden">
<table border="0" cellpadding="3" bgcolor="#E6E6E6" cellspacing="0">
	<tr>
		<td><b>Date</b></td>
		<td bgcolor="#FFFFFF">&nbsp;</td>
		<td><b>Production Unit</b></td>
		<td><b>Area</b></td>
		<td><b>Facility</b></td>
		<td bgcolor="#FFFFFF">&nbsp;</td>
		<td><b>Record Frequency</b></td>
		<td><b>Phase Type</b></td>
		<td bgcolor="#FFFFFF">&nbsp;</td>
		<td id="boxAction" style="padding:10px;<?php //if(hasRight("DATA_READONLY")) echo "display:none;"; ?>" rowspan="2"><input type="button" value="Save" name="B3" onClick="save()" style="width: 85; height: 26">
		<input type="button" value="Load data" id="buttonLoadData" name="B33" onClick="doReloadData()" style="width: 85; height: 26"></td>
	</tr>
	<tr>
<?php
 	/* $ws_info=getWorkSpaceInfo();
echo "		<td width='80'>
		<input onChange='reloadData()' readonly style='width:100%' type='text' id = 'date_begin' name='date_begin' size='15' value='".$ws_info[3]."'></td>
		<td bgcolor='#FFFFFF'>&nbsp;
		</td>
		<td width='140'>
					<select style='width:100%;' id='cboProdUnit' size='1' name='cboProdUnit'>".$ws_info[2]."</select></td>
		<td width='140'>
		<select style='width:100%;' id='cboArea' size='1' name='cboArea'>".$ws_info[1]."</select></td>
		<td width='140'>
					<select style='width:100%;' id='cboFacility' size='1' name='cboFacility'>".$ws_info[0]."</select></td>";
 */
 ?>
		<td bgcolor="#FFFFFF">&nbsp;
					</td>
		<td width="140">
					<select style="width:100%;" id="cboRecordFrequency" size="1" name="cboRecordFrequency">
					<option value="0">(All)</option>
					</select></td>
		<td width="140">
					<select style="width:100%;" id="cboPhaseType" size="1" name="cboPhaseType">
					<option value="0">(All)</option>
					</select></td>
		<td bgcolor="#FFFFFF">&nbsp;
					</td>
	</tr>
</table>
<br>
<div id="tabs">
<ul>
<li><a href="#tabs-1"><font size="2">FDC VALUE</font></a></li>
<li><a href="#tabs-2"><font size="2">STD VALUE</font></a></li>
<li><a href="#tabs-3"><font size="2">THEORETICAL</font></a></li>
<li><a href="#tabs-4"><font size="2">ALLOCATION</font></a></li>
<li><a href="#tabs-5"><font size="2">COMPOSITION ALLOC</font></a></li>
<li><a href="#tabs-6"><font size="2">PLAN</font></a></li>
<li><a href="#tabs-7"><font size="2">FORECAST</font></a></li>
</ul>

<div id="tabs-1">
	<div id="containerFDC" style="width:1280px;overflow-x:hidden">
		<table border="0" cellpadding="3" id="table_FDC" class="fixedtable nowrap display compact">
			<thead>
                <tr style="height:26"><th style='font-size:9pt;text-align:left;white-space: nowrap; background:#FFF'><div style="width:230px">Object name</div></th>
					<?php// genTableHeader("flow_data_fdc_value"); ?>	
				</tr>
			</thead>
			<tbody id="body_FDC">
			</tbody>
		</table>
	</div>
</div>

<div id="tabs-2">
    <div id="containerVALUE" style="width:1280px;overflow-x: hidden;">
            <table border="0" cellpadding="3" id="table_DAYVALUE" class="fixedtable nowrap display compact" >
                <thead>
                    <tr style="height:26"><th style='font-size:9pt;text-align:left;white-space: nowrap; background:#FFF'><div style="width:230px">Object name</div></th>
    					<?php //genTableHeader("flow_data_value"); ?>	
                    </tr>
                </thead>
                <tbody id="body_DAYVALUE">
                </tbody>
            </table>
    </div>
</div>
<div id="tabs-3">
    <div id="containerTHEOR" style="width:1280px;overflow-x: hidden;" class="fixedtable nowrap display compact">
        <table border="0" cellpadding="3" id="table_DATA_THEOR" class="fixedtable nowrap display compact" >
            <thead>
                <tr style="height:26"><th style='font-size:9pt;text-align:left;white-space: nowrap; background:#FFF'><div style="width:230px">Object name</div></th>
                     <?php //genTableHeader("flow_data_theor"); ?>	
                </tr>
            </thead>
            <tbody id="body_DAYTHEOR">
            </tbody>
        </table>
    </div>
</div>
<div id="tabs-4">
    <div id="containerALLOC" style="width:1280px;overflow-x: hidden;" class="fixedtable nowrap display compact">
        <table border="0" cellpadding="3" id="table_DATA_ALLOC" class="fixedtable nowrap display compact" >
            <thead>
                <tr style="height:26"><th style='font-size:9pt;text-align:left;white-space: nowrap; background:#FFF'><div style="width:230px">Object name</div></th>
                     <?php //genTableHeader("flow_data_alloc"); ?>	
                </tr>
            </thead>
            <tbody id="body_DAYALLOC">
            </tbody>
        </table>
    </div>
</div>

<div id="tabs-5">
<div style="width:1250px;overflow-x: scroll;">
<table border="0" cellpadding="3" id="table_data_comp" >
			<thead>
				<tr style="height:26">
					<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
// genTableHeader("flow_comp_data_alloc"); ?>	
				</tr>
			</thead>
			<tbody id="body_data_comp">
			</tbody>
		</table>
</div>
<input style="font-size:10pt;margin-top:10px" type="button" onClick="addCompRow()" value="Add record" />
</div>
<div id="tabs-6">
    <div id="containerPLAN" style="width:1280px;overflow-x: hidden;">
        <table border="0" cellpadding="3" id="table_DATA_PLAN" class="fixedtable nowrap display compact">
            <thead>
                <tr style="height:26"><th style='font-size:9pt;text-align:left;white-space: nowrap; background:#FFF'><div style="width:230px">Object name</div></th>
					<?php// genTableHeader("flow_data_plan"); ?>	
                </tr>
            </thead>
            <tbody id="body_DAYPLAN">
            </tbody>
        </table>
    </div>
</div>
<div id="tabs-7">
    <div id="containerFORECAST" style="width:1280px;overflow-x: hidden;">
        <table border="0" cellpadding="3" id="table_DATA_FORECAST" class="fixedtable nowrap display compact">
            <thead>
                <tr style="height:26"><th style='font-size:9pt;text-align:left;white-space: nowrap; background:#FFF'><div style="width:230px">Object name</div></th>
                    <?php// genTableHeader("flow_data_forecast"); ?>	
                </tr>
            </thead>
            <tbody id="body_DAYFORECAST">
            </tbody>
        </table>
    </div>
</div>
</div>
</form>
</div>


<script>
function deleteRow(a,isOldRow)
{
	if(isOldRow)
	{
		$("#oldRow"+a).remove();
	}
	else if(a>0)
		$("#newRow"+a).remove();
}
var newRowInd=0;
function addCompRow()
{
	newRowInd++;
	var sRowHTML="<tr id='newRow"+newRowInd+"'>"+$("#newRow0").html()+"</tr>";
	sRowHTML=sRowHTML.replace("deleteRow(0)","deleteRow("+newRowInd+")");
	sRowHTML=sRowHTML.replace(/_TMP0/g,"_TMP"+newRowInd);

	//alert(sRowHTML);
	$('#table_data_comp tr:last').after(sRowHTML);
	$('[name="DATA_FL_DATA_GRS_VOL_TMP'+newRowInd+'"]').focus();

	//Event not delegation in javascrip
	$("._datetimepicker").removeClass('hasDatepicker').datetimepicker();
	$("._datepicker").removeClass('hasDatepicker').datepicker();
	$("._numeric").numericInput({ allowFloat: true, allowNegative: true });
}

$(".chkall").change(function(){
	$(":checkbox").prop('checked', $(this).prop('checked'));
});

$('#cboFacility').change(function(e){reloadData();});
$('#cboRecordFrequency').change(function(e){reloadData();});
$('#cboPhaseType').change(function(e){reloadData();});
$('#cboEventType').change(function(e){reloadData();});

$('#cboProdUnit').change(function(e){
    $('#cboArea').html('');   // clear the existing options
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"LO_AREA",id:"",parent_field:"production_unit_id",parent_value:$(this).val()},
	             function(data) {
	             	$('#cboArea').html(data);
	             	$('#cboArea').change();
	             }
	          );
});
$('#cboArea').change(function(e){
    $('#cboFacility').html('');   // clear the existing options
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"FACILITY",id:"",parent_field:"area_id",parent_value:$(this).val()},
	             function(data) {
	             	$('#cboFacility').html(data);
	             	$('#cboFacility').change();
	             }
	          );
});
$("#tabs").click(function(){
	showCTV();
});
var checkAll=false;
function toggleCheckAll()
{
	checkAll=!checkAll;
	$(":checkbox").prop('checked', checkAll);
}
function showCTV()
{
	if($("#tabs").tabs("option", "active")==0)
		$(".CTV").css("display", "");
	else
		$(".CTV").css("display", "none");
};

function reloadData()
{
}
function doReloadData()
{
	if($('#date_begin').val()!="" && $("#cboFacility").val()>0)
	{
		postRequest( 
		             "loadflowfdc.php",
		             {date:$('#date_begin').val(),facility_id:$("#cboFacility").val(),record_freq:$('#cboRecordFrequency').val(),phase_type:$('#cboPhaseType').val()},
		             function(data) {
						$("#buttonLoadData").val("Refresh");
		             	var arrs=data.split("!@#$");
if(arrs.length<2)
{
	alert(data); return;
}

		             	$(".bodyObjectList").html(arrs[0]);

						$("#containerFDC").html(containerFDC_html);
		             	$("#body_FDC").html(arrs[1]);
						$("[name='fields_fdc']").val($("[name='fields_fdc_tmp']").val());

						$("#containerVALUE").html(containerVALUE_html);
		             	$("#body_DAYVALUE").html(arrs[2]);
						$("[name='fields_value']").val($("[name='fields_value_tmp']").val());

						$("#containerTHEOR").html(containerTHEOR_html);
		             	$("#body_DAYTHEOR").html(arrs[3]);
						$("[name='fields_theor']").val($("[name='fields_theor_tmp']").val());

						$("#containerALLOC").html(containerALLOC_html);
		             	$("#body_DAYALLOC").html(arrs[4]);
						$("[name='fields_alloc']").val($("[name='fields_alloc_tmp']").val());

		             	$("#body_data_comp").html(arrs[5]);

						$("#containerPLAN").html(containerPLAN_html);
		             	$("#body_DAYPLAN").html(arrs[6]);
						$("[name='fields_plan']").val($("[name='fields_plan_tmp']").val());

						$("#containerFORECAST").html(containerFORECAST_html);
		             	$("#body_DAYFORECAST").html(arrs[7]);
						$("[name='fields_forecast']").val($("[name='fields_forecast_tmp']").val());
						
						$("._datetimepicker").datetimepicker();
						$("._datepicker").datepicker();
						$("._numeric").numericInput({ allowFloat: true, allowNegative: true });
						
						var active=$("#tabs").tabs("option", "active");
						fdc=value=theor=alloc=plan=forecast=false;						
						if(active==0) freezeFDC();
						else if(active==1) freezeDAYVALUE();
						else if(active==2) freezeDAYTHEOR();
						else if(active==3) freezeDAYALLOC();
						else if(active==5) freezeDAYPLAN();
						else if(active==6) freezeDAYFORECAST();
		             }
		          );
	}
}
function save()
{
	var bre;
	$("input[need_check=1]").each(function(){
		var v_max=($(this).attr("max_value")==""? -1: Number($(this).attr("max_value")))
		var v_min=($(this).attr("min_value")==""? -1: Number($(this).attr("min_value")))
		var v_val=$(this).val();
		
		if (v_val!="")
		{
			var v_val=Number(v_val);
			if(v_max==-1 && v_min!=-1)
			{
				if(v_val < v_min)
					{alert("Input have to greater than or equal to "+v_min); $(this).focus().select(); bre=true; return false}
			}
			else if(v_max!=-1 && v_min==-1)
			{
				if(v_val > v_max)
					{alert("Input have to less than or equal to "+v_max); $(this).focus().select(); bre=true; return false}
			}
			else if(v_max !=-1 && v_min !=-1)
			{
				if(v_val < v_min || v_val > v_max)
					{alert("Input out up range ["+v_min+": "+v_max+"]"); $(this).focus().select(); bre=true; return false}
			}
		}
	});
	if(bre) return;
	
    postRequest('saveflowfdc.php?new_ind='+newRowInd, $('form#form_fdc').serialize(),
            function(data){
                	if(data!="")
                		alert(data);
					else
					{
						doReloadData();
						alert("Save successfully");
					}
                }
    );
}
var containerFDC_html; var containerVALUE_html; var containerTHEOR_html; var containerALLOC_html; var containerPLAN_html; var containerFORECAST_html;
function makeTableResizable(t)
{
	return;
		$(t).colResizable({
			liveDrag:true, 
			gripInnerHtml:"<div class='grip'></div>", 
			draggingClass:"dragging"
			});
		$(t).wrap("<div style='width:"+($(t).width()+10)+"px'></div>");
}
var func_code="<?php //echo $RIGHT_CODE; ?>";
$(function() {
	containerFDC_html=$("#containerFDC").html();
	containerVALUE_html=$("#containerVALUE").html();
	containerTHEOR_html=$("#containerTHEOR").html();
	containerALLOC_html=$("#containerALLOC").html();
	containerPLAN_html=$("#containerPLAN").html();
	containerFORECAST_html=$("#containerFORECAST").html();

	$("#pageheader").load("../home/header.php?menu=dc");
	$( "#date_begin" ).datepicker({
	    changeMonth:true,
	     changeYear:true,
	     dateFormat:"mm/dd/yy"
	});
	makeTableResizable("#table_FDC");
	makeTableResizable("#table_DATA_VALUE");
	makeTableResizable("#table_DATA_THEOR");
	makeTableResizable("#table_DATA_ALLOC");
	makeTableResizable("#table_data_comp");
	makeTableResizable("#table_DATA_PLAN");
	
	$("#tabs").tabs({active:1});
<?php
	/* if(!$ws_info)
echo "
	var d = new Date();
	d.setDate(d.getDate() - 1);
	$('#date_begin').val(''+(1+d.getMonth())+'/'+d.getDate()+'/'+d.getFullYear());
	postRequest( 
	             '../common/getcodelist.php',
	             {table:'LO_PRODUCTION_UNIT',id:'',parent_field:'',parent_value:''},
	             function(data) {
	             	$('#cboProdUnit').html(data);
	             	$('#cboProdUnit').change();
	             }
	          );
"; */
?>
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"CODE_READING_FREQUENCY",id:"",parent_field:"",parent_value:""},
	             function(data) {
	             	$('#cboRecordFrequency').html("<option value='0'>(All)</option>"+data);
	             }
	          );
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"CODE_FLOW_PHASE",id:"",parent_field:"",parent_value:""},
	             function(data) {
	             	$('#cboPhaseType').html("<option value='0'>(All)</option>"+data);
	             }
	          );
});

var fdc=value=theor=alloc=plan=forecast=false;
var tablefdc;
function freezeFDC()
{
	var w=Number($("#body_FDC").find("tr").eq(0).attr("tbl_w"));
	if(w < 1280-248) $("#containerFDC").css("width", w+248);
	
	if($.fn.dataTable.isDataTable( '#table_FDC' ))
		tablefdc.destroy();
	tablefdc = $('#table_FDC').DataTable({
		scrollY:        "275px",
		scrollX:        true,
		scrollCollapse: true,
		paging:         false,
		searching:		false,
		info:			false
	});
	new $.fn.dataTable.FixedColumns(tablefdc,{leftColumns: 1});
	fdc=true;
}
var tabledayvalue;
function freezeDAYVALUE()
{
	var w=Number($("#body_DAYVALUE").find("tr").eq(0).attr("tbl_w"));
	if(w < 1280-248) $("#containerVALUE").css("width", w+248);
	
	if($.fn.dataTable.isDataTable( '#table_DAYVALUE' ))
		tabledayvalue.destroy();
	tabledayvalue = $('#table_DAYVALUE').DataTable({
		scrollY:        "275px",
		scrollX:        true,
		scrollCollapse: true,
		paging:         false,
		searching:		false,
		info:			false
	});
	new $.fn.dataTable.FixedColumns(tabledayvalue,{leftColumns: 1});
	value=true;	
}
var tabletheor;
function freezeDAYTHEOR()
{
	var w=Number($("#body_DAYTHEOR").find("tr").eq(0).attr("tbl_w"));
	if(w < 1280-248) $("#containerTHEOR").css("width", w+248);

	if($.fn.dataTable.isDataTable( '#table_DATA_THEOR' ))
		tabletheor.destroy();
	tabletheor = $('#table_DATA_THEOR').DataTable({
		scrollY:        "275px",
		scrollX:        true,
		scrollCollapse: true,
		paging:         false,
		searching:		false,
		info:			false
	});
	new $.fn.dataTable.FixedColumns(tabletheor,{leftColumns: 1});
	theor=true;	
}
var tablealloc;
function freezeDAYALLOC()
{
	var w=Number($("#body_DAYALLOC").find("tr").eq(0).attr("tbl_w"));
	if(w < 1280-248) $("#containerALLOC").css("width", w+248);
	
	if($.fn.dataTable.isDataTable( '#table_DATA_ALLOC' ))
		tablealloc.destroy();
	tablealloc = $('#table_DATA_ALLOC').DataTable({
		scrollY:        "275px",
		scrollX:        true,
		scrollCollapse: true,
		paging:         false,
		searching:		false,
		info:			false
	});
	new $.fn.dataTable.FixedColumns(tablealloc,{leftColumns: 1});
	alloc=true;	
}
var tableplan;
function freezeDAYPLAN()
{
	var w=Number($("#body_DAYPLAN").find("tr").eq(0).attr("tbl_w"));
	if(w < 1280-248) $("#containerPLAN").css("width", w+248);
	
	if($.fn.dataTable.isDataTable( '#table_DATA_PLAN' ))
		tableplan.destroy();
	tableplan = $('#table_DATA_PLAN').DataTable({
		scrollY:        "275px",
		scrollX:        true,
		scrollCollapse: true,
		paging:         false,
		searching:		false,
		info:			false
	});
	new $.fn.dataTable.FixedColumns(tableplan,{leftColumns: 1});
	plan=true;	
}
var tableforecast;
function freezeDAYFORECAST()
{
	var w=Number($("#body_DAYFORECAST").find("tr").eq(0).attr("tbl_w"));
	if(w < 1280-248) $("#containerFORECAST").css("width", w+248);
	
	if($.fn.dataTable.isDataTable( '#table_DATA_FORECAST' ))
		tableforecast.destroy();
	tableforecast = $('#table_DATA_FORECAST').DataTable({
		scrollY:        "275px",
		scrollX:        true,
		scrollCollapse: true,
		paging:         false,
		searching:		false,
		info:			false
	});
	new $.fn.dataTable.FixedColumns(tableforecast,{leftColumns: 1});
	forecast=true;	
}

$("#tabs").on("tabsactivate", function(event, ui){	
	//if(reloaded==1)
	{
		var tab=ui.newTab.index();
		if(tab==0) {if(fdc==false) freezeFDC();}
		else if(tab==1) {if(value==false) freezeDAYVALUE();}
		else if(tab==2) {if(theor==false) freezeDAYTHEOR();}
		else if(tab==3) {if(alloc==false) freezeDAYALLOC();}
		else if(tab==5) {if(plan==false) freezeDAYPLAN();}
		else if(tab==6) {if(forecast==false) freezeDAYFORECAST();}
	}
	reloaded=false;
})
</script>
<div style="text-align:center;padding:10px;color:#666"><font face="Arial" size="1">Copyright &copy; 2016 eDataViz LLC</font></div>
</body>
</html>