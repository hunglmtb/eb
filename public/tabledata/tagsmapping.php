<?php
require_once('../lib/db.php');
require_once('../lib/utils.php');

$RIGHT_CODE="CONFIG_TAGS_MAPPING";
checkRight($RIGHT_CODE);
?>
<html>

<head>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Energy Builder</title>
    <script src="../common/lm/jquery.js"></script>
	<script src="../common/utils.js"></script>
	<script src="../common/js/jquery-ui.js"></script>
	<link rel="stylesheet" href="../common/css/jquery-ui.css" />
    <link rel="stylesheet" href="../common/css/style.css"/>
<style type="text/css">
html,body {height:100%;}
#boxEditUser{
	display:;
	position:fixed;background:#f8f8f8;padding:0px;border:2px solid #666;
	width:800px;
	height:400px;
	left:50%;
	top:50%;
	margin-left:-400px;
	margin-top:-200px;
}
</style>
</head>
<body style="margin:0px">
<div id="pageheader" style="height:100px;">&nbsp;</div>
<div id="container" style="padding:0px 10px;">
	<div style="padding:10px 0px"><font size="5" color="#609CB9">TAG MAPPING CONFIG</font></div>
	<form name="form_fdc" id="form_fdc" method="POST"> 
	<table border="0" cellpadding="3" bgcolor="#E6E6E6" cellspacing="0">
	<tr>
		<td><b>Production Unit</b></td>
		<td><b>Area</b></td>
		<td><b>Facility</b></td>
		<td><strong>Object Type</strong></td>
		<td><strong>Object Name</strong></td>
		<td bgcolor="#FFFFFF">&nbsp;</td>
		<td style="padding:10px" rowspan="2">
			<input type="button" value="Save" name="B3" onClick="save()" style="width: 85; height: 26">
			<input type="button" value="Load Tags" name="B33" onClick="loadData()" style="width: 85; height: 26">
			<input onclick="addRow()" type="button" value="Add Tag" style="margin-left:30px;width:100px;">
		</td>
	</tr>
	<tr>
		<td width="140">
					<select style="width:100%;" id="cboProdUnit" size="1" name="cboProdUnit"></select></td>
		<td width="140">
		<select style="width:100%;" id="cboArea" size="1" name="cboArea"></select></td>
		<td width="140">
					<select style="width:100%;" id="cboFacility" size="1" name="cboFacility"></select></td>
		<td width="140">
				<select id="cboObjectType" style="width:120px;" size="1" name="cboObjectType"></select></td>
		<td width="140">
				<select id="cboObjectName" style="width:120px;" size="1" name="cboObjectName"></select></td>
		<td bgcolor="#FFFFFF">&nbsp;
					</td>
	</tr>
</table>

		<div id="boxTagMapList" style="box-sizing: border-box;margin-top:10px;min-width:100px;width:100%;height:calc(100% - 250px);overflow:auto;background:#f8f8f8;padding:10px;border:1px solid #ccc">
		<table border="0" cellpadding="2" id="tableList">
			<thead>
                <tr style="height:26">
					<?php genTableHeader("INT_TAG_MAPPING"); ?>	
				</tr>
			</thead>
			<tbody id="bodyTagMapList">
			</tbody>
        </table>
		</div>
</form>
</div>
<script>
function tableChanged(id)
{
	postRequest( 
	             "act.php?act=getTableFields",
	             {table:$('#TABLE_NAME'+id).val()},
	             function(data) {
	             	$('#COLUMN_NAME'+id).html(data);
	             }
	          );
}
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
function addRow()
{
	newRowInd++;
	var sRowHTML="<tr id='newRow"+newRowInd+"'>"+$("#newRow0").html()+"</tr>";
	sRowHTML=sRowHTML.replace("deleteRow(0)","deleteRow("+newRowInd+")");
	sRowHTML=sRowHTML.replace(/_TMP0/g,"_TMP"+newRowInd);
	sRowHTML=sRowHTML.replace("display:none","");

	//alert(sRowHTML);
	$('#tableList tr:last').before(sRowHTML);
	var objDiv = document.getElementById("boxTagMapList");
	objDiv.scrollTop = objDiv.scrollHeight;
	$('[name="TAG_ID_TMP'+newRowInd+'"]').focus();
}

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
var load_objs_first_time=true;
$('#cboArea').change(function(e){
    $('#cboFacility').html('');   // clear the existing options
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"FACILITY",id:"",parent_field:"area_id",parent_value:$(this).val()},
	             function(data) {
	             	$('#cboFacility').html(data);
					if(load_objs_first_time){
						load_objs_first_time=false;
						loadObjects("ENERGY_UNIT");
					}
	             	//$('#cboFacility').change();
	             }
	          );
});
$('#cboFacility').change(function(e){
	loadObjects();
});
function loadObjects(objtype){
	var objectType=$("#cboObjectType").val();
	if(objtype) objectType=objtype;
	else{
		if(objectType==1) objectType='FLOW';
		else if(objectType==2) objectType='ENERGY_UNIT';
		else if(objectType==3) objectType='TANK';
		else if(objectType==4) objectType='STORAGE';
		else if(objectType==5) objectType='EQUIPMENT';
	}
	$("#cboObjectName").html("");
	postRequest(
	             "../graph/act.php?act=loadVizObjects",
	             {object_type:objectType,facility_id:$("#cboFacility").val()},
	             function(data)
	             {
	             	$("#cboObjectName").html("<option>(All)</option>"+data);
	             }
	);
}
$('#cboObjectType').change(function(e){
	loadObjects();
});
function loadData()
{
    $('#bodyTagMapList').html('');   // clear the existing options
	postRequest( 
	             "loadtagmaps.php",
	             {
	             	"object_type":$("#cboObjectType").val(),
	             	"object_id":$("#cboObjectName").val(),
	             	"facility_id":$("#cboFacility").val()
	             },
	             function(data) {
	             	$('#bodyTagMapList').html(data);
					$("._datepicker").datepicker();
					$(".class_COLUMN_NAME").dblclick(function(){
						var id=$(this).attr("idvalue");
						var table=$("#TABLE_NAME"+id).val();
						
					});
	             }
	          );
}
function save()
{
    postRequest('savetagsmap.php?new_ind='+newRowInd, $('form#form_fdc').serialize(),
            function(data){
                	if(data!="")
                		alert(data);
					else
					{
						alert("Save successfully");
						loadData();
					}
                }
    );
}
var func_code="<?php echo $RIGHT_CODE; ?>";
$(function() {

	$("#pageheader").load("../home/header.php?menu=config");
	/*
	var d = new Date();
	$("#txtExpireDate").val("12/31/"+d.getFullYear());
	$( "#txtExpireDate" ).datepicker({
	    changeMonth:true,
	     changeYear:true,
	     dateFormat:"mm/dd/yy"
	});
	*/
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"INT_OBJECT_TYPE"},
	             function(data) {
	             	$('#cboObjectType').html(data);
					$('#cboObjectType').change();
					postRequest( 
					             "../common/getcodelist.php",
					             {table:"LO_PRODUCTION_UNIT",id:"",parent_field:"",parent_value:""},
					             function(data) {
					             	$('#cboProdUnit').html(data);
					             	$('#cboProdUnit').change();
					             }
					          );
	             }
	          );
});
</script>
<div style="text-align:center;padding:2px;color:#666"><font size="1">
	Copyright © 2014 eDataViz</font></div>
</body>
</html>