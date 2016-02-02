<?php
include_once('../lib/db.php');
$act=$_REQUEST["act"];

if($act=="savediagram"){
	$diagram_name=$_REQUEST['name'];
	$value=urldecode($_REQUEST['key']);
	$diagram_code=$_REQUEST['code'];
	$network_type=$_REQUEST['nw_type'];
	$diagram_id=$_REQUEST['id'];

	if ($diagram_id>0){
		$sSQL_network = "UPDATE WF_DIAGRAM SET `NAME` = '$diagram_name', `CODE`='$diagram_code', XML_CODE='$value' WHERE `id`=$diagram_id";
		$result_network=  mysql_query($sSQL_network) or die ("error:".mysql_error());
	}
	else
	{
		$sSQL_network = "INSERT INTO WF_DIAGRAM(`NAME`,`CODE`,XML_CODE) VALUES('$diagram_name','$diagram_code','$value')";
		$result_network=  mysql_query($sSQL_network) or die (mysql_error());
		$diagram_id=mysql_insert_id();
	}
	echo "ok$diagram_id";
}
else if($act=="getdiagram"){
	$diagram_id=$_REQUEST['id'];
	if($diagram_id=="~~GETLIST")
	{
		$sSQL="select ID,NAME from WF_DIAGRAM order by `NAME`";
		$result=mysql_query($sSQL) or die (mysql_error());
		$s="";
		while($row=mysql_fetch_array($result))
		{
			$s .= ($s==""?"":"~~").$row[NAME]."||".$row[ID];
		}
		echo "%%%$s";
		exit();
	}

	$sSQL="select XML_CODE from WF_DIAGRAM where ID='$diagram_id'";
	$result=mysql_query($sSQL) or die (mysql_error());
	$row=mysql_fetch_array($result);

	echo $row[0];	
}
else if($act=="deletediagram"){
	$diagram_id=$_REQUEST['id'];
	$sSQL="delete from WF_DIAGRAM where `id`='$diagram_id'";
	$result=mysql_query($sSQL) or die (mysql_error());
	$row=mysql_fetch_array($result);
	echo $row[0];	
}
?>