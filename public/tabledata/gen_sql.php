<?php
include_once('../lib/db.php');
include_once('../lib/utils.php');
$table=$_REQUEST["table"];
$id=$_REQUEST["id"];
$type=$_REQUEST["type"];

	$sql="select * from $table where id in ($id)";
	$result=mysql_query($sql) or die (mysql_error());
	while($row=mysql_fetch_assoc($result)){
		$f="";
		$v="";
		$s="";
		foreach ($row as $key => $value) {
			$f.=($f?",":"")."`$key`";
			$v.=($v?",":"")."'".addslashes($value)."'";
			$s.=($s?",":"")."`$key`='".addslashes($value)."'";
		}
if($type==1)
		$sss="insert into $table($f) values($v);\n";
else if($type==2)
		$sss="update $table set $s where id=$row[ID];\n";
else
		$sss="insert into $table($f) values($v)~@^@~update $table set $s where id=$row[ID];";

		$sss=str_replace("''","null", $sss);
		echo $sss;
	}
?>