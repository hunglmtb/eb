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
		var html = '<a id="edit_row_'+id+'" class="actionLink">Select</a>';
		return html;
	};

	/* actions.getTableOption	= function(data){
		return {tableOption :	{
									scrollY			: null,
								}
		};
	} */

	editBox.initExtraPostData = function (id,rowData){
	 		return 	{
		 		id			: id,
		 		templateId	: rowData.CONTRACT_TEMPLATE};
	 	}

</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.fields = ['PdContractData'];
	editBox.loadUrl = "/contractdetail/load";
	editBox.saveUrl = '/contractdetail/save';
	editBox.enableRefresh = true;

	editBox.preEditHandleAction = function(id,rowData){
// 		$('#table_PdContractData_containerdiv').html('<table id="table_PdContractData" class="fixedtable nowrap display"></table>');
	}
	
	editBox.editGroupSuccess = function(data,id){
		tab = 'PdContractData';
			options = {
	 					tableOption :	{
			 									searching	: false,
			 									autoWidth	: false,
// 			 									scrollX		: true,
			 									bInfo 		: false,
			 									scrollY		: "240px",
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
		<tr>
			<td valign='top'>
				<div id="table_PdContractData_containerdiv" style='height:100%;overflow:auto'>
					<table id="table_PdContractData" class="fixedtable nowrap display"></table>
				</div>
			</td>
		</tr>
	</table>
@stop
