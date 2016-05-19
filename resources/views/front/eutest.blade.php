<?php
	$currentSubmenu ='eutest';
	$tables = ['EuTestDataFdcValue'		=>['name'=>'FDC VALUE'],
			'EuTestDataStdValue'		=>['name'=>'STD VALUE'],
			'EuTestDataValue'			=>['name'=>'DAY VALUE'],
	];
 	$active = 1;
?>

@extends('core.pm')
@section('funtionName')
WELL TEST DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	var index = 1000;
	actions.loadUrl = "/eutest/load";
	actions.saveUrl = "/eutest/save";
	actions.type = {
					idName:['ID'],
					keyField:'ID',
					saveKeyField : function (model){
										return 'ID';
									},
					};

	actions.afterDataTable = function (table,tab){
		$("#toolbar_"+tab).html('<button>Add</button>');
		$("#toolbar_"+tab+ " button").on( 'click', function () {
				var columns = table.settings()[0].aoColumns;
				var addingRow = {};
				$.each(columns, function( i, vl ) {
					 addingRow[vl.data] = null;
		        });
				addingRow['DT_RowId'] = 'NEW_RECORD_DT_RowId_'+(index++);
				addingRow['ID'] = addingRow['DT_RowId'];
// 				addingRow['notAttachedToList'] = true;
				table.row.add(addingRow).draw( false );
            });
	};
	
	actions.renderFirsColumn = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		var html = '<a id="delete_row_'+id+'" style="color:gray">Delete</a>';
		return html;
	}

	actions.afterGotSavedData = function (data,table,tab){
    	var editedData = actions.editedData[tab];
    	 $.each(editedData, function( i, rowData ) {
    		 	var id = rowData['DT_RowId'];
    		 	if ((typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1)) {
    		 		table.row($('#'+id)).remove().draw(false);
// 					$('#'+id).remove();
			    }
          });
	};
	
</script>
@stop