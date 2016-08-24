var intVal = function ( i ) {
	    return typeof i === 'string' ?
	        i.replace(/[\$,]/g, '')*1 :
	        typeof i === 'number' ?
	            i : 0;
	};
	var index = 1000;
	var addingOptions ={keepColumns:[]};
	
	actions.isDisableAddingButton	= function (tab,table) {
		return false;
	};
	
	actions['getDefaultAddButtonHandler'] = function (table,tab,doMore){
		return function () {
			var columns = table.settings()[0].aoColumns;
			var sample = table.rows(0).data()[0];
			var addingRow = {};
			if(sample!=null){
				addingRow = jQuery.extend({}, sample);
			}
			addingRow['DT_RowId'] = 'NEW_RECORD_DT_RowId_'+(index++);
			addingRow['ID'] = addingRow['DT_RowId'];
//				var addingRow = {};
			$.each(columns, function( i, vl ) {
				if($.inArray(vl.data,addingOptions.keepColumns)<=-1){
					 addingRow[vl.data] = '';
				}
				else{
					if(sample==null){
						 addingRow[vl.data] = '';
					}
					else{
						actions.putModifiedData(tab,vl.data,addingRow[vl.data],addingRow);
					}
				}
	        });
			
			if(typeof(doMore) == "function"){
				addingRow = doMore(addingRow);
				$.each(columns, function( i, vl ) {
					actions.putModifiedData(tab,vl.data,addingRow[vl.data],addingRow);
		        });
			}
			
			if(typeof(editBox.hidenFields) !== "undefined"){
				$.each(editBox.hidenFields, function( i, vl ) {
					actions.putModifiedData(tab,vl.field,actions.loadedData[tab][vl.name],addingRow);
				});
			}
//				addingRow['notAttachedToList'] = true;
			table.row.add(addingRow).draw( false );

			var tbbody = $('#table_'+tab);
	 		tbbody.tableHeadFixer({"left" : 1,head: false,});
        }
	};
	
	actions.getAddButtonHandler = actions.getDefaultAddButtonHandler;

	actions.afterDataTable = function (table,tab){
		text = actions.isDisableAddingButton(tab,table)
		if(text==true) return;
		if(typeof(text) !== "string") text = 'Add';
		$("#toolbar_"+tab).html('<button class = "addButton">'+text+'</button>');
		$("#toolbar_"+tab).addClass('toolbarAction');
		addButtonHandle = actions.getAddButtonHandler(table,tab,actions['doMoreAddingRow']);
		$("#toolbar_"+tab+ " .addButton").on( 'click', addButtonHandle);
	};
	
	actions.renderFirsColumn = actions.deleteActionColumn;

	actions.afterGotSavedData = function (data,table,tab){
    	var editedData = actions.editedData[tab];
    	isAddingNewRow = false;
		if(typeof(editedData) !== "undefined"){
			$.each(editedData, function( i, rowData ) {
				var id = rowData['DT_RowId'];
				if ((typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1)) {
					table.row($('#'+id)).remove().draw(false);
					var tbbody = $('#table_'+tab);
					tbbody.tableHeadFixer({"left" : 1,head: false,});
					isAddingNewRow  = true;
				}
			});
		}
    	actions.addingNewRowSuccess(data,table,tab,isAddingNewRow);
	};
	actions.createdFirstCellColumn  = function (td, cellData, rowData, row, col) {
		$(td).css('z-index','1');
		var table =$(this).dataTable();
		var tableId = table.attr('id');
	    var splits = tableId.split("_");
   		var tab = splits[1];
 		createdFirstCellColumnByTable(table,rowData,td,tab);
    };
    
    actions['initDeleteObject']  = function (tab,id, rowData) {
    	return {'ID':id};
    };
    
	actions.dominoColumns = function(columnName,newValue,tab,rowData,collection,table,td){
// 		if(columnName=='SRC_TYPE') {
		if(columnName!=null&&source!=null&&source.hasOwnProperty(columnName)){
			if(typeof(source[columnName].url) !== "undefined"&&source[columnName].url != null){
				srcData = source.initRequest(tab,columnName,newValue,collection, rowData);
				if(srcData==null) return;
				
				DT_RowId = rowData['DT_RowId'];
				dependenceColumnNames = source[columnName].dependenceColumnName;
				$.each(dependenceColumnNames, function( i, dependence ) {
					rowData[dependence] = '';
					dependencetd = $('#'+DT_RowId+" ."+dependence);
					dependencetd.editable("destroy");
				});
				table.api().row( '#'+DT_RowId ).data(rowData);
				$.ajax({
					url: source[columnName].url,
					type: "post",
					data: srcData,
					success:function(data){
						console.log ( "success dominoColumns "+data );
						actions.dominoColumnSuccess(data,dependenceColumnNames,rowData,tab);
					},
					error: function(data) {
						console.log ( "error dominoColumns "+data );
					}
				});
			}
		}
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
        		deleteObject = actions.initDeleteObject(tab,id,rowData);
		    	eData.push(deleteObject);
        	}
	        	//remove on table
    		table.api().rows( r).remove().draw( false );
		};
		$(td).find('#delete_row_'+id).click(deleteFunction);
		$('#delete_row_'+id).click(deleteFunction);

		var editFunction = function(e){
			e.preventDefault();
			var r = table.fnGetPosition(td)[0];
		    var rowData = table.api().data()[ r];
		    editBox.editRow(id,rowData);
		};
//		$(td).find('#edit_row_'+id).click(editFunction);
		table.$('#edit_row_'+id).click(editFunction);
	}

	var renderTable = function (tab,subData,options,createdFirstCellColumnFunction) {
		if(subData==null){
			$('#table_'+tab+'_containerdiv').css("display", "none");
		}
		else{
			$('#table_'+tab+'_containerdiv').css("display", "block");
// 			$('#table_'+tab+'_containerdiv').html('<table id="table_'+tab+'" border="0" cellpadding="3" width="100%"></table>');
			createdFirstCellColumnFunction = typeof(createdFirstCellColumnFunction) == "function"?createdFirstCellColumnFunction:null;
			
			renderFirstColumn = typeof(options.tableOption.renderFirsColumn) != "undefined"?
								options.tableOption.renderFirsColumn:
									actions.renderFirsEditColumn;
			etbl = actions.initTableOption(tab,subData,options,renderFirstColumn,createdFirstCellColumnFunction);
			return etbl;
//			actions.afterDataTable(etbl,tab);
		}
		return null;
	};

	actions['renderFirsEditColumn'] = function ( data, type, rowData ) {
		return '<b>'+rowData.NAME+'</b>';
	};