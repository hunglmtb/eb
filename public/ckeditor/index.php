<?php
include_once('../lib/db.php');
include_once('../lib/utils.php');
$sql="select * from eb_functions where USE_FOR like '%TASK_GROUP%' order by CODE";
$result=mysql_query($sql) or die (mysql_error());
$sel="<option value='ROOT'>HOME SCREEN</option>\r\n";
while($row=mysql_fetch_array($result)){
	$sel.="<option value='$row[CODE]'>$row[NAME]</option>\r\n";
}
$sql="select * from eb_functions where USE_FOR like '%TASK_FUNC%' order by CODE";
$result=mysql_query($sql) or die (mysql_error());
$arr=array();
$arr["ROOT"]="<option value='ROOT'>Home screen</option>";
while($row=mysql_fetch_array($result)){
	$arr["$row[PARENT_CODE]"].="<option value='$row[CODE]'>$row[NAME]</option>";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Energy Builder - Help Editor</title>
    <link rel="stylesheet" href="../common/css/style.css"/>
	<link rel="stylesheet" href="../common/css/jquery-ui.css" />
    <script src="../common/js/jquery-1.9.1.js"></script>
	<script src="../common/js/jquery-ui.js"></script>
	<script src="../common/utils.js"></script>
        <script src="ckeditor.js"></script>
    </head>
    <body style="margin:0px">
<div id="pageheader" style="height:100px;">
	&nbsp;</div>
<div style="margin:10px;">
<p class="function_title">HELP EDITOR</p>	
<select id="cboFunctionGroup"><option>Select a group</option><?php echo $sel; ?></select>
<select id="cboFunction"></select>
	<button onclick="save()" style="width:100px;margin:5px;">Save</button>
        <form>
            <textarea name="editor1" id="editor1">
            </textarea>
<script>
var arr_funcs=[];
<?php
foreach ($arr as $key => $value) {
	echo "arr_funcs['$key']=\"$value\";\r\n";
}
?>
function save(){
	if($("#cboFunction").val()+""!=""){
		postRequest( 
			 "../common/act.php?act=save_help",
			 {func_code:$("#cboFunction").val(),help:CKEDITOR.instances.editor1.getData()},
			 function(data) {
				 if(data=="")
					 alert("Data saved successfully");
				 else
					 alert(data);
			 }
		  );
	}
}
function setData(data){
	CKEDITOR.instances.editor1.setData(data);
}

$("#cboFunctionGroup").change(function(e){
	if($(this).val()=="ROOT"){
		$('#cboFunction').html(arr_funcs[$(this).val()]);
	}
	else	
		$('#cboFunction').html('<option>Select a function</option>'+arr_funcs[$(this).val()]);
	$('#cboFunction').change();
});
$("#cboFunction").change(function(e){
	if($(this).val()+""!=""){
		postRequest( 
			 "../common/act.php?act=get_help",
			 {func_code:$(this).val()},
			 function(data) {
				CKEDITOR.instances.editor1.setData(data);
			 }
		  );
		
	}
});
$(function() {
	$("#pageheader").load("../home/header.php?menu=user");
	CKEDITOR.config.height=$(window).height()-280;
	CKEDITOR.replace( 'editor1' );
});

</script>
        </form>
</div>
    </body>
</html>