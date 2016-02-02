<?php
include_once('../lib/db.php');
include_once('../lib/utils.php');
$key=$_REQUEST["key"];
if(!isset($key)) $key="WF_TASK_ID";
	$v = getOneValue("select `NUMBER_VALUE`+1 from `params` where `KEY`='$key'");
	$s="update params set NUMBER_VALUE=NUMBER_VALUE+1 where `KEY`='$key'";
	mysql_query($s) or die("fail: ".$s."-> error:".mysql_error());
	echo $v;
?>