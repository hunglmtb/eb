<?php
	$currentSubmenu =	'/fp/allocateplan';
	$key 			= 	'allocateplan';
 ?>

@extends('core.fp')
@section('funtionName')
MANUAL ALLOCATE PLAN
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/allocateplan/load";
	actions.saveUrl = "/allocateplan/save";
	var tab			= '{{$key}}';
	actions.type = {
					idName	:	function (){
									var postData = actions.loadedData[tab];
									if(postData.IntObjectTypeName=='FLOW') return ['FLOW_ID','OCCUR_DATE'];
									if(postData.IntObjectTypeName=='ENERGY_UNIT') return ['EU_ID','EU_FLOW_PHASE','OCCUR_DATE'];
									return [''+postData.IntObjectTypeName+'_ID','OCCUR_DATE'];
								},
					keyField:'DT_RowId',
					saveKeyField : function (model){
							return 'ID';
						},
					};
	actions.renderFirsColumn = null;
	actions.getTableHeight	=	function(tab){
		headerOffset = $('#container_allocateplan').offset();
		hhh = $(document).height() - (headerOffset?(headerOffset.top):0) - $('#ebFooter').outerHeight() -135;
		tHeight = ""+hhh+'px';
		return tHeight;
	};
	/* actions.getExtendWidth	= function(data,autoWidth,tab){
		return 280;
	} */
	var objs="";

	function addObject()
 	{
 		var id=$("").val();
 		var s='<span style="display:block;margin:1px 0px" info="'+
 		$("#IntObjectType option:selected").attr('name')+
 		':'+
 		$("#ObjectName").val()+
 		':'+
 		$("#ObjectName option:selected").text()+
 		'">'+$("#IntObjectType option:selected").text()+
 		':'+
 		$("#ObjectName option:selected").text()+
 		' <img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="../img/x.png">';
 		
 		$("#selected_objects").append(s);
 	}
 	
	actions.initData = function(){

		var tabdata = {'{{config("constants.tabTable")}}'	:	tab,
					IntObjectTypeName :		$("#IntObjectType option:selected").attr('name')
				};
		return tabdata;
	}

	actions.loadValidating = function (reLoadParams){
		return true;
	}

	actions.getTableOption	= function(data){
		return {tableOption :	{searching		: false,
								ordering		: false,
								disableLeftFixer: true,
								autoWidth		: false,
// 								scrollY			: '350px',
								},
				invisible:[]};
		
	}
	
	actions.validating = function (reLoadParams){
		return true;
	}

	actions.afterGotSavedData = function (data,table,tab){
    	var editedData = table.data();
    	 $.each(editedData, function( i, rowData ) {
    		 	var id = rowData['DT_RowId'];
    		 	if ((typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1)) {
    		 		table.row($('#'+id)).remove().draw(false);
			    }
          });

    	 postData = data.postData;
    	 if(postData!=null&&postData.deleteData.hasOwnProperty(tab)
    	    	 &&postData.deleteData[tab]!=null
    			 &&postData.deleteData[tab].hasOwnProperty('clearTable')
    			 &&typeof(data.postData.deleteData[tab].clearTable) !== "undefined"
        		 &&data.postData.deleteData[tab].clearTable){
    		 	table.clear().draw(false);
			}
	};
	
	function getPreFix(source_type){
		obj_id_prefix	=	source_type;
		field_prefix	=	source_type;
		if(source_type=="ENERGY_UNIT"){
			obj_id_prefix	="EU";
		}
		else if(source_type=="FLOW") {
			obj_id_prefix="FL";
		}
		
		if(source_type=="FLOW"||source_type=="ENERGY_UNIT"){
			field_prefix	= obj_id_prefix+"_DATA";
		}
		return field_prefix+'_';
	}

	function deletePlan()
	{
		var id=$("#ObjectName").val();
		if(id<=0){
			alert("Please select object");
			return;
		}
		if(!confirm("Do you want to delete plan data?")) return;

		tab = '{{$key}}';
		actions.deleteData[tab] = {source_type	: $("#IntObjectType option:selected").attr('name'),
											clearTable	:true};
		actions.editedData = {tab	: []};
		actions.doSave(true);
	}

	function calculateAllocPlan(){

		var index = 1000;
		
		getRowData = function (date,prefix,postData,source_type){
			row = {
					"DT_RowId": 'NEW_RECORD_DT_RowId'+(index++),
					"OCCUR_DATE": date,
// 					"FLOW_ID": 434,
				};
			row[prefix+"GRS_VOL"] 		= s_grs_vol;
			row[prefix+"GRS_MASS"] 		= s_grs_mass;
			row[prefix+"GRS_ENGY"] 		= s_grs_energy;
			row[prefix+"GRS_PWR"] 		= s_grs_power;

			if(source_type=="ENERGY_UNIT"){
				row["EU_FLOW_PHASE"] 		= postData.ExtensionPhaseType;
				row["EU_ID"] 				= postData.ObjectName;
			}
			else if(source_type=="FLOW") {
				row["FLOW_ID"] 				= postData.ObjectName;
			}
			else row[""+source_type+"_ID"] 	= postData.ObjectName;
			
			var eData = actions.editedData[tab];
    		eData.push(row);
			return row;
		}
		
		//$(".cal_row").remove();
		var d1 = $("#date_begin").datepicker( 'getDate' );
		var d2 = $("#date_end").datepicker( 'getDate' );
		var c=0;
		var df=$("#cboDataFreq").val();
		if(df=="d"){
			for (var d = d1; d <= d2; d.setDate(d.getDate() + 1)) {
				c++;
			}
		}
		else if(df=="w"){
			for (var d = d1; d <= d2; d.setDate(d.getDate() + 7)) {
				c++;
			}
		}
		else if(df=="m"){
			for (var d = d1; d <= d2; d.setMonth(d.getMonth() + 1)) {
				c++;
			}
		}
		if(c<=0){
			alert("No date to calculate");
			return;
		}
		//alert("count="+c); return;
		var t_grs_vol=parseFloat($("#t_grs_vol").val());
		var t_grs_mass=parseFloat($("#t_grs_mass").val());
		var t_grs_energy=parseFloat($("#t_grs_energy").val());
		var t_grs_power=parseFloat($("#t_grs_power").val());
		var s_grs_vol="";
		var s_grs_mass="";
		var s_grs_energy="";
		var s_grs_power="";
		if(!isNaN(t_grs_vol)) s_grs_vol=""+(t_grs_vol/c).toFixed(3);
		if(!isNaN(t_grs_mass)) s_grs_mass=""+(t_grs_mass/c).toFixed(3);
		if(!isNaN(t_grs_energy)) s_grs_energy=""+(t_grs_energy/c).toFixed(3);
		if(!isNaN(t_grs_power)) s_grs_power=""+(t_grs_power/c).toFixed(3);
		
		if(isNaN(t_grs_vol) && isNaN(t_grs_mass) && isNaN(t_grs_energy) && isNaN(t_grs_power)){
			alert("Please input value to allocate plan");
			$("#t_grs_vol").focus();
			return;
		}

		if(t_grs_vol<0 || t_grs_mass < 0 || t_grs_energy<0 ||t_grs_power <0 ){
			alert("Please input value >= 0");
			return;
		}
// 		$("#tableData").html("");
		var dates="";
		d1 = $("#date_begin").datepicker( 'getDate' );
		var dx=d1.getFullYear() + '-' + (d1.getMonth() + 1) + '-' + d1.getDate();
		$("#f_date_from").val(dx);
		dx=d2.getFullYear() + '-' + (d2.getMonth() + 1) + '-' + d2.getDate();
		$("#f_date_to").val(dx);

		/* var postData = actions.loadedData['{{$key}}'];
		postData 	= typeof(postData) !== "undefined"?postData	:	actions.loadParams(true); */
		actions.editedData[tab] = [];
		var postData = actions.loadParams(true);
		actions.loadedData[tab] = postData;
		
		var source_type = postData.IntObjectTypeName;
		var prefix = getPreFix(source_type);
		actions.deleteData[tab] = {source_type	: source_type};
		
		var properties =  [{
		 	"data": "OCCUR_DATE",
		 	"title": "Occur Date",
		 	"width": 100,
		 	"INPUT_TYPE": 3,
		 	"DATA_METHOD": 2,
		 	"FIELD_ORDER": 1
		 }, {
		 	"data": prefix+"GRS_VOL",
		 	"title": "Gross Vol",
		 	"width": 125,
		 	"INPUT_TYPE": 2,
		 	"DATA_METHOD": 1,
		 	"FIELD_ORDER": 2
		 }, {
		 	"data": prefix+"GRS_MASS",
		 	"title": "Gross Mass",
		 	"width": 125,
		 	"INPUT_TYPE": 2,
		 	"DATA_METHOD": 1,
		 	"FIELD_ORDER": 3
		 }, {
		 	"data": prefix+"GRS_ENGY",
		 	"title": "Gross Energy",
		 	"width": 125,
		 	"INPUT_TYPE": 2,
		 	"DATA_METHOD": 1,
		 	"FIELD_ORDER": 4
		 }, {
		 	"data": prefix+"GRS_PWR",
		 	"title": "Gross Power",
		 	"width": 125,
		 	"INPUT_TYPE": 2,
		 	"DATA_METHOD": 1,
		 	"FIELD_ORDER": 5
		 }];

		var tableData = {
							properties	: 	properties,
							postData	:	postData
						};
		var dataSet	=	[];
		if(df=="d"){
			for (var d = d1; d <= d2; d.setDate(d.getDate() + 1)) {
				dx=d.getFullYear() + '-' + zeroFill((d.getMonth() + 1),2) + '-' + zeroFill(d.getDate(),2);
				getRowData(dx,prefix,postData,source_type);
				dataSet.push(row);
			}
		}
		else if(df=="w"){
			for (var d = d1; d <= d2; d.setDate(d.getDate() + 7)) {
				dx=d.getFullYear() + '-' + zeroFill((d.getMonth() + 1),2) + '-' + zeroFill(d.getDate(),2);
				getRowData(dx,prefix,postData,source_type);
				dataSet.push(row);
			}
		}
		else if(df=="m"){
			for (var d = d1; d <= d2; d.setMonth(d.getMonth() + 1)) {
				dx=d.getFullYear() + '-' + zeroFill((d.getMonth() + 1),2) + '-' + zeroFill(d.getDate(),2);
				getRowData(dx,prefix,postData,source_type);
				dataSet.push(row);
			}
		}
		tableData["dataSet"] = dataSet;
		actions.loadSuccess(tableData);
	}
</script>
@stop

@section('content')
	<div id="container_{{$key}}">
		<table border="0" id="table_{{$key}}" class="fixedtable nowrap display" cellspacing="0">
			<thead>
				<tr id="_rh" style="background:#E6E6E6;" role="row">
					<th rowspan="1" colspan="1" style="position: relative; left: 0px; background-color: rgb(230, 230, 230);"><b>Occur Date</b>	</th>
					<th rowspan="1" colspan="1"><b>Gross Vol</b>	</th>
					<th rowspan="1" colspan="1"><b>Gross Mass</b>	</th>
					<th rowspan="1" colspan="1"><b>Gross Energy</b>	</th>
					<th rowspan="1" colspan="1"><b>Gross Power</b>	</th>
				</tr>
				<tr style="background:#E6E6E6;height:40px" role="row">
					<th style="position: relative; left: 0px; background-color: rgb(230, 230, 230);" rowspan="1" colspan="1"></th>
					<th rowspan="1" colspan="1"><input type="number" id="t_grs_vol" class="_numeric" style="width:100%;background:#ffff88">	</th>
					<th rowspan="1" colspan="1"><input type="number" id="t_grs_mass" class="_numeric" style="width:100%;background:#ffff88">	</th>
					<th rowspan="1" colspan="1"><input type="number" id="t_grs_energy" class="_numeric" style="width:100%;background:#ffff88"></th>
					<th rowspan="1" colspan="1"><input type="number" id="t_grs_power" class="_numeric" style="width:100%;background:#ffff88">	</th>
				</tr>
				<tr style="background:#E6E6E6;height:40px;display:none">
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
			</thead>
		</table>
	</div>
	<div>
		<table border="0" id="table_{{$key}}_action" class="fixedtable nowrap display" cellspacing="0">
			<thead>
				<tr id="_rh" style="background:#E6E6E6;" role="row">
					<th><b>Record Frequency</b></th>
					<th></th>
				</tr>
				<tr style="background:#E6E6E6;height:40px" role="row">
					<th><select id="cboDataFreq" style="width:100%;background:#ffff88"><option value="d">Daily</option><option value="w">Weekly</option><option value="m">Monthly</option></select></th>
					<th style="padding:3px;width:260">
						<input type="button" onClick="calculateAllocPlan()" style="width:80px;height:30px;" value="Calculate">
						<input type="button" onClick="deletePlan()" style="width:80px;height:30px;" value="Delete">
				<!-- <input type="button" onClick="save()" style="width:80px;height:30px;" value="Save">-->
					</th>
				</tr>
			</thead>
		</table>
	</div>
@stop