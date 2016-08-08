<?php
	$currentSubmenu ='/pd/contractdata';
	$tables = ['PdContract'	=>['name'=>'Load']];
	$isAction = true;
?>

@extends('core.pd')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/contractdata/load";
	actions.saveUrl = "/contractdata/save";
	actions.type = {
			idName:['ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};

	actions.renderFirsColumn = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		var html = '';
		if(isAdding)
			html = '<a class="actionLink">Select</a>';
		else 
			html += '<a id="edit_row_'+id+'" class="actionLink">Select</a>';
		return html;
	};
	
	addingOptions.keepColumns = ['CONTRACT_TEMPLATE','CONTRACT_TYPE','CONTRACT_PERIOD','CONTRACT_EXPENDITURE'];

	editBox.initExtraPostData = function (id,rowData){
	 		return 	{
		 		id			: id,
		 		templateId	: rowData.CONTRACT_TEMPLATE};
	 	}

	actions.renderFirsEditColumn = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		var html = '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
		return html;
	};

</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.fields = ['PdContractData'];
	editBox.loadUrl = "/contractdetail/load";
	editBox.saveUrl = '/contractdetail/save';
	editBox.enableRefresh = true;

	editBox.editGroupSuccess = function(data,id){
		tab = 'PdContractData';
			options = {
	 					tableOption :	{
			 									autoWidth	: false,
 			 									scrollX		: false,
			 									scrollY		: "200px",
			 							}
				};
		subData = data[tab];
		etbl = renderTable(tab,subData,options,actions.createdFirstCellColumn);
		if(etbl!=null) actions.afterDataTable(etbl,tab);
	}

</script>
@stop

@section('editBoxContentview')
@parent
	<table border='0' cellpadding='0' style='width:100%;height:100%'>
		<tr>
			<td valign='top'>
				<div id="table_PdContractData_containerdiv" style='height:100%;overflow:auto'>
					<table id="table_PdContractData" class="fixedtable nowrap display"></table>
				</div>
			</td>
		</tr>
	</table>
@stop
