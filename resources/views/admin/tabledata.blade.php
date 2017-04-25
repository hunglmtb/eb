<?php
$currentSubmenu 					='/loadtabledata';
if (!isset($subMenus)) $subMenus 	= [];
$enableFilter 						= false;
if (!isset($active)) $active 		= 1;
if (!isset($isAction)) $isAction 	= false;
$tables 							= ['FlowDataFdcValue'	=>['name'=>'FDC VALUE']];
$useBootrapCss						= false;
?>

@extends('core.bsmain',['subMenus' => $subMenus])

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/loadtabledata/load";
	actions.saveUrl 		= "/loadtabledata/save";
	actions.type = {
					idName:['ID'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
						},
					};

	actions.tableChange = function(e){
		var s=$("#listTables :selected").text();
		if(s!="" && s!="undefined")
		{
			$("#tableHeaderName").html(s);
// 			actions.doLoad(true);
// 	 		$("#frameEdit").attr('src','tabledata/edittable.php?table='+s);
// 	 		$("#frameEdit").attr('src','loadtabledata/edittable/'+s+"/index");
 	 		$("#frameEdit").attr('src','loadtabledata/edittable?table='+s);
		}
	}
</script>
@stop

@section('content')
<script>
function scopeChange(c){
	var s="";
	if(c) s=c; else s=$("#cboObjectScope").val();
	if(s=="CODE")
	{
		s=
"<option value='CODE_ALLOCATION_NODE'>CODE_ALLOCATION_NODE</option>"+
"<option value='CODE_ALLOC_CODE'>CODE_ALLOC_CODE</option>"+
"<option value='CODE_ALLOC_TYPE'>CODE_ALLOC_TYPE</option>"+
"<option value='CODE_BA_TYPE'>CODE_BA_TYPE</option>"+
"<option value='CODE_BOOLEAN'>CODE_BOOLEAN</option>"+
"<option value='CODE_COMMENT_TYPE'>CODE_COMMENT_TYPE</option>"+
"<option value='CODE_COMMENT_STATUS'>CODE_COMMENT_STATUS</option>"+
"<option value='CODE_COMPOSITION'>CODE_COMPOSITION</option>"+
"<option value='CODE_COLLECTION_POINT_FACILITY'>CODE_COLLECTION_POINT_FACILITY</option>"+
"<option value='CODE_CONTRAINST_SETTING_UOM'>CODE_CONTRAINST_SETTING_UOM</option>"+
"<option value='CODE_DATA_METHOD'>CODE_DATA_METHOD</option>"+
"<option value='CODE_DEFER_CODE1'>CODE_DEFER_CODE1</option>"+
"<option value='CODE_DEFER_CODE2'>CODE_DEFER_CODE2</option>"+
"<option value='CODE_DEFER_CODE3'>CODE_DEFER_CODE3</option>"+
"<option value='CODE_DEFER_GROUP_TYPE'>CODE_DEFER_GROUP_TYPE</option>"+
"<option value='CODE_DEFER_REASON'>CODE_DEFER_REASON</option>"+
"<option value='CODE_DEFER_STATUS'>CODE_DEFER_STATUS</option>"+
"<option value='CODE_DEFER_CATEGORY'>CODE_DEFER_CATEGORY</option>"+
"<option value='CODE_DENSITY_METHOD'>CODE_DENSITY_METHOD</option>"+
"<option value='CODE_ENERGY_METHOD'>CODE_ENERGY_METHOD</option>"+
"<option value='CODE_ENERGY_UNIT_TYPE'>CODE_ENERGY_UNIT_TYPE</option>"+
"<option>CODE_EQUIPMENT_TYPE</option>"+
"<option>CODE_PLAN_TYPE</option>"+
"<option>CODE_FORECAST_TYPE</option>"+
"<option value='CODE_EQP_OFFLINE_REASON'>CODE_EQP_OFFLINE_REASON</option>"+
"<option value='CODE_EQP_FUEL_CONS_TYPE'>CODE_EQP_FUEL_CONS_TYPE</option>"+
"<option value='CODE_EQP_GHG_REL_TYPE'>CODE_EQP_GHG_REL_TYPE</option>"+
"<option value='CODE_EU_SUB_TYPE'>CODE_EU_SUB_TYPE</option>"+
"<option value='CODE_EVENT_TYPE'>CODE_EVENT_TYPE</option>"+
"<option value='CODE_FACILITY_TYPE'>CODE_FACILITY_TYPE</option>"+
"<option value='CODE_FIXED_ADJUST'>CODE_FIXED_ADJUST</option>"+
"<option value='CODE_FLOW_CATEGORY'>CODE_FLOW_CATEGORY</option>"+
"<option value='CODE_FLOW_DISP'>CODE_FLOW_DISP</option>"+
"<option value='CODE_FLOW_PHASE'>CODE_FLOW_PHASE</option>"+
"<option value='CODE_FLOW_SUB_PHASE'>CODE_FLOW_SUB_PHASE</option>"+
"<option value='CODE_GAS_COMPOSITION_METHOD'>CODE_GAS_COMPOSITION_METHOD</option>"+
"<option value='CODE_GVOL_METHOD'>CODE_GVOL_METHOD</option>"+
"<option value='CODE_GMASS_METHOD'>CODE_GMASS_METHOD</option>"+
"<option value='CODE_INJECTING_METHOD'>CODE_INJECTING_METHOD</option>"+
"<option value='CODE_INJECTION_PLAN_METHOD'>CODE_INJECTION_PLAN_METHOD</option>"+
"<option value='CODE_LIQUID_COMPONENT_ANALYSIS_METHOD'>CODE_LIQUID_COMPONENT_ANALYSIS_METHOD</option>"+
"<option value='CODE_NETWORK_TYPE'>CODE_NETWORK_TYPE</option>"+
"<option value='CODE_NVOL_METHOD'>CODE_NVOL_METHOD</option>"+
"<option value='CODE_NMASS_METHOD'>CODE_NMASS_METHOD</option>"+
"<option value='CODE_OBJECT_CLASS'>CODE_OBJECT_CLASS</option>"+
"<option value='CODE_ON_OFFSHORE'>CODE_ON_OFFSHORE</option>"+
"<option value='CODE_ONSTREAM_METHOD'>CODE_ONSTREAM_METHOD</option>"+
"<option value='CODE_PLAN_METHOD'>CODE_PLAN_METHOD</option>"+
"<option value='CODE_POTENTIAL_METHOD'>CODE_POTENTIAL_METHOD</option>"+
"<option value='CODE_POWER_METHOD'>CODE_POWER_METHOD</option>"+
"<option value='CODE_PRODUCING_METHOD'>CODE_PRODUCING_METHOD</option>"+
"<option value='CODE_PRODUCT_TYPE'>CODE_PRODUCT_TYPE</option>"+
"<option value='CODE_PRODUCTION_DATA_START'>CODE_PRODUCTION_DATA_START</option>"+
"<option value='CODE_PRODUCTION_PLAN_METHOD'>CODE_PRODUCTION_PLAN_METHOD</option>"+
"<option value='CODE_PHYSICAL_FORM'>CODE_PHYSICAL_FORM</option>"+
"<option value='CODE_READING_FREQUENCY'>CODE_READING_FREQUENCY</option>"+
"<option value='CODE_SAFETY_CATEGORY'>CODE_SAFETY_CATEGORY</option>"+
"<option value='CODE_SAFETY_SEVERITY'>CODE_SAFETY_SEVERITY</option>"+
"<option value='CODE_SDS_CAPACITY_METHOD'>CODE_SDS_CAPACITY_METHOD</option>"+
"<option value='CODE_STATUS'>CODE_STATUS</option>"+
"<option value='CODE_STORAGE_TYPE'>CODE_STORAGE_TYPE</option>"+
"<option value='CODE_STRAPPING_MEASURE'>CODE_STRAPPING_MEASURE</option>"+
"<option value='CODE_STRAPPING_METHOD'>CODE_STRAPPING_METHOD</option>"+
"<option value='CODE_STRAPPING_UOM'>CODE_STRAPPING_UOM</option>"+
"<option value='CODE_SW_VOL_METHOD'>CODE_SW_VOL_METHOD</option>"+
"<option value='CODE_TANK_TYPE'>CODE_TANK_TYPE</option>"+
"<option value='CODE_TANK_USAGE'>CODE_TANK_USAGE</option>"+
"<option value='CODE_TICKET_TYPE'>CODE_TICKET_TYPE</option>"+
"<option value='CODE_TESTING_METHOD'>CODE_TESTING_METHOD</option>"+
"<option value='CODE_TESTING_USAGE'>CODE_TESTING_USAGE</option>"+
"<option value='CODE_THEORETICAL_METHOD'>CODE_THEORETICAL_METHOD</option>"+
"<option value='CODE_PORTABLE_TANK_LOCATION'>CODE_PORTABLE_TANK_LOCATION</option>"+
"<option>CODE_MMR_CLASS</option>"+
"<option>CODE_MMR_ROOT_CAUSE</option>"+
"<option>CODE_MMR_STATUS</option>"+
"<option>CODE_MMR_WO_ACTION</option>"+
"<option>CODE_MMR_REASON</option>"+
"<option>CODE_MMR_CALC_METHOD</option>"+
				'';
	}
	else if($("#cboObjectScope").val()=="UOM")
	{
		s=

"<option value='STANDARD_UOM'>STANDARD_UOM</option>"+
"<option value='CODE_QUANTITY_TYPE'>CODE_QUANTITY_TYPE</option>"+
"<option value='CODE_UOM_TYPE'>CODE_UOM_TYPE</option>"+
"<option value='CODE_VOL_UOM'>CODE_VOL_UOM</option>"+
"<option value='CODE_MASS_UOM'>CODE_MASS_UOM</option>"+
"<option value='CODE_POWER_UOM'>CODE_POWER_UOM</option>"+
"<option value='CODE_ENERGY_UOM'>CODE_ENERGY_UOM</option>"+
"<option value='CODE_DENS_UOM'>CODE_DENS_UOM</option>"+
"<option value='CODE_TEMP_UOM'>CODE_TEMP_UOM</option>"+
"<option value='CODE_PRESS_UOM'>CODE_PRESS_UOM</option>"+
"<option value='CODE_VOL_RATE_UOM'>CODE_VOL_RATE_UOM</option>"+
"<option value='CODE_MASS_RATE_UOM'>CODE_MASS_RATE_UOM</option>"+
"<option value='CODE_PWR_RATE_UOM'>CODE_PWR_RATE_UOM</option>"+

"<option value='CODE_LENGTH_UOM'>CODE_LENGTH_UOM</option>"+
"<option value='CODE_TIME_UOM'>CODE_TIME_UOM</option>"+
"<option value='CODE_AREA_UOM'>CODE_AREA_UOM</option>"+
"<option value='CODE_WATER_DENS_UOM'>CODE_WATER_DENS_UOM</option>"+
"<option value='CODE_MOLECULAR_WEIGHT_UOM'>CODE_MOLECULAR_WEIGHT_UOM</option>"+
"<option value='CODE_VISCOSITY_UOM'>CODE_VISCOSITY_UOM</option>"+
"<option value='CODE_COMPRESSIBILITY_UOM'>CODE_COMPRESSIBILITY_UOM</option>"+
"<option value='CODE_PERMIABILITY_UOM'>CODE_PERMIABILITY_UOM</option>"+

"<option value='UOM_CONVERSION'>UOM_CONVERSION</option>"+
"<option value='API_6A'>API_6A</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="STORAGE")
	{
		s=
"<option value='TANK'>TANK</option>"+
"<option value='TANK_DATA_FDC_VALUE'>TANK_DATA_FDC_VALUE</option>"+
"<option value='TANK_DATA_VALUE'>TANK_DATA_VALUE</option>"+
"<option value='TANK_DATA_THEOR'>TANK_DATA_THEOR</option>"+
"<option value='TANK_DATA_ALLOC'>TANK_DATA_ALLOC</option>"+
"<option value='TANK_DATA_PLAN'>TANK_DATA_PLAN</option>"+
"<option value='TANK_DATA_FORECAST'>TANK_DATA_FORECAST</option>"+
"<option value='STORAGE'>STORAGE</option>"+
"<option value='STORAGE_DATA_FDC_VALUE'>STORAGE_DATA_FDC_VALUE</option>"+
"<option value='STORAGE_DATA_VALUE'>STORAGE_DATA_VALUE</option>"+
"<option value='STORAGE_DATA_THEOR'>STORAGE_DATA_THEOR</option>"+
"<option value='STORAGE_DATA_ALLOC'>STORAGE_DATA_ALLOC</option>"+
"<option value='STORAGE_DATA_PLAN'>STORAGE_DATA_PLAN</option>"+
"<option value='STORAGE_DATA_FORECAST'>STORAGE_DATA_FORECAST</option>"+
"<option value='STRAPPING_TABLE'>STRAPPING_TABLE</option>"+
"<option value='STRAPPING_TABLE_DATA'>STRAPPING_TABLE_DATA</option>"+
"<option value='RUN_TICKET_VALUE'>RUN_TICKET_VALUE</option>"+
"<option value='RUN_TICKET_FDC_VALUE'>RUN_TICKET_FDC_VALUE</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="RESERVOIR")
	{
		s=
"<option value='RESERVOIR'>RESERVOIR</option>"+
"<option value='RESERVOIR_BLOCK'>RESERVOIR_BLOCK</option>"+
"<option value='RESERVOIR_FORMATION'>RESERVOIR_FORMATION</option>"+
"<option value='RESERVOIR_BLOCK_FORMATION'>RESERVOIR_BLOCK_FORMATION</option>"+
"<option value='WELL_COMP_INTERVAL_PERF'>WELL_COMP_INTERVAL_PERF</option>"+
"<option value='WELL_COMP_INTERVAL'>WELL_COMP_INTERVAL</option>"+
"<option value='WELL_COMP'>WELL_COMP</option>"+
"<option value='ENERGY_UNIT'>ENERGY_UNIT (WELL)</option>"+
"<option value='WELL_HOLE'>WELL_HOLE</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="CHEMICAL")
	{
		s=
"<option value='KEYSTORE'>KEYSTORE</option>"+
"<option value='KEYSTORE_TANK'>KEYSTORE_TANK</option>"+
"<option value='KEYSTORE_STORAGE'>KEYSTORE_STORAGE</option>"+
"<option value='KEYSTORE_INJECTION_POINT'>KEYSTORE_INJECTION_POINT</option>"+
"<option value='KEYSTORE_INJECTION_POINT_DAY'>KEYSTORE_INJECTION_POINT_DAY</option>"+
"<option value='KEYSTORE_INJECTION_POINT_CHEMICAL'>KEYSTORE_INJECTION_POINT_CHEMICAL</option>"+
"<option value='KEYSTORE_TANK_DATA_VALUE'>KEYSTORE_TANK_DATA_VALUE</option>"+
"<option value='KEYSTORE_STORAGE_DATA_VALUE'>KEYSTORE_STORAGE_DATA_VALUE</option>"+
"<option value='CODE_KEYSTORE_TYPE'>CODE_KEYSTORE_TYPE</option>"+
"<option value='CODE_KEYSTORE_USAGE'>CODE_KEYSTORE_USAGE</option>"+
"<option value='CODE_INJECT_POINT'>CODE_INJECT_POINT</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="SECURITY")
	{
		s=
"<option value='USER'>USER</option>"+
"<option value='USER_RIGHT'>USER_RIGHT</option>"+
"<option>USER_RIGHT_GUI</option>"+
"<option value='USER_ROLE'>USER_ROLE</option>"+
"<option value='USER_ROLE_RIGHT'>USER_ROLE_RIGHT</option>"+
"<option value='USER_USER_ROLE'>USER_USER_ROLE</option>"+
"<option value='USER_DATA_SCOPE'>USER_DATA_SCOPE</option>"+
"<option>LOG_USER</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="AUDIT")
	{
		s=
"<option value='AUDIT_RECORD'>AUDIT_RECORD</option>"+
"<option value='AUDIT_TRAIL'>AUDIT_TRAIL</option>"+
"<option value='CODE_AUDIT_REASON'>CODE_AUDIT_REASON</option>"+
"<option value='CODE_AUDIT_LOCK_STATUS'>CODE_AUDIT_LOCK_STATUS</option>"+
"<option value='CODE_AUDIT_RECORD_STATUS'>CODE_AUDIT_RECORD_STATUS</option>"+
"<option value='AUDIT_VALIDATE_TABLE'>AUDIT_VALIDATE_TABLE</option>"+
"<option value='AUDIT_APPROVE_TABLE'>AUDIT_APPROVE_TABLE</option>"+
"<option value='LOCK_TABLE'>LOCK_TABLE</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="DEFER")
	{
		s=
"<option value='DEFERMENT'>DEFERMENT</option>"+
"<option value='DEFERMENT_DETAIL'>DEFERMENT_DETAIL</option>"+
"<option value='DEFERMENT_GROUP'>DEFERMENT_GROUP</option>"+
"<option value='DEFERMENT_GROUP_EU'>DEFERMENT_GROUP_EU</option>"+
"<option value='WORK_ORDER'>WORK_ORDER</option>"+
"<option>MIS_MEASUREMENT</option>"+
"<option>WORK_ORDER_MMR</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="TEST")
	{
		s=
"<option value='EU_TEST_DATA_FDC_VALUE'>EU_TEST_DATA_FDC_VALUE</option>"+
"<option value='EU_TEST_DATA_STD_VALUE'>EU_TEST_DATA_STD_VALUE</option>"+
"<option value='EU_TEST_DATA_VALUE'>EU_TEST_DATA_VALUE</option>"+
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
"<option value='ALLOC_JOB'>ALLOC_JOB</option>"+
"<option value='ALLOC_RUNNER'>ALLOC_RUNNER</option>"+
"<option value='ALLOC_RUNNER_OBJECTS'>ALLOC_RUNNER_OBJECTS</option>"+
"<option value='ALLOC_CONDITION'>ALLOC_CONDITION</option>"+
"<option value='ALLOC_COND_OUT'>ALLOC_COND_OUT</option>"+
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
"<option>EQUIPMENT_DATA_VALUE</option>"+
"<option>EQUIPMENT_DATA_PLAN</option>"+
"<option>EQUIPMENT_DATA_FORECAST</option>"+
"<option value='EQUIPMENT_GROUP'>EQUIPMENT_GROUP</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="QUALITY")
	{
		s=
"<option value='QLTY_DATA'>QLTY_DATA</option>"+
"<option value='QLTY_DATA_DETAIL'>QLTY_DATA_DETAIL</option>"+
"<option value='CODE_QLTY_SRC_TYPE'>CODE_QLTY_SRC_TYPE</option>"+
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
"<option value='LICENSE '>LICENSE</option>"+
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
"<option>FORMULA</option>"+
"<option>FO_VAR</option>"+
"<option value='FO_GROUP'>FO_GROUP</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="ENERGY")
	{
		s=
"<option value='FACILITY'>FACILITY</option>"+
"<option value='ENERGY_UNIT'>ENERGY_UNIT</option>"+
"<option value='ENERGY_UNIT_HISTORY'>ENERGY_UNIT_HISTORY</option>"+
"<option value='ENERGY_UNIT_GROUP'>ENERGY_UNIT_GROUP</option>"+
"<option value='ENERGY_UNIT_DATA_FDC_VALUE'>ENERGY_UNIT_DATA_FDC_VALUE</option>"+
"<option value='ENERGY_UNIT_DATA_VALUE'>ENERGY_UNIT_DATA_VALUE</option>"+
"<option value='ENERGY_UNIT_DATA_THEOR'>ENERGY_UNIT_DATA_THEOR</option>"+
"<option value='ENERGY_UNIT_DATA_ALLOC'>ENERGY_UNIT_DATA_ALLOC</option>"+
"<option value='ENERGY_UNIT_DATA_PLAN'>ENERGY_UNIT_DATA_PLAN</option>"+
"<option value='ENERGY_UNIT_DATA_FORECAST'>ENERGY_UNIT_DATA_FORECAST</option>"+
"<option value='ENERGY_UNIT_COMP_DATA_ALLOC'>ENERGY_UNIT_COMP_DATA_ALLOC</option>"+
"<option value='ENERGY_UNIT_CO_ENT_DATA_ALLOC'>ENERGY_UNIT_CO_ENT_DATA_ALLOC</option>"+
"<option value='ENERGY_UNIT_CO_ENT_COMP_DATA_ALLOC'>ENERGY_UNIT_CO_ENT_COMP_DATA_ALLOC</option>"+
"<option value='EU_PHASE_CONFIG'>EU_PHASE_CONFIG</option>"+
"<option value='WELL_COMP'>WELL_COMP</option>"+
"<option value='WELL_COMP_DATA_ALLOC'>WELL_COMP_DATA_ALLOC</option>"+
"<option value='WELL_COMP_INTERVAL'>WELL_COMP_INTERVAL</option>"+
"<option value='WELL_COMP_INTERVAL_DATA_ALLOC'>WELL_COMP_INTERVAL_DATA_ALLOC</option>"+
"<option value='WELL_COMP_INTERVAL_PERF'>WELL_COMP_INTERVAL_PERF</option>"+
"<option value='WELL_COMP_INTERVAL_PERF_DATA_ALLOC'>WELL_COMP_INTERVAL_PERF_DATA_ALLOC</option>"+
"<option value='FLOW'>FLOW</option>"+
"<option value='FLOW_DATA_FDC_VALUE'>FLOW_DATA_FDC_VALUE</option>"+
"<option value='FLOW_DATA_VALUE'>FLOW_DATA_VALUE</option>"+
"<option value='FLOW_DATA_THEOR'>FLOW_DATA_THEOR</option>"+
"<option value='FLOW_DATA_ALLOC'>FLOW_DATA_ALLOC</option>"+
"<option value='FLOW_DATA_PLAN'>FLOW_DATA_PLAN</option>"+
"<option value='FLOW_DATA_FORECAST'>FLOW_DATA_FORECAST</option>"+
"<option value='FLOW_COMP_DATA_ALLOC'>FLOW_COMP_DATA_ALLOC</option>"+
"<option value='FLOW_CO_ENT_DATA_ALLOC'>FLOW_CO_ENT_DATA_ALLOC</option>"+
"<option value='FLOW_CO_ENT_COMP_DATA_ALLOC'>FLOW_CO_ENT_COMP_DATA_ALLOC</option>"+
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
"<option value='CODE_PERSONNEL_TYPE'>CODE_PERSONNEL_TYPE</option>"+
"<option value='CODE_PERSONNEL_TITLE'>CODE_PERSONNEL_TITLE</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="REPORT")
	{
		s=
"<option>RPT_GROUP</option>"+
"<option>RPT_REPORT</option>"+
"<option>RPT_PARAM</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="DATACAPTURE")
	{
		s=
"<option>DC_ROUTE</option>"+
"<option>DC_ROUTE_USER</option>"+
"<option>DC_POINT</option>"+
"<option>DC_POINT_FLOW</option>"+
"<option>DC_POINT_EU</option>"+
"<option>DC_POINT_TANK</option>"+
"<option>DC_POINT_EQUIPMENT</option>"+
		"";
	}
	else if($("#cboObjectScope").val()=="GHG")
	{
		s=
"<option value='CODE_FUEL_TYPE_SUB'>CODE_FUEL_TYPE_SUB</option>"+
"<option value='CODE_GHG_PWR_RATE_UOM'>CODE_GHG_PWR_RATE_UOM</option>"+
"<option value='CODE_GHG_VOL_RATE_UOM'>CODE_GHG_VOL_RATE_UOM</option>"+
"<option value='CODE_GHG_ENGY_RATE_UOM'>CODE_GHG_ENGY_RATE_UOM</option>"+
"<option value='CODE_GHG_MASS_RATE_UOM'>CODE_GHG_MASS_RATE_UOM</option>"+
"<option value='CODE_GHG_UOM'>CODE_GHG_UOM</option>"+
"<option value='CODE_GHG_HEAT_VALUE'>CODE_GHG_HEAT_VALUE</option>"+
"<option value='CODE_PROTOCOL'>CODE_PROTOCOL</option>"+
"<option value='CODE_SECTOR'>CODE_SECTOR</option>"+
"<option value='CODE_SEGMENT'>CODE_SEGMENT</option>"+
"<option value='CODE_SOURCE_CATEGORY'>CODE_SOURCE_CATEGORY</option>"+
"<option value='CODE_SOURCE_CLASS'>CODE_SOURCE_CLASS</option>"+
"<option value='CODE_EPA_SOURCE_TYPE'>CODE_EPA_SOURCE_TYPE</option>"+
"<option value='CODE_API_SOURCE_TYPE'>CODE_API_SOURCE_TYPE</option>"+
"<option value='REL_SEGMENT_SOURCE_CATEGORY'>REL_SEGMENT_SOURCE_CATEGORY</option>"+
"<option value='REL_SOURCE_CLASS_EPA_SOURCE_TYPE'>REL_SOURCE_CLASS_EPA_SOURCE_TYPE</option>"+
"<option value='CODE_EMISSION_CALC_BY'>CODE_EMISSION_CALC_BY</option>"+
"<option value='CODE_EMISSION_EVENT_TYPE'>CODE_EMISSION_EVENT_TYPE</option>"+
"<option value='CODE_EMISSION_METHOD'>CODE_EMISSION_METHOD</option>"+
"<option value='CODE_CALC_SECTION'>CODE_CALC_SECTION</option>"+
"<option value='CODE_CALC_OPTION'>CODE_CALC_OPTION</option>"+
"<option value='CODE_EPA_SECTOR'>CODE_EPA_SECTOR</option>"+
"<option value='COMBUSTION_EMISSION_GROUP'>COMBUSTION_EMISSION_GROUP</option>"+
"<option value='COMBUSTION_EMISSION_CALC_METHOD'>COMBUSTION_EMISSION_CALC_METHOD</option>"+
"<option value='INDIRECT_EMISSION_GROUP'>INDIRECT_EMISSION_GROUP</option>"+
"<option value='INDIRECT_EMISSION_CALC_METHOD'>INDIRECT_EMISSION_CALC_METHOD</option>"+
"<option value='EVENT_EMISSION_GROUP'>EVENT_EMISSION_GROUP</option>"+
"<option value='EMISSION_VENT'>EMISSION_VENT</option>"+
"<option value='EVENT_EMISSION_CALC_METHOD'>EVENT_EMISSION_CALC_METHOD</option>"+
"<option value='EMISSION_FACTOR'>EMISSION_FACTOR</option>"+
"<option value='EMISSION_FACTOR_TABLE'>EMISSION_FACTOR_TABLE</option>"+
"<option value='EMISSION_FORMULA'>EMISSION_FORMULA</option>"+
"<option value='HEAT_FACTOR'>HEAT_FACTOR</option>"+
"<option value='REL_EMI_FACTOR_TABLE_CALC_OPTION'>REL_EMI_FACTOR_TABLE_CALC_OPTION</option>"+
"<option value='REL_EMI_FORMULA_CALC_OPTION'>REL_EMI_FORMULA_CALC_OPTION</option>"+
"<option value='GHG_EPA_FRS_ID_XREF'>GHG_EPA_FRS_ID_XREF</option>"+
"<option value='GLOBAL_WARMING_POTENTIAL'>GLOBAL_WARMING_POTENTIAL</option>"+
"<option value='GHG_OBJECT_SETTING'>GHG_OBJECT_SETTING</option>"+
"<option value='EMISSION_EVENT_DATA_VALUE'>EMISSION_EVENT_DATA_VALUE</option>"+
"<option value='EMISSION_EVENT_RELEASE_DATA_VALUE'>EMISSION_EVENT_RELEASE_DATA_VALUE</option>"+
"<option value='EMISSION_INDIRECT_DATA_VALUE'>EMISSION_INDIRECT_DATA_VALUE</option>"+
"<option value='EMISSION_INDIRECT_RELEASE_DATA_VALUE'>EMISSION_INDIRECT_RELEASE_DATA_VALUE</option>"+
"<option value='EMISSION_COMB_DATA_VALUE'>EMISSION_COMB_DATA_VALUE</option>"+
"<option value='EMISSION_COMB_RELEASE_DATA_VALUE'>EMISSION_COMB_RELEASE_DATA_VALUE</option>"+
		"";
	}
	$("#listTables").html(s);
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
				<option value="LOGICAL">LOGICAL tables</option>
				<option value="CE">CALC. ENGINE tables</option>
				<option value="UOM">UOM tables</option>
				<option value="CONFIG">CONFIG tables</option>
				<option value="ENERGY">ENERGY OBJECT tables</option>
				<option value="STORAGE">TANKS, STORAGE tables</option>
				<option value="DEFER">Deferment and MMR tables</option>
				<option value="TEST">TEST tables</option>
				<option value="RESERVOIR">RESERVOIR tables</option>
				<option value="NETWORK">NETWORK tables</option>
				<option value="OPERATION">OPERATION tables</option>
				<option value="QUALITY">QUALITY DATA tables</option>
				<option value="CHEMICAL">CHEMICAL tables</option>
				<option value="PERSONNEL">PERSONNEL tables</option>
				<option value="SECURITY">SECURITY tables</option>
				<option value="AUDIT">AUDIT TRAIL tables</option>
				<option value="TAGMAP">TAG MAPPING</option>
				<option value="GHG">GHG tables</option>
				<option value="REPORT">REPORT tables</option>
				<option value="DATACAPTURE">DATA CAPTURE tables</option>
				</select></td>
					</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td height="440" valign="top">
				<SELECT style="width:100%;height:100%" SIZE=5 name="listTables" id="listTables" onclick="actions.tableChange()">
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
	<input onClick="document.getElementById('frameEdit').contentWindow._delete_rows();" style="height:30; width:110;" type="button" value="Delete" name="B33">
	<input onClick="document.getElementById('frameEdit').contentWindow.saveChanges();" style="margin-right:5px;height:30; width:110;" type="button" value="Save Changes">			
	<input onClick="document.getElementById('frameEdit').contentWindow.genSQL(2);" style="float:right;margin-right:5px;height:30; width:130;" type="button" value="Generate Update">			
	<input onClick="document.getElementById('frameEdit').contentWindow.genSQL(1);" style="float:right;margin-right:5px;height:30; width:130;" type="button" value="Generate Insert">			
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
</script>
@stop
