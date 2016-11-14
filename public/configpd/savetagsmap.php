<?php
include_once('../lib/db.php');
include_once('../lib/utils.php');

$facility_id=$_REQUEST[cboFacility];
$object_type=$_REQUEST[cboObjectType];

$ids=explode(";",$_REQUEST[ids]);
$fields_list=explode(",",$_REQUEST[fields_list]);

$new_ind=$_REQUEST[new_ind];
$insert_values="";
for ($i = 0; $i <= $new_ind; $i++) 
{
	$hasData=false;
	if($_REQUEST["TAG_ID"."_TMP$i"]!=="" && $_REQUEST["COLUMN_NAME"."_TMP$i"]!=="")
	{
		$hasData=true;
	}

	if($hasData)
	{
		$sF="";
		$sV="";
		foreach($fields_list as $fcomp) 
		{
			if($fcomp=='BEGIN_DATE' || $fcomp=='END_DATE')
				$_REQUEST["$fcomp"."_TMP$i"]=toDateString($_REQUEST["$fcomp"."_TMP$i"]);
			$sF.=($sF==""?"":",")."`$fcomp`";
			$sV.=($sV==""?"":",")."'".$_REQUEST["$fcomp"."_TMP$i"]."'";
		}
		$insert_values.=($insert_values==""?"":",")."($object_type,$sV)";
	}
}
if($insert_values)
{
	$sSQL="insert into INT_TAG_MAPPING(OBJECT_TYPE, $_REQUEST[fields_list]) values $insert_values";
	$sSQL=str_replace("''","null",$sSQL);
	$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
}

$ids_delete="";
foreach($ids as $xID) 
if($xID)
{
	$c=$_REQUEST["TAG_ID$xID"];
	if($c)
	{
		$sSet="";
		foreach($fields_list as $fcomp) 
		{
			if($fcomp=='BEGIN_DATE' || $fcomp=='END_DATE')
				$_REQUEST["$fcomp$xID"]=toDateString($_REQUEST["$fcomp$xID"]);
			$sSet.=($sSet==""?"":",")."`$fcomp`='".$_REQUEST["$fcomp$xID"]."'";
		}
		if($sSet)
		{
			$sSQL="UPDATE INT_TAG_MAPPING SET $sSet where id='$xID';";
			$sSQL=str_replace("''","null",$sSQL);
			$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
		}
	}
	else
		$ids_delete.=($ids_delete==""?"":",").$xID;
}
if($ids_delete)
{
	$sSQL="delete from INT_TAG_MAPPING where id in ($ids_delete);";
	$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
}

echo "";
?>