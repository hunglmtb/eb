<?php
require_once('../lib/db.php');
require_once('../lib/utils.php');

$act=$_REQUEST[act];
if($act=='getTableFields')
{
	$table=$_REQUEST[table];
	$field=$_REQUEST[field];
	echo getTableFields($table,"decimal,int,double",$field,false);
	exit();
}
else if($act=='getTableFieldsAll')
{
	$table=$_REQUEST[table];
	$field=$_REQUEST[field];
	echo getTableFields($table,"",$field,false);
	exit();
}
else if($act=='deleterows')
{
	$table=$_REQUEST["table"];
	$id=$_REQUEST["id"];
	$sql="delete from $table where id in ($id)";
	$result=mysql_query($sql) or die (mysql_error());
	exit();
}
?>