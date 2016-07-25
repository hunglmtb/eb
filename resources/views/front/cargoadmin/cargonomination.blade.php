<?php
	$currentSubmenu ='cargonomination';
	$tables = ['PdCargoNomination'	=>['name'=>'Data Input']];
	$isAction = true;
?>

@extends('core.pd')
@section('funtionName')
CARGO NOMINATION
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargonomination/load";
	actions.saveUrl = "/cargonomination/save";
	actions.type = {
			idName:['ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};
	addingOptions.keepColumns = ['STORAGE_ID','REQUEST_UOM','PRIORITY','QUANTITY_TYPE','LIFTING_ACCT','CONTRACT_ID'];

	var renderFirsColumn = actions.renderFirsColumn;
	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var html = renderFirsColumn(data, type, rowData );
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
			if(typeof rowData['IS_NOMINATED'] !== "undefined" && rowData['IS_NOMINATED'] !== null && rowData['IS_NOMINATED'] > 0 ){
				html += "<img class='nominate' src='../img/tick.png' height=16>";
			}
			else{
				html += '<a id="edit_row_'+id+'" class="actionLink">Confirm</a>';
			}
		}
		return html;
	};
</script>
@stop


@section('editBoxParams')
@parent
<script>
	editBox.editRow = function (id,rowData){
		$.ajax({
			url: '/cargonomination/confirm',
			type: "post",
			data: {
					cargoId		: id,	
				},
			success:function(data){
				console.log ( "confirm success: "/*+JSON.stringify(data)*/);
				alert(data.message);
				if(data.code=='NOT_EXIST'){
					//REMOVE ROW
					table = $('#table_PdCargo').DataTable();
					row = table.row( '#'+id);
					row.remove().draw(false);
				}
				else if(data.code!='ERROR'){
					rowData.IS_NOMINATED = true;
					table = $('#table_PdCargo').DataTable();
					row = table.row( '#'+id);
					row.data(rowData).draw(false);
				}
			},
			error: function(data) {
				console.log ( "confirm error: "/*+JSON.stringify(data)*/);
				alert("ERROR");
			}
		});
    }
		
</script>
@stop

