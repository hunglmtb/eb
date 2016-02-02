<?php
include_once('../lib/db.php');
require_once('includes/gfconfig.php');
require_once('includes/gfinnit.php');
require_once('libs/cls.mysql.php');
$username=$current_username;
$wf_id = $_REQUEST["wf_id"];
//echo "xxxx $wf_id yyy";
?>
<html>
<head>
<title>Workflow Display</title>
<link rel="stylesheet" href="../common/css/style.css">
<script src="../common/js/jquery-1.9.1.js"></script>
<script src="../common/utils.js"></script>
<script src="../common/js/base64.js"></script>
<script src="js/wfshow.js?8"></script>
<?php 
$sql="SELECT * FROM tm_workflow WHERE isrun='YES' AND id in(SELECT wf_id FROM tm_workflow_task WHERE isrun in (2,3) AND (user LIKE '%,$username,%' OR user LIKE '$username,%'))";
$mysql_task=new CLS_MYSQL;
$objmysql=new CLS_MYSQL;
$objmysql->Query($sql);
$wfs="";
$c=0;
while($r=$objmysql->Fetch_Assoc()){
	$data=$r['data'];
	if(!($wf_id>0)) $wf_id=$r['id'];
	$selected=($r['id']==$wf_id?"selected":"");
	$wfs.="<option value='{$r['id']}' $selected>{$r['name']}</option>";
	$sql="SELECT id FROM tm_workflow_task WHERE isrun=2 AND (user LIKE '%,$username,%' OR user LIKE '$username,%')";
	$mysql_task->Query($sql);
	$c++;
}
if($c===0) $wf_id=0;
if($wf_id>0){
	$sql="select data from tm_workflow where id='$wf_id'";
	$objmysql->Query($sql);
	$row=$objmysql->Fetch_Assoc();
	$xml=$row['data'];
	$sql="SELECT id,task_code,isrun,runby,start_time,finish_time,case when `log` is null or `log`='' then 0 else 1 end has_log FROM tm_workflow_task WHERE wf_id=$wf_id";
	$objmysql->Query($sql);
	while($r=$objmysql->Fetch_Assoc()){
		$xml=str_replace('task_id="'.$r['id'].'"','task_id="'.$r['id'].'" has_log="'.$r['has_log'].'" start_time="'.$r['start_time'].'" finish_time="'.$r['finish_time'].'" task_code="'.$r['task_code'].'" isrun="'.$r['isrun'].'" autorun="'.($r['runby']==1?1:0).'"', $xml);
	}
	echo '<span id="wf_xml" style="display:none">'."\r\n".base64_encode($xml)."\r\n</span>\r\n";
	echo '<script type="text/javascript" src="js/mxClient.js"></script>
<script type="text/javascript" src="js/mxApplication.js"></script>';
}
?>
</head>
<body onload="<?php 
	if($wf_id>0) 
		echo "new mxApplication('config/diagrameditor.xml?3');"; 
	else 
		echo "parent.hide_wf_loading()"; 
?>" style="margin:0px;background:white;overflow:hidden">
<div id='wflist' style='margin-top:5px;width:100%;height:30px;border-top:0px solid #aaaaaa;padding-top:0px;background:white'>
<b> Workflow </b> <select id='cbo_wflist' style='min-width:200px;height:30px;margin:0 2px;border-color:#378de5'><?php echo $wfs;?></select>
<input type='button' value='Refresh' style="margin:0 2px;height:30px;width:80px" onclick="refreshPage()">
<button type='text' id='cmd_open_task' onclick="openTask()" style='display:none;margin:0 2px;width:100px;height:30px' >Open Task</button>
<button type='text' id='cmd_finish_task' style='display:none;margin:0 2px;width:120px;height:30px' >Finish Task</button>
</div>
<center>
<div id="graph" style="margin-top:15px;position:relative;height:calc(100% - 60px);width:100%;box-sizing: border-box;cursor:default;overflow:hidden;border:0px solid #a0a0a0;">
<?php if(!($wf_id>0)) echo '<p class="center_content">You have no running task</p>'; ?>
</div>
</center>
<div id='task_process' style='display:none;text-align:center;padding-top:0px'>
<span id='task_info' style="display:none">Task Name:<span class='name' task_id='0'></span></span>
</div>
<div style="display:none; padding:10px;" id="toolbar" ></div>
<script>
$(document).ready(function(){
	$('#cbo_wflist').change(function(){
		var wfid=$(this).val();
		loadSavedDiagram(wfid);		
	});
	$('#cmd_finish_task').click(function(){
		task_id=$('#task_process .name').attr('task_id');
		if(task_id>0){
			$.get('../taskman/ajaxs/finish_workflowtask.php?id='+task_id,function(data){
				//alert("Action completed ("+data+")");
				loadWorkflow();
			})
		}
	})
});
</script>
</body>
</html>
