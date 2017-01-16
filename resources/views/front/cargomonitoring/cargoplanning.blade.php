<?php
	$currentSubmenu ='/pd/cargoplanning';
	$tables = ['PdCargo'	=>['name'=>'Data']];
	$isAction = false;
?>

@extends('core.pd')
@section('funtionName')
CARGO PLANNING
@stop

@section('adaptData')
@parent
<style>
#table_quality th div{width:100px;font-size:10pt}
#table_quality thead td{text-align:center}
#table_quality td {background:white}
#table_quality td {border:1px solid #aaaaaa;padding-left:5px;padding-right:5px;}
#table_quality th {border:0px solid #aaaaaa;padding-left:5px;padding-right:5px;}
#table_quality .group1_th {background:rgb(146,208,80)}
#table_quality .group1_td {background:rgb(216,228,188)}
#table_quality .group2_th {background:rgb(255,255,0)}
#table_quality .group2_td {background:rgb(255,255,153)}
#table_quality .group3_th {background:rgb(204,192,218)}
#table_quality .group3_td {background:rgb(221,213,231)}
#table_quality tbody td {text-align:right}
.td_highlight {color: #378de5;font-weight:bold;}
#table_quality .td_plan {text-align:center;font-weight:bold}
</style>
<script>
	$( document ).ready(function() {
		$("#mainContent").html("<form name='form_fdc' id='form_fdc'><div style='width:100%;overflow-x: auto;'><table border='0' cellpadding='2' cellspacing='2' id='table_quality' class='display compact'></table></div></form>");
	    var onChangeFunction = function() {
		    if($('#Storage option').size()>0 ) actions.doLoad(true);
	    };
	    $( "#Storage" ).change(onChangeFunction);
	});

	actions.loadUrl = "/cargoplanning/load";

	actions.type = {
			idName:['ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};
	actions.doLoad = function(){
		if($("#txt_balance").val()===""){
			$("#txt_balance").focus();
			alert("Please enter balance");
			return;
		}
		$("#buttonLoadData").hide();
		//if($('#dateFrom').val()!="" && $('#dateTo').val()!="" && $("#cboFacility").val()>0)
		{
			var formData = $("#form_fdc").serialize();
			if(!formData)
				formData = "";
			formData += (formData==""?"":"&")+"dateFrom="+$("#date_begin").val()+"&dateTo="+$("#date_end").val()+"&cboFacility="+$("#Facility").val()+"&cboStorage="+$("#Storage").val()+"&dateformat="+"--";
			/*
			formData['dateFrom'] = $("#date_begin").val();
			formData['dateTo'] = $("#date_end").val();
			formData['cboFacility'] = $("#Facility").val();
			formData['cboStorage'] = $("#Storage").val();
			formData['dateformat'] = '--';
			*/
			console.log(formData);
			$.post("/pd/cargoplanning_load.php",
				formData,
				function(data, status){
						$("#table_quality").html(data);
						$("#buttonLoadData").show();
				});
			/*
			postRequest("cargoplanning_load.php", formData,
				   function(data, status){
						$("#table_quality").html(data);
						$("#buttonLoadData").show();
				   });
			*/
		}
	}
</script>
@stop

