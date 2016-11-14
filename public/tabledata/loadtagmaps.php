<?php
include_once('../lib/db.php');
include_once('../lib/utils.php');

function getTableWidth($table)
{
	$s="select SUM(width) from
(SELECT SUM(FDC_WIDTH+18) width
	FROM cfg_field_props
	WHERE table_name='".$table."'
		AND FDC_WIDTH >0
		AND USE_FDC=1
UNION 
SELECT COUNT(ID)*118 width
	FROM cfg_field_props
	WHERE table_name='".$table."'
		AND (FDC_WIDTH =0 OR FDC_WIDTH is NULL)
		AND USE_FDC=1
) x";
	$re=mysql_query($s) or die("Error: ".mysql_error());
	$ro=mysql_fetch_row($re);
	return $ro[0];
}
function getField($table)
{
	$s="SELECT COLUMN_NAME, DATA_METHOD FROM cfg_field_props WHERE TABLE_NAME='".$table."' AND USE_FDC='1' ORDER BY FIELD_ORDER";
	$ref=mysql_query($s) or die("fail: ".$s."-> error:".mysql_error());
	while($row=mysql_fetch_array($ref))
		$fields.=($fields?",".$row[COLUMN_NAME]:$row[COLUMN_NAME]);
	return $fields;
}

$facility_id=$_REQUEST[facility_id];
$object_type=$_REQUEST[object_type];
$object_id=$_REQUEST[object_id];

$xtable=getOneValue("select CODE from INT_OBJECT_TYPE where id=$object_type");

$c_system=loadCodes('INT_SYSTEM');
$c_freq=loadCodes('CODE_READING_FREQUENCY');
$c_override=loadCodes('CODE_BOOLEAN');
$c_table=loadComboFromSQL("select TABLE_NAME ID,TABLE_NAME `NAME` from INT_MAP_TABLE where OBJECT_TYPE=$object_type");
$first_table=getOneValue("select TABLE_NAME from INT_MAP_TABLE where OBJECT_TYPE=$object_type limit 1");
if($first_table)
	$x_column=getTableFields($first_table,"decimal","",false);
$c_object=loadComboFromSQL("select ID,`NAME` from $xtable where FACILITY_ID=$facility_id order by `NAME`");
$c_eventtype=loadCodes('CODE_EVENT_TYPE');
$c_flowphase=loadCodes('CODE_FLOW_PHASE');

$x_system=$c_system;
$x_freq=$c_freq;
$x_override=$c_override;
$x_table=$c_table;
$x_object=$c_object;
$x_eventtype=$c_eventtype;
$x_flowphase=$c_flowphase;

$fields=getField("INT_TAG_MAPPING");
$sSQL="select a.*
	from INT_TAG_MAPPING a,$xtable b 
	where a.OBJECT_ID=b.ID and b.FACILITY_ID=$facility_id and a.OBJECT_TYPE=$object_type".($object_id>0?" and a.OBJECT_ID=$object_id":"");
$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
$i=0;
$ids="";
$table_width=getTableWidth("INT_TAG_MAPPING");

$s="SELECT * FROM cfg_field_props WHERE TABLE_NAME='INT_TAG_MAPPING' ORDER BY FIELD_ORDER";
$re=mysql_query($s) or die("fail: ".$s."- error:".mysql_error());	
while($row=mysql_fetch_array($result))
{
	$i++;
	$ids.="$row[ID];";
	if($i % 2==0) $bgcolor="#eeeeee"; else $bgcolor="#f8f8f8";

	$x_system=str_replace("value='$row[SYSTEM_ID]'", "value='$row[SYSTEM_ID]' selected", $c_system);
	$x_freq=str_replace("value='$row[FREQUENCY]'", "value='$row[FREQUENCY]' selected", $c_freq);
	$x_override=str_replace("value='$row[ALLOW_OVERRIDE]'", "value='$row[ALLOW_OVERRIDE]' selected", $c_override);
	$x_table=str_replace("value='$row[TABLE_NAME]'", "value='$row[TABLE_NAME]' selected", $c_table);
	$x_object=str_replace("value='$row[OBJECT_ID]'", "value='$row[OBJECT_ID]' selected", $c_object);

	//$x_column=loadCodes("INT_TABLE_COLUMN",false,"TABLE_NAME",$row[TABLE_NAME],$row[COLUMN_NAME],"","COLUMN_NAME","COLUMN_NAME");
	$x_column=getTableFields($row[TABLE_NAME],"decimal",$row[COLUMN_NAME],false);

	if(!$x_column) $x_column="<option value='$row[COLUMN_NAME]'>$row[COLUMN_NAME]</option>";

	$x_eventtype=str_replace("value='$row[EVENT_TYPE]'", "value='$row[EVENT_TYPE]' selected", $c_eventtype);
	$x_flowphase=str_replace("value='$row[FLOW_PHASE]'", "value='$row[FLOW_PHASE]' selected", $c_flowphase);

	$tablename=$row[TABLE_NAME];

	echo "<tr id='oldRow$row[ID]' bgcolor='$bgcolor' height='26'>";
	mysql_data_seek($re, 0);
	while($ro=mysql_fetch_array($re))
	{
		$f=$ro[COLUMN_NAME];
		if($f=='SYSTEM_ID')
			echo "<td><select size='1' style='width:100%' name='SYSTEM_ID".$row[ID]."'>".$x_system."</select></td>";
		else if($f=='FREQUENCY')
			echo "<td><select size='1' style='width:100%' name='FREQUENCY".$row[ID]."'>".$x_freq."</select></td>";
		else if($f=='ALLOW_OVERRIDE')
			echo "<td><select size='1' style='width:100%' name='ALLOW_OVERRIDE".$row[ID]."'>".$x_override."</select></td>";
		else if($f=='TABLE_NAME')
			echo "<td><select size='1' onchange=\"tableChanged('$row[ID]')\" style='width:100%' id='TABLE_NAME".$row[ID]."' name='TABLE_NAME".$row[ID]."'>".$x_table."</select></td>";
		else if($f=='COLUMN_NAME')
			echo "<td><select size='1' style='width:100%' id='COLUMN_NAME".$row[ID]."' name='COLUMN_NAME".$row[ID]."'>".$x_column."</select></td>";
		else if($f=='OBJECT_ID')
			echo "<td><select size='1' style='width:100%' name='OBJECT_ID".$row[ID]."'>".$x_object."</select></td>";
		else if($f=='EVENT_TYPE')
			echo "<td><select size='1' style='width:100%' name='EVENT_TYPE".$row[ID]."'>".$x_eventtype."</select></td>";
		else if($f=='FLOW_PHASE')
			echo "<td><select size='1' style='width:100%' name='FLOW_PHASE".$row[ID]."'>".$x_flowphase."</select></td>";
		else
		{
			$val=$row[$ro[COLUMN_NAME]];
			if($f=='BEGIN_DATE' or $f=='END_DATE')
			{
				$date=date_create($val);
				$val=($val?date_format($date, "m/d/Y"):NULL);
			}
			//echo "<td><input style='width:100%' ".getInputBoxAttr($ro[DATA_METHOD],$ro[INPUT_TYPE],$val)." ".getInputControl($ro, $val)." name='$ro[COLUMN_NAME]$row[ID]' size='15'></td>\n";		
			echo "<td><input idvalue='$row[ID]' style='width:100%' ".getInputControl($ro,$val,"class_$f")." name='$ro[COLUMN_NAME]$row[ID]' size='15'></td>\n";
		}
	}
	echo "<td><a href='javascript:deleteRow($row[ID],true)'>Delete</a></td>";
	echo "</tr>";
}

//TEMP ROW

	$row[ID]=0;
	echo "<tr id='newRow$row[ID]' bgcolor='$bgcolor' height='26'>";
	mysql_data_seek($re, 0);
	while($ro=mysql_fetch_array($re))
	{
		$f=$ro[COLUMN_NAME];
		if($f=='SYSTEM_ID')
			echo "<td><select size='1' style='width:100%' name='SYSTEM_ID_TMP".$row[ID]."'>".$x_system."</select></td>";
		else if($f=='FREQUENCY')
			echo "<td><select size='1' style='width:100%' name='FREQUENCY_TMP".$row[ID]."'>".$x_freq."</select></td>";
		else if($f=='ALLOW_OVERRIDE')
			echo "<td><select size='1' style='width:100%' name='ALLOW_OVERRIDE_TMP".$row[ID]."'>".$x_override."</select></td>";
		else if($f=='TABLE_NAME')
			echo "<td><select size='1' onchange=\"tableChanged('_TMP0')\" style='width:100%' id='TABLE_NAME_TMP".$row[ID]."' name='TABLE_NAME_TMP".$row[ID]."'>".$x_table."</select></td>";
		else if($f=='COLUMN_NAME')
			echo "<td><select size='1' style='width:100%' id='COLUMN_NAME_TMP".$row[ID]."' name='COLUMN_NAME_TMP".$row[ID]."'>".$x_column."</select></td>";
		else if($f=='OBJECT_ID')
			echo "<td><select size='1' style='width:100%' name='OBJECT_ID_TMP".$row[ID]."'>".$x_object."</select></td>";
		else if($f=='EVENT_TYPE')
			echo "<td><select size='1' style='width:100%' name='EVENT_TYPE_TMP".$row[ID]."'>".$x_eventtype."</select></td>";
		else if($f=='FLOW_PHASE')
			echo "<td><select size='1' style='width:100%' name='FLOW_PHASE_TMP".$row[ID]."'>".$x_flowphase."</select></td>";
		else
		{
			echo "<td><input idvalue='_TMP0' style='width:100%' ".getInputControl($ro,"","class_$f")." name='$ro[COLUMN_NAME]_TMP$row[ID]' size='15'></td>\n";
		}
	}
	echo '<td><a style="display:none" href="javascript:deleteRow(0)">Delete</a></td>';
	echo "</tr>";

echo "<input type='hidden' name='fields_list' value='$fields'>";
echo "<input type='hidden' name='ids' value='$ids'>";

?>