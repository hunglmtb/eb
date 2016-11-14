<?php
include_once('../lib/db.php');
include_once('../lib/utils.php');

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
<div id="pageheader" style="height:100px;">
	&nbsp;</div>
<div style="padding-top:10px"><font size="5" color="#609CB9" face="Arial">&nbsp; 
	&nbsp;Tag Mapping Configuration</font></div>

<!-- Run allocation box  -->
<div style="display:none; padding:20px;position: fixed; width: 1100px; height: 450px; z-index: 100; left: 120px; top: 130px; border:2px solid #999; background:#ffffff" id="boxRunAlloc">
	<input onClick="hideAllocResult()" style="width:100px; margin:10 10 10 0px" type="button" value="Hide" name="B7"><br>
	<b><span style="font-size: 13pt">Allocation log:</span></b><br>
	<div id="allocLog" style="width:1048px;height:340px;overflow:auto">....</div>
</div>


<!-- Allocation date box -->

<!-- Content  -->
<table border="0" cellpadding="5" cellspacing="0" id="table4">
	<tr>
		<td width="6" valign="top">&nbsp;</td>
		<td width="1100" valign="top">
<form name="form_fdc" id="form_fdc" method="POST"> 
		<table border="0" cellpadding="3" bgcolor="#E6E6E6" cellspacing="0">
	<tr>
		<td><b>Production Unit</b></td>
		<td><b>Area</b></td>
		<td><b>Facility</b></td>
		<td><strong>Object Type</strong></td>
		<td bgcolor="#FFFFFF">&nbsp;</td>
		<td style="padding:10px" rowspan="2"><input type="button" value="Save" name="B3" onClick="save()" style="width: 85; height: 26">
		<input type="button" value="Refresh" name="B33" onClick="loadData()" style="width: 85; height: 26"></td>
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
		<td bgcolor="#FFFFFF">&nbsp;
					</td>
	</tr>
</table>

<!-- Job list box -->  
		<div id="boxTagMapList" style="width:1200px;overflow-x:auto;background:#f8f8f8;padding:10px;border-bottom:1px solid #ccc">
		<table border="0" cellpadding="2" id="tableList">
			<thead>
                <tr style="height:26">
					<?php genTableHeader("INT_TAG_MAPPING"); ?>	
				</tr>
			</thead>
			<tbody id="bodyTagMapList">
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
				<td>&nbsp; <a href="javascript:AddUser()">Delete</a>&nbsp; </td>
			</tr>
			</tbody>
        </table>
<!-- Add user box -->
		</div>
				<input onclick="addRow()" type="button" value="Add New" style="margin-top:10px;width:100px;">
</form>
	</td>
	</tr>
</table>

<script>
function tableChanged(id)
{
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"int_table_column",value_field:"COLUMN_NAME",text_field:"COLUMN_NAME",current_value:$('#COLUMN_NAME'+id).val(),parent_field:"TABLE_NAME",parent_value:$('#TABLE_NAME'+id).val()},
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
	$('[name="P_NAME_TMP'+newRowInd+'"]').focus();
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
$('#cboFacility').change(function(e){
	loadData();
});
$('#cboObjectType').change(function(e){
	loadData();
});
function loadData()
{
    $('#bodyTagMapList').html('');   // clear the existing options
	postRequest( 
	             "loadtagmaps.php",
	             {
	             	"object_type":$("#cboObjectType").val(),
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
</div>
<div style="text-align:center;padding:5px;color:#666"><font face="Arial" size="1">
	Copyright © 2016 eDataViz LLCC</font></div>
</body>
</html>