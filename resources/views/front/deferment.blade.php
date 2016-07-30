<?php
	$currentSubmenu ='/dc/deferment';
	$tables = ['Deferment'	=>['name'=>'DEFERMENT']];
 	$active = 0;
?>

@extends('core.action')
@section('funtionName')
DEFERMENT DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/deferment/load";
	actions.saveUrl 		= "/deferment/save";
	actions.historyUrl 		= "/deferment/history";
	actions.type = {
					idName:['ID'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
						},
					};
	actions.extraDataSetColumns = {'DEFER_TARGET':'DEFER_GROUP_TYPE','CODE2':'CODE1','CODE3':'CODE2'};
	
	source['DEFER_GROUP_TYPE']	={	dependenceColumnName	:	['DEFER_TARGET'],
									url						: 	'/deferment/loadsrc'
								};
	source['CODE1']	={	dependenceColumnName	:	['CODE2','CODE3'],
						url						: 	'/deferment/loadsrc'
		};
	source['CODE2']	={	dependenceColumnName	:	['CODE3'],
						url						: 	'/deferment/loadsrc'
	};

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

	var renderFirsColumn = actions.renderFirsColumn;
	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var html = renderFirsColumn(data, type, rowData );
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
			html += '<a id="edit_row_'+id+'" class="actionLink">Edit</a>';
		}
		return html;
	};

</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.fields = ['deferment'];
	editBox.loadUrl = "/deferment/edit";
	editBox.saveUrl = '/deferment/edit/saving';
	editBox.enableRefresh = true;
	
	editBox.editGroupSuccess = function(data,id){
		tab = 'deferment';
			options = {
	 					tableOption :	{
			 									searching: false,
			 									autoWidth: false,
			 									scrollX: true,
			 									bInfo 		: false,
			 									scrollY		:	"320px",
			 								}
				};
		subData = data[tab];
		renderTable(tab,subData,options);
	}
</script>
@stop


@section('editBoxContentview')
@parent
<table border='0' cellpadding='0' style='width:100%;height:100%'>
			<caption style='background:gray;color:white;height:20px;font-size:10.5pt' id = 'cationEditGroup'></caption>
			<tr>
				<td valign='top'>
					<div id="table_deferment_containerdiv" class="secondaryTable" style='height:400px;width: 950px;overflow:auto'>
						<table id="table_deferment" class="fixedtable nowrap display"></table>
					</div>
				</td>
			</tr>
		</table>
@stop