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
		$("#toolbar_"+tab).addClass('toolbarAction');
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

				var tbbody = $('#table_'+tab);
		 		tbbody.tableHeadFixer({"left" : 1,head: false,});
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
    		 		var tbbody = $('#table_'+tab);
    		 		tbbody.tableHeadFixer({"left" : 1,head: false,});
// 					$('#'+id).remove();
			    }
          });
	};
	actions.createdFirstCellColumn  = function (td, cellData, rowData, row, col) {
		var table =$(this).dataTable();
    	$(td).click(function(){
	    	var r = table.fnGetPosition(this)[0];
    		var rowData = table.api().data()[ r];
    		var tableId = table.attr('id');
    		var splits = tableId.split("_");
			var id = rowData['DT_RowId'];
			var isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
   			var recordData = actions.deleteData;
   			var tab = splits[1];
	   		if (!(tab in recordData)) {
	    		recordData[tab] = [];
	    	}
	    	//remove in postdata
        	var eData = recordData[tab];
        	if(isAdding) {
	    	var editedData = actions.editedData[tab];
	    	if(editedData!=null){
	        		var result = $.grep(editedData, function(e){ 
	               	 return e[actions.type.keyField] == rowData[actions.type.keyField];
	                });
			    if (result.length > 0) {
//					    	result[0]['deleted'] = true;
			    	editedData.splice( $.inArray(result[0], editedData), 1 );
			    }
	    	}
		   	}
        	else{
		    	eData.push({'ID':id});
        	}
	        	//remove on table
    		table.api().rows( r).remove().draw( false );
		});
    };
	
</script>
@stop