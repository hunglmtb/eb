<?php
include_once('../lib/db.php');
include_once('../lib/utils.php');

$act=$_REQUEST['act'];
if($act=="opentask"){
	$taskcode=$_REQUEST['taskcode'];
	$url=getOneValue("select PATH from eb_functions where CODE='$taskcode'");
	$ROOT="/eb";
	if($url) header("location:$ROOT/$url");
}
else if($act=="get_help"){
	$func_code=$_REQUEST['func_code'];
	echo getOneValue("select HELP from eb_functions where CODE='$func_code'");
}
else if($act=="get_tasklog"){
	$task_id=$_REQUEST['task_id'];
	echo getOneValue("select `log` from tm_workflow_task where id=$task_id");
}
else if($act=="save_help"){
	$func_code=$_REQUEST['func_code'];
	$help=mysql_real_escape_string($_REQUEST['help']);
	$sSQL="update eb_functions set HELP='$help' where CODE='$func_code'";
	mysql_query($sSQL) or die (mysql_error());
}
?>