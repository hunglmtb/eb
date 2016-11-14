<?php
include_once('../lib/db.php');
$RIGHT_CODE="CONFIG_TABLE_DATA";
checkRight($RIGHT_CODE);
$funcnames=array(
	"cargoentry" => "Cargo Entry",
	"cargonomination" => "Cargo Nomination",
	"cargoschedule" => "Cargo Schedule",
	"cargovoyage" => "Cargo Voyage",
	"cargoload" => "Cargo Load",
	"cargounload" => "Cargo Unload",
	"voyagemarine" => "Voyage Marine",
	"voyageground" => "Voyage Ground",
	"voyagepipeline" => "Voyage Pipeline",
	"storagedisplay" => "Storage Display",
	"configpd" => "Product Delivery Table Config"
);
?>
<html>

<head>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Energy Builder - Configuration</title>
<link href="/common/css/style.css" rel="stylesheet">

</head>

<body style="margin:0; overflow-x:hidden">
<link rel="stylesheet" href="/css/css3menu0/style.css" type="text/css" /><style type="text/css">._css3m{display:none}</style>
<header role="banner">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table3">
	<tbody><tr>
		<td style="border-bottom: 1px solid #000000;background-color: #666666;" height="100">
			<div id="divMenu" style="position: absolute; z-index: 1; left: 150px; top: 43px; width: 196px; height: 40px">
	<ul id="css3menu0" class="topmenu">

		<li class="topmenu"><a href="/" style="height: 21px; line-height: 21px;">HOME</a></li>

		<li class="topmenu"><a href="#" style="height: 21px; line-height: 21px;"><span>FUNCTIONS</span></a>
			<ul>

		<li class="subfirst"><a href="#"><span>Production Management</span></a>

		<ul>

			<li><a href="/dc/flow">Flow Stream</a></li>
			<li><a href="/dc/eu">Energy Unit </a></li>
			<li><a href="/dc/storage">Tank &amp; Storage </a>

			</li>

			<li><a href="/dc/eutest">Test (Lab Data)</a>

			</li>

			<li><a href="/dc/deferment">Deferment</a>

			</li>

			<li><a href="/dc/quality">Quality </a></li>
		</ul></li>

		<li><a href="#"><span>Field Operations</span></a>

		<ul>

			<li><a href="/dc2/safety">Safety</a>

			</li>

			<li><a href="/dc2/comment">Comments</a>

			</li>

			<li><a href="/dc2/equipment">Equipment</a>

			</li>
			<li><a href="/dc2/chemical">Chemical</a>

			</li>
			<li><a href="/dc2/personnel">Personnel</a>

			</li>

		</ul></li>

		<li><a href="#">Allocation</a>

		<ul>

			<li><a href="ce/?act=allocset">Production Allocation</a>

			</li>

		</ul></li>
		<li style="display:none"><a href="#"><span>Allocation</span></a>

		<ul>

			<li class="subfirst"><a href="#"><span>Allocation Network Diagram</span></a>

			<ul>

				<li class="subfirst"><a href="#">Allocation Network Surveillance</a></li>

				<li class="sublast"><a href="#">Allocation Network Configuration</a></li>

			</ul></li>

			<li><a href="#"><span>Perform Allocation</span></a>

			<ul>

				<li class="subfirst"><a href="#">Volume/Mass/Energy/Power</a></li>

				<li><a href="#">Network (pipeline)</a></li>

				<li class="sublast"><a href="#">Compositional</a></li>

			</ul></li>

			<li><a href="#">Perform Ownership Allocation</a></li>

			<li><a href="#">Perform Tax Allocation</a></li>

			<li><a href="#">Perform Sale Allocation</a></li>

			<li><a href="#">Perform Forecast/Planning Allocation</a></li>

			<li class="sublast"><a href="#">Perform Allocation (chemical, fuel, gas, electricity)</a></li>

		</ul></li>

		<li><a href="#"><span>Forecast &amp; Planning</span></a>

		<ul>

			<li class="subfirst"><a href="#"><span>Forecast Scenarios</span></a>

			<ul>

				<li class="subfirst"><a href="#">Well</a></li>

				<li><a href="#">Facility</a></li>

				<li><a href="#">Pipeline</a></li>

				<li><a href="#">Tank/Storage</a></li>

				<li><a href="#">Export</a></li>

				<li><a href="#">Import</a></li>

				<li><a href="#">System</a></li>

				<li class="sublast"><a href="#">Forecast Configuration</a></li>

			</ul></li>

			<li class="sublast"><a href="#"><span>Planning</span></a>

			<ul>

				<li class="subfirst"><a href="#">Production</a></li>

				<li><a href="#">P&amp;B</a></li>

				<li><a href="#">Sales</a></li>

				<li><a href="#">Graphical Model </a></li>

				<li class="sublast"><a href="#">Planning Configuration</a></li>

			</ul></li>

		</ul></li>

		<li><a href="#"><span>Data Vizualization</span></a>

		<ul>

			<li class="subfirst"><a href="../diagram">Network Model</a></li>

			<li><a href="graph">Data Plotting</a></li>

			<li><a href="view">Data View</a></li>

			<li class="sublast"><a href="../report">Report</a></li>
		</ul>
		</li>
		<li class="sublast"><a href="#"><span>Product Delivery</span></a>

		<ul>

			<li class="subfirst"><a href="cargoentry.php">Cargo Entry</a></li>
			<li><a href="cargonomination.php">Cargo Nomination</a></li>
			<li><a href="cargoschedule.php">Cargo Schedule</a></li>
			<li><a href="cargovoyage.php">Cargo Voyage</a></li>
			<li><a href="cargoload.php">Cargo Load</a></li>
			<li><a href="cargounload.php">Cargo Unload</a></li>
			<li><a href="voyagemarine.php">Voyage Marine</a></li>
			<li><a href="voyageground.php">Voyage Ground</a></li>
			<li><a href="voyagepipeline.php">Voyage Pipeline</a></li>
			<li><a href="storagedisplay.php">Storage Display</a></li>
			<li class="sublast"><a href="configpd.php">Config</a></li>
		</ul>
		</li>
	</ul></li>

	</ul>
</div>			<div style="position: absolute; width: 100px; height: 84px; z-index: 0; left: 20px; top: 15px" id="layer3">
				<img border="0" src="/img/eb2.png?1" height="70">
			</div>
			<p>&nbsp;
		</p></td>
	</tr>
	<tr>
		<td height="10" style="background-image: url('../img/g.png'); background-repeat: no-repeat; background-position: center top"></td>
	</tr>
</tbody></table>
<script src="/common/js/jquery-1.9.1.js"></script> 
<script src="/common/js/jquery-ui.js"></script>
<script type="text/javascript" src="/common/js/utils.js"></script>
<div style="position: absolute; height: 32px; z-index: 1; left:350px; top:43px" id="submenu">
	<ul id="css3menu0" class="topmenu">
	<li class="topmenu"><a href="../fieldsconfig" style="height:21px;line-height:21px;">FIELDS CONFIG</a></li>
	<li class="topmenu"><a href="../tabledata/index.php" style="height:21px;line-height:21px;">TABLES DATA</a></li>
	<li class="topmenu current_menu"><a href="#" style="height:21px;line-height:21px;">PD TABLES</a></li>
	<li class="topmenu"><a href="../tagsMapping" style="height:21px;line-height:21px;">TAGS MAPPING</a></li>
	<li class="topmenu"><a href="../formula" style="height:21px;line-height:21px;">FORMULA EDITOR</a></li>
	<li class="topmenu"><a href="../viewconfig" style="height:21px;line-height:21px;">VIEW CONFIG</a></li>
<!--
	<li class="topmenu"><a href="../users/roles.php" style="height:21px;line-height:21px;">
	ROLES</a></li>
-->
	</ul>
</div>
			</header>
<?php
//include('/tabledata/userbox.php');
?>
<script>
function scopeChange(c)
{
	var s="";
	if(c) s=c; else s=$("#cboObjectScope").val();
	if(s=="CODE")
	{
		s=
"<option value='PD_CODE_BERTH_CODE'>PD_CODE_BERTH_CODE</option>"+
"<option value='PD_CODE_CARGO_PRIORITY'>PD_CODE_CARGO_PRIORITY</option>"+
"<option value='PD_CODE_CARGO_QTY_TYPE'>PD_CODE_CARGO_QTY_TYPE</option>"+
"<option value='PD_CODE_CARGO_STATUS'>PD_CODE_CARGO_STATUS</option>"+
"<option value='PD_CODE_CARGO_TYPE'>PD_CODE_CARGO_TYPE</option>"+
"<option value='PD_CODE_INCOTERM'>PD_CODE_INCOTERM</option>"+
"<option value='PD_CODE_LAYTIME_LAYCAN'>PD_CODE_LAYTIME_LAYCAN</option>"+
"<option value='PD_CODE_LOAD_ACTIVITY'>PD_CODE_LOAD_ACTIVITY</option>"+
"<option value='PD_CODE_PARCEL_QTY_TYPE'>PD_CODE_PARCEL_QTY_TYPE</option>"+
"<option value='PD_CODE_PIPELINE_MATERIAL'>PD_CODE_PIPELINE_MATERIAL</option>"+
"<option value='PD_CODE_PIPELINE_TYPE'>PD_CODE_PIPELINE_TYPE</option>"+
"<option value='PD_CODE_PORT_LOCATION'>PD_CODE_PORT_LOCATION</option>"+
"<option value='PD_CODE_PORT_TYPE'>PD_CODE_PORT_TYPE</option>"+
"<option value='PD_CODE_QTY_ADJ'>PD_CODE_QTY_ADJ</option>"+
"<option value='PD_CODE_TANKER_CLASS'>PD_CODE_TANKER_CLASS</option>"+
"<option value='PD_CODE_TANK_MEASURE_METHOD'>PD_CODE_TANK_MEASURE_METHOD</option>"+
"<option value='PD_CODE_TERMINAL_TYPE'>PD_CODE_TERMINAL_TYPE</option>"+
"<option value='PD_CODE_TIME_ADJ'>PD_CODE_TIME_ADJ</option>"+
"<option value='PD_CODE_INSPECT_TYPE'>PD_CODE_INSPECT_TYPE</option>"+
"<option value='PD_CODE_TRANSIT_TYPE'>PD_CODE_TRANSIT_TYPE</option>"+
"<option value='PD_CODE_UNLOAD_ACTIVITY'>PD_CODE_UNLOAD_ACTIVITY</option>"+
"<option value='PD_CODE_MEAS_UOM'>PD_CODE_MEAS_UOM</option>"+
"<option value='PD_CODE_MEAS_ITEM'>PD_CODE_MEAS_ITEM</option>"+
"<option value='PD_CODE_LIFT_ACCT_ADJ'>PD_CODE_LIFT_ACCT_ADJ</option>"+
"<option value='PD_CODE_DEMURRAGE_EBO'>PD_CODE_DEMURRAGE_EBO</option>"+
				'';
	}
	else if($("#cboObjectScope").val()=="CONTRACT")
	{
		s=
"<option value='PD_CODE_CONTRACT_TYPE'>PD_CODE_CONTRACT_TYPE</option>"+
"<option value='PD_CODE_CONTRACT_TYPE'>PD_CODE_CONTRACT_ATTRIBUTE</option>"+
"<option value='PD_CODE_CONTRACT_PERIOD'>PD_CODE_CONTRACT_PERIOD</option>"+
"<option value='PD_CODE_CONTRACT_PARTY_TYPE'>PD_CODE_CONTRACT_PARTY_TYPE</option>"+
"<option value='PD_CONTRACT'>PD_CONTRACT</option>"+
"<option value='PD_CONTRACT_DATA'>PD_CONTRACT_DATA</option>"+
"<option value='PD_CONTRACT_EXPENDITURE'>PD_CONTRACT_EXPENDITURE</option>"+
"<option value='PD_CONTRACT_FORMULA'>PD_CONTRACT_FORMULA</option>"+
"<option value='PD_CONTRACT_PARTIES'>PD_CONTRACT_PARTIES</option>"+
"<option value='PD_CONTRACT_QTY_FORMULA'>PD_CONTRACT_QTY_FORMULA</option>"+
"<option value='PD_CONTRACT_TEMPLATE'>PD_CONTRACT_TEMPLATE</option>"+
"<option value='PD_CONTRACT_TEMPLATE_ATTRIBUTE'>PD_CONTRACT_TEMPLATE_ATTRIBUTE</option>"+
		"";
	}	else if($("#cboObjectScope").val()=="DOCUMENT")
	{
		s=
"<option value='PD_REPORT_LIST'>PD_REPORT_LIST</option>"+
"<option value='PD_CODE_ORGINALITY'>PD_CODE_ORGINALITY</option>"+
"<option value='PD_CODE_NUMBER'>PD_CODE_NUMBER</option>"+
"<option value='PD_DOCUMENT_SET'>PD_DOCUMENT_SET</option>"+
"<option value='PD_DOCUMENT_SET_LIST'>PD_DOCUMENT_SET_LIST</option>"+
"<option value='PD_DOCUMENT_SET_CONTACT_DATA'>PD_DOCUMENT_SET_CONTACT_DATA</option>"+
"<option value='PD_DOCUMENT_SET_DATA'>PD_DOCUMENT_SET_DATA</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="OBJECT")
	{
		s=
"<option value='PD_CARGO'>PD_CARGO</option>"+
"<option value='PD_CARGO_LOAD'>PD_CARGO_LOAD</option>"+
"<option value='PD_CARGO_UNLOAD'>PD_CARGO_UNLOAD</option>"+
"<option value='PD_CARGO_NOMINATION'>PD_CARGO_NOMINATION</option>"+
"<option value='PD_CARGO_SCHEDULE'>PD_CARGO_SCHEDULE</option>"+
"<option value='PD_TRANSIT_CARRIER'>PD_TRANSIT_CARRIER</option>"+

"<option value='PD_PORT'>PD_PORT</option>"+
"<option value='PD_BERTH'>PD_BERTH</option>"+
"<option value='PD_VOYAGE'>PD_VOYAGE</option>"+
"<option value='PD_VOYAGE_DETAIL'>PD_VOYAGE_DETAIL</option>"+

"<option value='PD_LIFTING_ACCOUNT'>PD_LIFTING_ACCOUNT</option>"+
"<option value='PD_LIFTING_ACCOUNT_MTH_DATA'>PD_LIFTING_ACCOUNT_MTH_DATA</option>"+
"<option value='PD_SHIP_LNG_TANK'>PD_SHIP_LNG_TANK</option>"+
"<option value='PD_SHIP_OIL_LPG_TANK'>PD_SHIP_OIL_LPG_TANK</option>"+
"<option value='PD_SHIP_PORT_INFORMATION'>PD_SHIP_PORT_INFORMATION</option>"+
"<option value='PD_TRANSIT_DETAIL'>PD_TRANSIT_DETAIL</option>"+
"<option value='PD_TRANSPORT_GROUND_DETAIL'>PD_TRANSPORT_GROUND_DETAIL</option>"+
"<option value='PD_TRANSPORT_PIPELINE_DETAIL'>PD_TRANSPORT_PIPELINE_DETAIL</option>"+
"<option value='PD_TRANSPORT_SHIP_DETAIL'>PD_TRANSPORT_SHIP_DETAIL</option>"+
"<option value='PD_TRANSIT_PIPELINE'>PD_TRANSIT_PIPELINE</option>"+
"<option value='DEMURRAGE'>DEMURRAGE</option>"+
"<option value='PROC_TRAIN'>PROC_TRAIN</option>"+
"<option value='PROC_TRAIN_BY_PRODUCT'>PROC_TRAIN_BY_PRODUCT</option>"+
"<option>SHIP_CARGO_BLMR</option>"+
"<option>SHIP_CARGO_BLMR_DATA</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="ACTIVITY")
	{
		s=
"<option value='TERMINAL_ACTIVITY_SET'>TERMINAL_ACTIVITY_SET</option>"+
"<option value='TERMINAL_ACTIVITY_SET_LIST'>TERMINAL_ACTIVITY_SET_LIST</option>"+
"<option value='TERMINAL_TIMESHEET_DATA'>TERMINAL_TIMESHEET_DATA</option>"+
"<option value='PD_GAS_FILLING'>GAS_COOLDOWN</option>"+
"<option value='GAS_FILLING'>GAS_FILLING</option>"+
"<option value='COOLDOWN_DETAIL'>COOLDOWN_DETAIL</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="SECURITY")
	{
		s=
"<option value='USER'>USER</option>"+
"<option value='USER_RIGHT'>USER_RIGHT</option>"+
"<option value='USER_ROLE'>USER_ROLE</option>"+
"<option value='USER_ROLE_RIGHT'>USER_ROLE_RIGHT</option>"+
"<option value='USER_USER_ROLE'>USER_USER_ROLE</option>"+
"<option value='USER_DATA_SCOPE'>USER_DATA_SCOPE</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="AUDIT")
	{
		s=
"<option value='AUDIT_RECORD'>AUDIT_RECORD</option>"+
"<option value='AUDIT_TRAIL'>AUDIT_TRAIL</option>"+
"<option value='PD_CODE_AUDIT_REASON'>PD_CODE_AUDIT_REASON</option>"+
"<option value='PD_CODE_AUDIT_LOCK_STATUS'>PD_CODE_AUDIT_LOCK_STATUS</option>"+
"<option value='PD_CODE_AUDIT_RECORD_STATUS'>PD_CODE_AUDIT_RECORD_STATUS</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="DEFER")
	{
		s=
"<option value='DEFERMENT'>DEFERMENT</option>"+
"<option value='DEFERMENT_DETAIL'>DEFERMENT_DETAIL</option>"+
"<option value='DEFERMENT_GROUP'>DEFERMENT_GROUP</option>"+
"<option value='DEFERMENT_GROUP_EU'>DEFERMENT_GROUP_EU</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="TEST")
	{
		s=
"<option value='EU_TEST_DAY_FDC_VALUE'>EU_TEST_DAY_FDC_VALUE</option>"+
"<option value='EU_TEST_DAY_VALUE'>EU_TEST_DAY_VALUE</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="CONFIG")
	{
		s=
"<option value='CFG_FIELD_PROPS'>CFG_FIELD_PROPS</option>"+
"<option value='CFG_INPUT_TYPE'>CFG_INPUT_TYPE</option>"+
"<option value='CFG_DATA_SOURCE'>CFG_DATA_SOURCE</option>"+
"<option value='GRAPH'>GRAPH</option>"+
"<option value='GRAPH_DATA_SOURCE'>GRAPH_DATA_SOURCE</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="NETWORK")
	{
		s=
"<option value='NETWORK'>NETWORK</option>"+
"<option value='NETWORK_SUB'>NETWORK_SUB</option>"+
"<option value='NETWORK_CONNECTION'>NETWORK_CONNECTION</option>"+
"<option value='NETWORK_OBJECT_MAPPING'>NETWORK_OBJECT_MAPPING</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="OPERATION")
	{
		s=
"<option value='SAFETY'>SAFETY</option>"+
"<option value='FACILITY_SAFETY_CATEGORY'>FACILITY_SAFETY_CATEGORY</option>"+
"<option value='COMMENT'>COMMENT</option>"+
"<option value='EQUIPMENT'>EQUIPMENT</option>"+
"<option value='EQUIPMENT_DAY_VALUE'>EQUIPMENT_DAY_VALUE</option>"+
"<option value='EQUIPMENT_GROUP'>EQUIPMENT_GROUP</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="QUALITY")
	{
		s=
"<option value='QLTY_DATA'>QLTY_DATA</option>"+
"<option value='QLTY_DATA_DETAIL'>QLTY_DATA_DETAIL</option>"+
"<option value='PD_CODE_QLTY_SRC_TYPE'>PD_CODE_QLTY_SRC_TYPE</option>"+
"<option value='QLTY_PRODUCT_ELEMENT_TYPE'>QLTY_PRODUCT_ELEMENT_TYPE</option>"+
"<option value='QLTY_UOM'>QLTY_UOM</option>"+
"<option value='REFERENCE'>REFERENCE</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="LOGICAL")
	{
		s=
"<option value='LO_CONTINENT'>LO_CONTINENT</option>"+
"<option value='LO_COUNTRY'>LO_COUNTRY</option>"+
"<option value='LO_STATE_PROVINCE'>LO_STATE_PROVINCE</option>"+
"<option value='LO_PRODUCTION_UNIT'>LO_PRODUCTION_UNIT</option>"+
"<option value='LO_AREA'>LO_AREA</option>"+
"<option value='LO_FIELD'>LO_FIELD</option>"+
"<option value='LO_REGION'>LO_REGION</option>"+
"<option value='BA_ADDRESS'>BA_ADDRESS</option>"+
"<option value='COST_INT_CTR '>COST_INT_CTR </option>"+
"<option value='COST_INT_CTR_DETAIL '>COST_INT_CTR_DETAIL </option>"+
"<option value='COST_INT_CATEGORY'>COST_INT_CATEGORY</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="CE")
	{
		s=
"<option value='CE_LIST'>CE_LIST</option>"+
"<option value='CE_BLOCK'>CE_BLOCK</option>"+
"<option value='CE_EQUATION'>CE_EQUATION</option>"+
"<option value='CE_EQUATION_DETAIL'>CE_EQUATION_DETAIL</option>"+
"<option value='CE_EQUATION_TYPE'>CE_EQUATION_TYPE</option>"+
"<option value='CE_EQUATION_STATUS'>CE_EQUATION_STATUS</option>"+
"<option value='PRE_DEF_LOCAL_DATA_SET'>PRE_DEF_LOCAL_DATA_SET</option>"+
"<option value='PRE_DEF_GLOBAL_DATA_SET'>PRE_DEF_GLOBAL_DATA_SET</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="ENERGY")
	{
		s=
"<option value='RESERVOIR'>RESERVOIR</option>"+
"<option value='FACILITY'>FACILITY</option>"+
"<option value='ENERGY_UNIT'>ENERGY_UNIT</option>"+
"<option value='ENERGY_UNIT_GROUP'>ENERGY_UNIT_GROUP</option>"+
"<option value='ENERGY_UNIT_DAY_FDC_VALUE'>ENERGY_UNIT_DAY_FDC_VALUE</option>"+
"<option value='ENERGY_UNIT_DAY_VALUE'>ENERGY_UNIT_DAY_VALUE</option>"+
"<option value='ENERGY_UNIT_DAY_THEOR'>ENERGY_UNIT_DAY_THEOR</option>"+
"<option value='ENERGY_UNIT_DAY_ALLOC'>ENERGY_UNIT_DAY_ALLOC</option>"+
"<option value='ENERGY_UNIT_DAY_PLAN'>ENERGY_UNIT_DAY_PLAN</option>"+
"<option value='ENERGY_UNIT_DAY_FORECAST'>ENERGY_UNIT_DAY_FORECAST</option>"+
"<option value='ENERGY_UNIT_COMP_DAY_ALLOC'>ENERGY_UNIT_COMP_DAY_ALLOC</option>"+
"<option value='EU_PHASE_CONFIG'>EU_PHASE_CONFIG</option>"+
"<option value='WELL_BORE'>WELL_BORE</option>"+
"<option value='WELL_BORE_DAY_ALLOC'>WELL_BORE_DAY_ALLOC</option>"+
"<option value='WELL_BORE_INTERVAL'>WELL_BORE_INTERVAL</option>"+
"<option value='WELL_BORE_INTERVAL_DAY_ALLOC'>WELL_BORE_INTERVAL_DAY_ALLOC</option>"+
"<option value='FLOW'>FLOW</option>"+
"<option value='FLOW_DAY_FDC_VALUE'>FLOW_DAY_FDC_VALUE</option>"+
"<option value='FLOW_DAY_VALUE'>FLOW_DAY_VALUE</option>"+
"<option value='FLOW_DAY_THEOR'>FLOW_DAY_THEOR</option>"+
"<option value='FLOW_DAY_ALLOC'>FLOW_DAY_ALLOC</option>"+
"<option value='FLOW_DAY_PLAN'>FLOW_DAY_PLAN</option>"+
"<option value='FLOW_COMP_DAY_ALLOC'>FLOW_COMP_DAY_ALLOC</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="TAGMAP")
	{
		s=
"<option value='INT_TAG_MAPPING'>INT_TAG_MAPPING</option>"+
"<option value='INT_MAP_TABLE'>INT_MAP_TABLE</option>"+
"<option value='INT_TABLE_COLUMN'>INT_TABLE_COLUMN</option>"+
"<option value='INT_OBJECT_TYPE'>INT_OBJECT_TYPE</option>"+
"<option value='INT_SYSTEM'>INT_SYSTEM</option>"+
"<option value='INT_TAG_TRANS'>INT_TAG_TRANS</option>"+
"<option value='INT_IMPORT_LOG'>INT_IMPORT_LOG</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="VIEW")
	{
		s=
"<option value='V_FLOW_DATA'>V_FLOW_DATA</option>"+
"<option value='V_EU_DATA'>V_EU_DATA</option>"+
"<option value='V_TEST_DATA'>V_TEST_DATA</option>"+
"<option value='V_TANK_DATA'>V_TANK_DATA</option>"+
"<option value='V_STORAGE_DATA'>V_STORAGE_DATA</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="PERSONNEL")
	{
		s=
"<option value='PERSONNEL'>PERSONNEL</option>"+
"<option value='PERSONNEL_SUM_DAY'>PERSONNEL_SUM_DAY</option>"+
"<option value='PD_CODE_PERSONNEL_TYPE'>PD_CODE_PERSONNEL_TYPE</option>"+
"<option value='PD_CODE_PERSONNEL_TITLE'>PD_CODE_PERSONNEL_TITLE</option>"+
		"";
	}
	$("#listTables").html(s);
}
function tableChange()
{
	var s=$("#listTables :selected").text(); //$("#listTables").val();
	$("#tableHeaderName").html(s);
	if(s!="")
		$("#frameEdit").attr('src','edittable.php?table='+s);
}
</script>
<table border="0" cellpadding="10" cellspacing="0" width="100%" id="table2">
	<tr>
		<td width="250">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table3">
			<tr>
				<td height="40">
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table5">
					<tr>
						<td><b><font size="2">Category</font></b></td>
						<td align="right">
				<select style="" size="1" onChange="scopeChange()" name="cboObjectScope" id="cboObjectScope">
				<option value="CODE">CODE tables</option>
				<option value="CONTRACT">CONTRACT tables</option>
				<option value="DOCUMENT">DOCUMENT tables</option>
				<option value="ACTIVITY">ACTIVITY tables</option>
				<option value="OBJECT">OBJECT tables</option>
				</select></td>
					</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td height="440" valign="top">
				<SELECT style="width:100%;height:100%" SIZE=5 name="listTables" onclick="tableChange()" id="listTables">
</SELECT></td>
			</tr>
		</table>
		</td>
		<td valign="top">
<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table4">
	<tr>
		<td>
		<div style="padding:5px;background:#D4E5EE; border-radius:4px">
	<input onClick="document.getElementById('frameEdit').contentWindow.addRecord();" style="height:30; width:110;" type="button" value="Add Record" name="B33">
	<input onClick="document.getElementById('frameEdit').contentWindow.saveChanges();" style="margin-right:5px;height:30; width:110;" type="button" value="Save Changes">			
	<span id="tableHeaderName" style="font-size:10pt;font-weight:bold">Data table</span>
		</div>
	</tr>
	<tr>
		<td height="430">
		<iframe id="frameEdit" style="width:100%;height:100%;padding:0px;border:medium none; " name="I1"></iframe>
		</td>
	</tr>
</table>
		</td>
	</tr>
</table>
<script>
scopeChange();
//$("#pageheader").load("../home/header.php?menu=config");
</script>
<div style="text-align:center;padding:5px;color:#666"><font face="Arial" size="1">Copyright &copy; 2016 eDataViz LLC</font></div>
</body>

</html>