<?php
include_once('../lib/db.php');
include_once('../lib/utils.php');

$objtype=$_REQUEST['objtype'];
if($objtype && $objtype!="")
{
	$tableName=$objtype;
	$currentID=$_REQUEST['current_id'];
	$facility_id=$_REQUEST['facility_id'];
	if($currentID)
	{
		$sSQL="select a.FACILITY_ID,b.AREA_ID,c.PRODUCTION_UNIT_ID from `$tableName` a, FACILITY b, LO_AREA c where a.ID=$currentID and a.FACILITY_ID=b.ID and b.AREA_ID=c.ID";
		$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
		if($row=mysql_fetch_array($result))
		{
			if($row[FACILITY_ID]!=$facility_id)
			{
				$facility_id=$row[FACILITY_ID];
				$ff ="!@#$".loadCodes("FACILITY",false,"AREA_ID",$row[AREA_ID],$facility_id);
				$ff.="!@#$".loadCodes("LO_AREA",false,"PRODUCTION_UNIT_ID",$row[PRODUCTION_UNIT_ID],$row[AREA_ID]);
				$ff.="!@#$".loadCodes("lo_production_unit",false,"","",$row[PRODUCTION_UNIT_ID]);
			}
		}
	}
	$sSQL="select id,name from $tableName where facility_id='$facility_id'";

	$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
	while($row=mysql_fetch_array($result))
	{
		$s.="<option value='$row[id]'".($row[id]==$currentID?"selected":"").">$row[name]</option>\r\n";
	}
	echo $s.$ff;

	exit();
}

$tableName=strtoupper($_REQUEST['table']);
$parentField=$_REQUEST['parent_field'];
$parentValue=$_REQUEST['parent_value'];
$current_value=$_REQUEST['current_value'];
$firstblank=$_REQUEST['first_blank'];
$sWhere=$_REQUEST['where'];
$sOrderBy=$_REQUEST['orderby'];
$ValueField=$_REQUEST['value_field'];
$TextField=$_REQUEST['text_field'];

if($tableName=="FACILITY" && $DATA_SCOPE_FACILITY)
{
	$sWhere =($sWhere?"($sWhere) and ":"")."ID=$DATA_SCOPE_FACILITY";
}
if($tableName=="LO_AREA" && $DATA_SCOPE_AREA)
{
	$sWhere =($sWhere?"($sWhere) and ":"")."ID=$DATA_SCOPE_AREA";
}
if($tableName=="LO_PRODUCTION_UNIT" && $DATA_SCOPE_PU)
{
	$sWhere =($sWhere?"($sWhere) and ":"")."ID=$DATA_SCOPE_PU";
}
echo loadCodes($tableName,$firstblank,$parentField,$parentValue,$current_value,$sWhere,$ValueField?$ValueField:"ID",$TextField?$TextField:"NAME",$sOrderBy);
?>