<?php
	$currentSubmenu ='/dc/ticket';
	$tables = [
			'RunTicketFdcValue'		=>['name'=>'TICKET FDC'],
			'RunTicketValue'		=>['name'=>'TICKET VALUE'],
	];
 	$active = 1;
 	$isAction = true;
?>

@extends('core.pm')
@section('funtionName')
RUN TICKET CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/ticket/load";
	actions.saveUrl 		= "/ticket/save";
	actions.historyUrl 		= "/ticket/history";
	
	actions.type = {
					idName:['TANK_ID','ID','FLOW_PHASE','OCCUR_DATE','TICKET_NO'],
					keyField:'ID',
					saveKeyField : function (model){
										return 'ID';
									},
					};
	actions.validating = function (reLoadParams){
		return true;
	}
	addingOptions.keepColumns = ['OCCUR_DATE','TICKET_NO','TICKET_TYPE'];

	actions['parseChartDate'] = function(datetime){
		date = moment.utc(datetime,configuration.time.DATETIME_FORMAT_UTC);
		y 		= date.year();
		m 		= date.month();
		d 		= date.date();
		hour 	= date.hour();
		minute 	= date.minute();
		day = Date.UTC(y,m,d,hour,minute);
		return {data	: day,
				display	: date.format(configuration.time.DATETIME_FORMAT)};
				};
	
</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.hidenFields = [{name:'Tank',field:'TANK_ID'}];
</script>
@stop