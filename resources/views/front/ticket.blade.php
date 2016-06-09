<?php
	$currentSubmenu ='ticket';
	$tables = [
			'RunTicketFdcValue'		=>['name'=>'TICKET FDC'],
			'RunTicketValue'		=>['name'=>'TICKET VALUE'],
	];
 	$active = 1;
?>

@extends('core.action')
@section('funtionName')
RUN TICKET CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/ticket/load";
	actions.saveUrl = "/ticket/save";
	actions.type = {
					idName:['ID','FLOW_PHASE','TANK_ID','OCCUR_DATE','TICKET_NO'],
					keyField:'ID',
					saveKeyField : function (model){
										return 'ID';
									},
					};
	/* actions.afterDataTable  = function (table,tab){
		$("#toolbar_"+tab).html('');
	} */
	addingOptions.keepColumns = ['SAMPLE_DATE','TEST_DATE','EFFECTIVE_DATE','PRODUCT_TYPE','SRC_ID','SRC_TYPE'];
	
</script>
@stop