@extends('core.pm')
@section('adaptData')
@parent
<script>
	var index = 1000;
	var addingOptions ={keepColumns:[]};
	
	actions.afterDataTable = function (table,tab){
		$("#toolbar_"+tab).html('<button>Add</button>');
		$("#toolbar_"+tab).addClass('toolbarAction');
		$("#toolbar_"+tab+ " button").on( 'click', function () {
				var columns = table.settings()[0].aoColumns;
				var addingRow = jQuery.extend({}, table.rows(0).data()[0]);
				addingRow['DT_RowId'] = 'NEW_RECORD_DT_RowId_'+(index++);
				addingRow['ID'] = addingRow['DT_RowId'];
// 				var addingRow = {};
				$.each(columns, function( i, vl ) {
					if($.inArray(vl.data,addingOptions.keepColumns)<=-1){
						 addingRow[vl.data] = '';
					}
					else{
 						actions.putModifiedData(tab,vl.data,addingRow[vl.data],addingRow);
					}
		        });
// 				addingRow['notAttachedToList'] = true;
				table.row.add(addingRow).draw( false );

				var tbbody = $('#table_'+tab);
		 		tbbody.tableHeadFixer({"left" : 1,head: false,});
            });
	};
	
	actions.renderFirsColumn = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		var html = '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
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
		var tableId = table.attr('id');
	    var splits = tableId.split("_");
   		var tab = splits[1];
 		createdFirstCellColumnByTable(table,rowData,td,tab);
    };

	actions.dominoColumns = function(columnName,newValue,tab,rowData,collection,table,td){
		createdFirstCellColumnByTable(table,rowData,td,tab);
	}

	function createdFirstCellColumnByTable(table,rowData,td,tab){
		var id = rowData['DT_RowId'];
		var isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);

		var deleteFunction = function(){
			var r = table.fnGetPosition(td)[0];
		    var rowData = table.api().data()[ r];
   			var recordData = actions.deleteData;
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
		};
		$(td).find('#delete_row_'+id).click(deleteFunction);
		$('#delete_row_'+id).click(deleteFunction);

		var editFunction = function(){
			var r = table.fnGetPosition(td)[0];
		    var rowData = table.api().data()[ r];
			actions.editRow(id,rowData);
		};
		$(td).find('#edit_row_'+id).click(editFunction);
		$('#edit_row_'+id).click(editFunction);
	}
	
</script>
@stop


@section('floatWindow')
	@include('core.float_window')
@stop