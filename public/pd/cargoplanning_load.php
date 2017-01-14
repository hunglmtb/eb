<?php
error_reporting(E_ERROR);

$db_server_name="localhost";
$db_user="nhneu_tung";
$db_pass="tung#3";
$db_schema="energy_builder";

$db_survey_conn = @mysql_connect($db_server_name, $db_user, $db_pass) or die ("Could not connect");
mysql_select_db($db_schema,$db_survey_conn);

//include_once('../lib/utils.php');

$DateFrom = ($_REQUEST['dateFrom']);
$DateTo = ($_REQUEST['dateTo']);
$facility_id = $_REQUEST['cboFacility'];
$storage_id = $_REQUEST['cboStorage'];
$dateformat = $_REQUEST['dateformat'];
$balance = isset($_REQUEST['txt_balance'])?$_REQUEST['txt_balance']:0;
$cargoSize = isset($_REQUEST['cargoSize'])?$_REQUEST['cargoSize']:0;

//echo "aaa: ".$DateFrom; exit;

//if(!is_numeric($balance))
//	$balance = 0;
if(!is_numeric($cargoSize))
	$cargoSize = -1;

if(!$storage_id){
	echo "No data ++";
	exit;
}
//saveWorkSpaceInfo($DateFrom, $DateTo, $facility_id);
$date_from='2016-12-01';//toDateString($DateFrom);
$date_to='2016-12-31';//toDateString($DateTo);

$sSQL="select la.name LA_NAME,la.id LA_ID, ba.name BA_NAME, la.INTEREST_PCT 
from pd_lifting_account la, ba_address ba where la.storage_id = $storage_id and la.company = ba.id";
$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
$ent_la_id = [];
$ent_r1 = [];
$ent_r2 = [];
$interest_percents = [];
$cap = 1800;
$lifting_acc_ids = "";
while($row=mysql_fetch_assoc($result)){
	//$ent_la_id[] = $row["LA_ID"];
	$ent_r1["$row[LA_ID]"] = $row["LA_NAME"];
	$ent_r2["$row[LA_ID]"] = $row["BA_NAME"];
	$interest_percents["$row[LA_ID]"] = $row["INTEREST_PCT"];//($row["INTEREST_PCT"]/100)*$cap;
	$lifting_acc_ids .= ($lifting_acc_ids==""?"":",").$row["LA_ID"];
}

if(!$lifting_acc_ids){
	echo "No data --";
	exit;
}

$sSQL="select la.id LA_ID, ps.ID SHIPPER_ID, ps.name SHIPPER_NAME, ps.CARGO_SIZE
from pd_lifting_account la, PD_SHIPPER ps, PD_CARGO_SHIPPER cs where la.storage_id = $storage_id and cs.LIFTING_ACCOUNT_ID = la.id and cs.SHIPPER_ID=ps.ID";
$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
$shipper_id = [];
$shipper_r1 = [];
$shipper_r2 = [];
$cargo_sizes = [];
$shipper_la = [];
$shipper_total_pct = [];
while($row=mysql_fetch_assoc($result)){
	//$shipper_id[] = $row["SHIPPER_ID"];
	$shipper_r1["$row[SHIPPER_ID]"] = $row["SHIPPER_NAME"];
	$shipper_r2["$row[SHIPPER_ID]"] .= ($shipper_r2["$row[SHIPPER_ID]"]?"+":"").$ent_r2["$row[LA_ID]"];
	$shipper_total_pct["$row[SHIPPER_ID]"] += $interest_percents["$row[LA_ID]"];
	$cargoSize = $_REQUEST["cargo_size_{$row[SHIPPER_ID]}"];
	$cargo_sizes["$row[SHIPPER_ID]"] = ($cargoSize>0)?$cargoSize:$row["CARGO_SIZE"]; //+= $interest_percents["$row[LA_ID]"];
	$shipper_la["$row[SHIPPER_ID]"][] = $row["LA_ID"];
}

$sSQL="select DATE_FORMAT(a.OCCUR_DATE,'%m/%d/%Y') OCCUR_DATE,d.id LIFTING_ACCOUNT_ID, round(sum(a.FL_DATA_GRS_VOL*d.INTEREST_PCT/100),3) flow_qty
from flow_data_value a, flow b, pd_lifting_account d 
where d.id in($lifting_acc_ids) and d.PROFIT_CENTER=b.COST_INT_CTR_ID and b.id=a.FLOW_ID 
and a.OCCUR_DATE between '$date_from' and '$date_to' and exists(select 1 from storage_data_value x
where x.OCCUR_DATE = a.OCCUR_DATE and x.storage_id=$storage_id)
group by a.occur_date,d.id
";
//echo $sSQL;
$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
$la_data = [];
while($row=mysql_fetch_assoc($result)){
	$la_data["$row[OCCUR_DATE]"]["$row[LIFTING_ACCOUNT_ID]"] = $row["flow_qty"];
}
//echo "xxx".count($la_data); exit();
echo '<thead><tr>
	<td colspan="3" rowspan="2" style="background:#dddddd"><b>Open balance &gt; </b><input id="txt_balance" name="txt_balance" value="'.$balance.'" style="width:100px"></td>
	<td colspan="'.count($ent_r1).'" class="group1_th"><b>Entitlement</b></td>
	<td colspan="'.count($shipper_r1).'" class="group2_th"><b>Planned Cargo</b></td>
	<td colspan="'.count($ent_r1).'" class="group3_th"><b>Scheduled Cargo</b></td>
</tr><tr>';
foreach($ent_r1 as $id => $name){
	echo "<td id='ent_la_{$id}' class='group1_th'>$name</td>";
}
foreach($shipper_r1 as $id => $name){
	echo "<td id='shipper_{$id}' class='group2_th'>$name</td>";
}
foreach($ent_r1 as $id => $name){
	echo "<td id='sche_la_{$id}' class='group3_th'>$name</td>";
}
echo '</tr><tr>
	<td rowspan="2" style="background:#cccccc"><b>Date</b></td>
	<td rowspan="2" style="background:#cccccc"><b>Openning balance</b></td>
	<td rowspan="2" style="background:#cccccc"><b>Plan cargo</b></td>
';
foreach($ent_r2 as $id => $name){
	echo "<td id='ent_ba_{$id}' class='group1_th'>$name</td>";
}
foreach($shipper_r2 as $id => $name){
	echo "<td id='shipper_ba_{$id}' class='group2_th'>$name</td>";
}
foreach($ent_r2 as $id => $name){
	echo "<td rowspan='2' id='sche_ba_{$id}' class='group3_th'>$name</td>";
}
echo '</tr><tr>';
foreach($interest_percents as $id => $interest_rate){
	echo "<td class='group1_th'>{$interest_rate}%</td>";
}
foreach($cargo_sizes as $id => $cargo_size){
	echo "<td class='group2_th'><input name='cargo_size_{$id}' style='width:100px;text-align:center' value='$cargo_size'></td>";
}
/*
foreach($interest_percents as $id => $name){
	echo "<td class='group3_th'></td>";
}
*/
echo '</tr></thead>';
if(!$balance) exit;
echo '<tbody>';

$sSQL="select a.ID, DATE_FORMAT(a.OCCUR_DATE,'%m/%d/%Y') OCCUR_DATE, a.AVAIL_SHIPPING_VOL OPENING_BALANCE from storage_data_value a
where a.OCCUR_DATE between '$date_from' and '$date_to' and a.storage_id=$storage_id
";

//echo "<tr><td>$sSQL<td></tr>";
$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
$vals = [];
while($row=mysql_fetch_assoc($result)){
	$v = $row["OPENING_BALANCE"];
	$rowvals = [];
	$rowvals["OCCUR_DATE"] = $row["OCCUR_DATE"];
	$rowvals["BALANCE"] = $v;
	$rowvals["PLAN"] = "";
	//echo "<tr><td>$row[OCCUR_DATE]</td><td>$v</td><td></td>";
	foreach($interest_percents as $id => $val){
		if(count($la_data)==0){
			if(!isset($vals["ENT_LA_$id"]))
				$vals["ENT_LA_$id"] = $val;
			$vals["ENT_LA_$id"] += 200;
			$v = $vals["ENT_LA_$id"];
		}
		else{
			$vals["ENT_LA_$id"] += $la_data["$row[OCCUR_DATE]"]["$id"];;
			$v = $vals["ENT_LA_$id"];
		}
		$rowvals["ENT_LA_$id"] = $v;
		//echo "<td id='ent_val_{$id}_{$row[ID]}' class='group1_td'>$v</td>";
	}
	foreach($shipper_r1 as $id => $val){
		$v = 0;
		foreach($shipper_la["$id"] as $key => $la_id){
			$v += $rowvals["ENT_LA_$la_id"];
		}
		$rowvals["SHIPPER_$id"] = $v;
		//echo "<td id='shipper_val_{$id}_{$row[ID]}' class='group2_td'>$v</td>";
	}
	foreach($ent_r2 as $id => $val){
		//echo "<td id='sche_val_{$id}_{$row[ID]}' class='group3_td'></td>";
		$rowvals["SCHE_LA_$id"] = "";
	}
	$shipper_max_id = -1;
	$highlight = [];
	if($balance > 0){
		if($rowvals["BALANCE"] > $balance){
			$rowvals["PLAN"] = "Y";
			//find max shipper
			$max = -1;
			foreach($shipper_r1 as $id => $val){
				$v = 0;
				if($rowvals["SHIPPER_$id"] > $max){
					$max = $rowvals["SHIPPER_$id"];
					$shipper_max_id = $id;
				}
			}
			if($shipper_max_id > 0){
				$highlight[] = "SHIPPER_$shipper_max_id";
				foreach($shipper_la["$shipper_max_id"] as $key => $la_id){
					//$dx = $cargo_sizes["$shipper_max_id"]/count($shipper_la["$shipper_max_id"]);
					$dx = round($cargo_sizes["$shipper_max_id"]*$interest_percents["$la_id"]/$shipper_total_pct["$shipper_max_id"],2);
					$rowvals["SCHE_LA_$la_id"] = $dx;
					$highlight[] = "ENT_LA_$la_id";
				}
			}
		}
	}
	echo "<tr>";
	foreach($rowvals as $key => $value){
		if(substr($key,0,4) == "ENT_")
			$class = 'group1_td';
		else if(substr($key,0,4) == "SHIP")
			$class = 'group2_td';
		else if(substr($key,0,4) == "SCHE")
			$class = 'group3_td';
		else
			$class = "";
		if($key == "PLAN")
			$class .= ($class==""?"":" ")."td_plan";
		if (in_array($key, $highlight))
			$class .= ($class==""?"":" ")."td_highlight";
			
		echo "<td class='$class'".($rowvals["PLAN"]=="Y"?" style='border:2px solid #378de5'":"").">$value</td>";
	}
	echo "</tr>";
	if($shipper_max_id > 0){
		foreach($shipper_la["$shipper_max_id"] as $key => $la_id){
			//$dx = $cargo_sizes["$shipper_max_id"]/count($shipper_la["$shipper_max_id"]);
			$dx = round($cargo_sizes["$shipper_max_id"]*$interest_percents["$la_id"]/$shipper_total_pct["$shipper_max_id"],2);
			$rowvals["ENT_LA_$la_id"] -= $dx;
			$vals["ENT_LA_$la_id"] -= $dx;
		}
	}
}
echo "<tbody>";
exit;
?>

