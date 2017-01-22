<?php
function toDateString($d)
{
	if(!$d) return $d;
	$v1=explode(" ",$d);
	$v2=explode("/",$v1[0]);
	if($v2[2].$v2[1]==="" && $v2[0]!=="")
		return $v2[0]; //time only
	else
		return "$v2[2]-$v2[0]-$v2[1]".($v1[1]?" $v1[1]":"");
}

error_reporting(E_ERROR);

$db_server_name="localhost";
$db_user="nhneu_tung";
$db_pass="tung#3";
$db_schema="energy_builder";

$db_survey_conn = @mysql_connect($db_server_name, $db_user, $db_pass) or die ("Could not connect");
mysql_select_db($db_schema,$db_survey_conn);

//include_once('../lib/utils.php');
$data = $_REQUEST['cargo_data'];
$cargo_data = (array) json_decode($data);
$count = 0;
foreach($cargo_data as $cargo){
	//echo $cargo->la_id."<br>";
	$sql = "select ID from PD_CARGO where LIFTING_ACCT={$cargo->la_id} and STORAGE_ID={$cargo->storage_id} and REQUEST_DATE='{$cargo->req_date}' limit 1";
	$result=mysql_query($sql) or die("fail: ".$sql."-> error:".mysql_error());
	$row=mysql_fetch_assoc($result);
	if($row["ID"] > 0){
		$sql = "update PD_CARGO set REQUEST_QTY = {$cargo->qty} where ID=$row[ID]";
	}
	else{
		$sql = "insert into PD_CARGO(LIFTING_ACCT,STORAGE_ID,REQUEST_DATE,REQUEST_QTY,PRIORITY) values ({$cargo->la_id},{$cargo->storage_id},'{$cargo->req_date}',$cargo->qty,1)";
	}
	mysql_query($sql) or die("fail: ".$sql."-> error:".mysql_error());
	//echo "$sql<br>";
	$count++;
}
echo "$count Cargo Entry generated successfully";
?>
