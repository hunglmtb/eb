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

$DateFrom = ($_REQUEST['dateFrom']);
$DateTo = ($_REQUEST['dateTo']);
$facility_id = $_REQUEST['cboFacility'];
$storage_id = $_REQUEST['cboStorage'];
$dateformat = $_REQUEST['dateformat'];
$balance = isset($_REQUEST['txt_balance'])?$_REQUEST['txt_balance']:"";
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
$dateformat = str_replace("yy","Y",$dateformat);
$df = explode("/",$dateformat);
if(count($df >= 3)){
	$mysql_dateformat = "%$df[0]/%$df[1]/%$df[2]";
	//$mysql_dateformat = str_replace("yy","Y",$mysql_dateformat);
	//echo "<tr><td colspan='6' style='color:orange;text-align:center'>Wrong date format $dateformat</td></tr></tbody>";
	//exit;
}
else{
	echo "<tr><td colspan='6' style='color:orange;text-align:center'>Wrong date format</td></tr></tbody>";
	exit;
}

//saveWorkSpaceInfo($DateFrom, $DateTo, $facility_id);
$date_from=$DateFrom;//toDateString($DateFrom);
$date_to=$DateTo;//toDateString($DateTo);

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

$sSQL="select DATE_FORMAT(a.OCCUR_DATE,'$mysql_dateformat') OCCUR_DATE,d.id LIFTING_ACCOUNT_ID, round(sum(a.FL_DATA_GRS_VOL*d.INTEREST_PCT/100),3) flow_qty
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
echo '<thead><tr height="40">
	<td colspan="3" rowspan="1" style="background:#dddddd"><b>Open balance &gt; </b><input id="txt_balance" name="txt_balance" value="'.$balance.'" style="width:100px" onkeypress="return txt_balance_keypress(event)"></td>
	<td colspan="'.count($ent_r1).'" class="group1_th"><b>Entitlement</b></td>
	<td colspan="'.count($shipper_r1).'" class="group2_th"><b>Planned Cargo</b></td>
	<td colspan="'.count($ent_r1).'" class="group3_th"><b>Scheduled Cargo</b></td>
</tr><tr><td style="background:#dddddd"></td><td colspan="2" class="td_gen_cargo" onclick="genCargoEntry()">Generate All Cargo Entry</td>';
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
	<td rowspan="2" style="background:#dddddd"><b>Date</b></td>
	<td rowspan="2" style="background:#dddddd"><b>Openning balance</b></td>
	<td rowspan="2" style="background:#dddddd"><b>Plan cargo</b></td>
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
//if(count($la_data)==0) {echo "<tr><td colspan='6' style='color:orange;text-align:center'>No entitlement data</td></tr></tbody>"; exit;}

$vals = [];
$last_value = null;
$last_date = null;
$last_minus = 0;

$d1 = date ("Y-m-d", strtotime($date_from));
$d2 = $date_to;
/*
while (strtotime($d1) <= strtotime($d2)) {
	$sSQL = "select DATE_FORMAT(ifnull(a.OCCUR_DATE,'$d1'),'$dateformat') OCCUR_DATE, max(a.AVAIL_SHIPPING_VOL) OPENING_BALANCE from storage_data_value a where a.OCCUR_DATE ='$d1' and a.storage_id=$storage_id";
	$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
	$row = mysql_fetch_assoc($result);

	//$row["OCCUR_DATE"]=$d1;
	$d1 = date ("Y-m-d", strtotime("+1 day", strtotime($d1)));
*/
$sSQL="select a.OCCUR_DATE SQL_OCCUR_DATE, DATE_FORMAT(a.OCCUR_DATE,'$mysql_dateformat') OCCUR_DATE, max(a.AVAIL_SHIPPING_VOL) OPENING_BALANCE from storage_data_value a
where a.OCCUR_DATE between '$date_from' and '$date_to' and a.storage_id=$storage_id group by a.OCCUR_DATE order by a.OCCUR_DATE 
";	
$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
$rowx=mysql_fetch_assoc($result);
$log="";
while(strtotime($d1) <= strtotime($d2)){
	if($rowx["SQL_OCCUR_DATE"] > $d1 || !$rowx){
		$row["OCCUR_DATE"] = date ($dateformat, strtotime($d1));
		$row["OPENING_BALANCE"] = "";
	}
	else if($rowx["SQL_OCCUR_DATE"] == $d1){
		$row["OCCUR_DATE"] = $rowx["OCCUR_DATE"];
		$row["OPENING_BALANCE"] = $rowx["OPENING_BALANCE"];
		$rowx=mysql_fetch_assoc($result);
	}
	
	$v = $row["OPENING_BALANCE"];
	$rowvals = [];
	$rowvals["OCCUR_DATE"] = $row["OCCUR_DATE"];
	$rowvals["BALANCE"] = ($v?$v:$last_value);
	$rowvals["PLAN"] = "";
	
	//Calculate by adding Monthly balance
	$first_day_month = date ("Y-m-01", strtotime($d1));
	$is_begin_month = ($d1 == $first_day_month);
	//echo "<tr><td>$row[OCCUR_DATE]</td><td>$v</td><td></td>";
	$has_month_bal = false;
	$has_month_adj = false;
	foreach($interest_percents as $id => $val){
		$add_val = 0;
		if(count($la_data)==0){
			if(!isset($vals["ENT_LA_$id"]))
				$vals["ENT_LA_$id"] = $val;
			$vals["ENT_LA_$id"] += 200;
			$v = $vals["ENT_LA_$id"];
			$add_val = 200;
		}
		else{
			$vals["ENT_LA_$id"] += $la_data["$row[OCCUR_DATE]"]["$id"];
			$v = $vals["ENT_LA_$id"];
			$add_val = $la_data["$last_date"]["$id"];
		}
		$month_bal["ENT_LA_$id"] = "";
		$month_adj["ENT_LA_$id"] = "";
		if($is_begin_month){
			if(!isset($monthly_balance["$id"][$first_day_month])){
				$_sql = "select ifnull(BAL_VOL,0)+ifnull(ADJUST_VOL,0) VAL from PD_LIFTING_ACCOUNT_MTH_DATA where LIFTING_ACCOUNT_ID = $id and ADJUST_CODE=2 and DATE_FORMAT(BALANCE_MONTH, '%Y-%m-01') = '$first_day_month'";
				$_re=mysql_query($_sql) or die("fail: ".$_sql."-> error:".mysql_error());
				$r_bal=mysql_fetch_assoc($_re);
				$_sql = "select ifnull(BAL_VOL,0)+ifnull(ADJUST_VOL,0) VAL from PD_LIFTING_ACCOUNT_MTH_DATA where LIFTING_ACCOUNT_ID = $id and ADJUST_CODE=3 and DATE_FORMAT(BALANCE_MONTH, '%Y-%m-01') = '$first_day_month'";
				$_re=mysql_query($_sql) or die("fail: ".$_sql."-> error:".mysql_error());
				$r_adj=mysql_fetch_assoc($_re);
				$month_bal["ENT_LA_$id"] = $r_bal["VAL"];
				$month_adj["ENT_LA_$id"] = $r_adj["VAL"];
				if($month_bal["ENT_LA_$id"])
					$has_month_bal = true;
				if($month_adj["ENT_LA_$id"])
					$has_month_adj = true;
				$monthly_balance["$id"][$first_day_month] = $r_bal["VAL"]+$r_adj["VAL"];
			}
		}
		$rowvals["ENT_LA_$id"] = $v + $monthly_balance["$id"][$first_day_month];
		if(!$row["OPENING_BALANCE"])
			$rowvals["BALANCE"] += $add_val;
		//echo "<td id='ent_val_{$id}_{$row[ID]}' class='group1_td'>$v</td>";
	}
	if(!$row["OPENING_BALANCE"])
		$rowvals["BALANCE"] -= $last_minus;
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
	$last_minus = 0;
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
			$max = -1;
			if($shipper_max_id > 0){
				$highlight[] = "SHIPPER_$shipper_max_id";
				foreach($shipper_la["$shipper_max_id"] as $key => $la_id){
					//$dx = $cargo_sizes["$shipper_max_id"]/count($shipper_la["$shipper_max_id"]);
					$dx = round($cargo_sizes["$shipper_max_id"]*$interest_percents["$la_id"]/$shipper_total_pct["$shipper_max_id"],2);
					$rowvals["SCHE_LA_$la_id"] = $dx;
					$highlight[] = "ENT_LA_$la_id";
					if($dx > $max){
						$max = $dx;
						$rowvals["GEN_CARGO"] = "{\"la_id\":\"$la_id\",\"storage_id\":\"$storage_id\",\"req_date\":\"$d1\",\"qty\":\"$dx\"}";
					}
				}
				$last_minus = $cargo_sizes["$shipper_max_id"];
			}
		}
	}
	if($has_month_bal){
		echo "<tr><td colspan='3' class='td_monnth_bal'>Monthly Balance</td>";
		foreach($rowvals as $key => $value){
			$html = "";
			if(substr($key,0,4) == "ENT_"){
				$class = 'td_monnth_bal';
				$html = $month_bal[$key];
			}
			else if(substr($key,0,4) == "SHIP")
				$class = 'group2_td';
			else if(substr($key,0,4) == "SCHE")
				$class = 'group3_td';
			else
				$class = "";
			if($class)
				echo "<td class='$class'><b>$html</b></td>";
		}
		echo "</tr>";
	}
	if($has_month_adj){
		echo "<tr><td colspan='3' class='td_monnth_bal'>Monthly Adjust</td>";
		foreach($rowvals as $key => $value){
			$html = "";
			if(substr($key,0,4) == "ENT_"){
				$class = 'td_monnth_bal';
				$html = $month_adj[$key];
			}
			else if(substr($key,0,4) == "SHIP")
				$class = 'group2_td';
			else if(substr($key,0,4) == "SCHE")
				$class = 'group3_td';
			else
				$class = "";
			if($class)
				echo "<td class='$class'><b>$html</b></td>";
		}
		echo "</tr>";
	}
	echo "<tr>";
	foreach($rowvals as $key => $value){
		if($key=="GEN_CARGO") continue;
		$rowspan = "";
		if(substr($key,0,4) == "ENT_"){
			$class = 'group1_td';
		}
		else if(substr($key,0,4) == "SHIP")
			$class = 'group2_td';
		else if(substr($key,0,4) == "SCHE")
			$class = 'group3_td';
		else
			$class = "";
		if($rowvals["PLAN"]=="Y" && $rowvals["GEN_CARGO"]){
			$class .= ($class==""?"":" ")."td_has_plan";
			if($key != "BALANCE" && $key != "PLAN")
				$rowspan = 2;
		}
		if($key=="BALANCE" && !$row["OPENING_BALANCE"])
			$class .= ($class==""?"":" ")."td_cal_balance";
		if($key == "PLAN")
			$class .= ($class==""?"":" ")."td_plan";
		if (in_array($key, $highlight))
			$class .= ($class==""?"":" ")."td_highlight";
			
		echo "<td class='$class'".($rowspan>1?" rowspan='$rowspan'":"").">$value</td>";
	}
	echo "</tr>";
	if($rowvals["GEN_CARGO"]){
		echo "<tr><td class='td_gen_cargo' gen_cargo='$rowvals[GEN_CARGO]' colspan='2' onclick=\"genCargoEntry(this)\">Create Cargo Entry</td></tr>";
	}
	if($shipper_max_id > 0){
		foreach($shipper_la["$shipper_max_id"] as $key => $la_id){
			//$dx = $cargo_sizes["$shipper_max_id"]/count($shipper_la["$shipper_max_id"]);
			$dx = round($cargo_sizes["$shipper_max_id"]*$interest_percents["$la_id"]/$shipper_total_pct["$shipper_max_id"],2);
			$rowvals["ENT_LA_$la_id"] -= $dx;
			$vals["ENT_LA_$la_id"] -= $dx;
		}
	}
	$last_value = $rowvals["BALANCE"];
	$last_date = $row["OCCUR_DATE"];
	$d1 = date ("Y-m-d", strtotime("+1 day", strtotime($d1)));
}
//echo "<tr><td colspan='6' style='color:orange;text-align:center'>$log</td></tr>";
echo "</tbody>";
?>
