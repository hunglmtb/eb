<?php
	$currentSubmenu ='tagsMapping';
	$tables = ['IntTagMapping'	=>['name'=>'tags mapping']];
 	$active = 0;
?>

@extends('core.sc_action')
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
	actions.extraDataSetColumns = {'OBJECT_ID':'OBJECT_ID',
									'TABLE_NAME':'TABLE_NAME',
									'COLUMN_NAME':'TABLE_NAME'
								};
	
	source['TABLE_NAME']	=	{	dependenceColumnName	:	['COLUMN_NAME'],
 									url						: 	'/tagsMapping/loadsrc'
								};

	addingOptions.keepColumns = ['TABLE_NAME','COLUMN_NAME'];

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

</script>
@stop
