<?php
	$currentSubmenu ='/tagsMapping';
	$tables = ['IntTagMapping'	=>['name'=>'Tags Mapping']];
 	$active = 0;
	$isAction = true;
 ?>

@extends('core.sc')
@section('funtionName')
TAG MAPPING CONFIG
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/tagsMapping/load";
	actions.saveUrl = "/tagsMapping/save";
	actions.type = {
					idName:['ID','OBJECT_TYPE'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
						},
					};
	actions.extraDataSetColumns = {
									'OBJECT_ID':'OBJECT_ID',
									'TABLE_NAME':'TABLE_NAME',
									'COLUMN_NAME':'TABLE_NAME'
								};
	
	source['TABLE_NAME']	=	{	dependenceColumnName	:	['COLUMN_NAME'],
 									url						: 	'/tagsMapping/loadsrc'
								};

	addingOptions.keepColumns = ['TABLE_NAME','COLUMN_NAME','OBJECT_TYPE'];

	source.initRequest = function(tab,columnName,newValue,collection){
		postData = actions.loadedData[tab];
		srcData = {	name : columnName,
					value : newValue,
					Facility : postData['Facility'],
 					target: source[columnName].dependenceColumnName,
// 					srcType : srcType,
				};
		return srcData;
	}

	actions['doMoreAddingRow'] = function(addingRow){
		if(typeof addingRow['OBJECT_TYPE'] == "undefined") addingRow['OBJECT_TYPE'] 	= $('#IntObjectType').val();
		addingRow['OBJECT_ID'] 		= $('#ObjectName').val();
		addingRow['SYSTEM_ID'] 		= 1;
		addingRow['FREQUENCY'] 		= 1;
		addingRow['FLOW_PHASE'] 	= 1;
		addingRow['EVENT_TYPE'] 	= 1;
		return addingRow;
	}
</script>
@stop
