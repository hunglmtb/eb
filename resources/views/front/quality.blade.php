<?php
	$currentSubmenu ='quality';
	$tables = ['QltyData'	=>['name'=>'QUALITY DATA']];
 	$active = 0;
?>

@extends('core.action')
@section('funtionName')
QUALITY DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/quality/load";
	actions.saveUrl = "/quality/save";
	actions.type = {
					idName:['ID'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
						},
					};
	actions.extraDataSetColumns = {'SRC_ID':'SRC_TYPE'};

	source['SRC_TYPE']={dependenceColumnName	:	['SRC_ID'],
						url						: 	'/quality/loadsrc'
						};
	
	source.initRequest = function(tab,columnName,newValue,collection){
		postData = actions.loadedData[tab];
		var srcType = null;
		var result = $.grep(collection, function(e){ 
          	 return e['ID'] == newValue;
           });
		if (result.length > 0) {
			srcType = result[0]['CODE'];
		}
		else return null;

		srcData = {name : columnName,
					value : newValue,
					srcType : srcType,
					Facility : postData['Facility']};
		return srcData;
	}

	addingOptions.keepColumns = ['SAMPLE_DATE','TEST_DATE','EFFECTIVE_DATE','PRODUCT_TYPE','SRC_ID','SRC_TYPE'];

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

	var renderFirsEditColumn = function ( data, type, rowData ) {
		return '<b>'+rowData.NAME+'</b>';
	};

	var renderTable = function (tab,subData,options) {
		if(subData==null){
			$('#table_'+tab+'_containerdiv').css("display", "none");
		}
		else{
			$('#table_'+tab+'_containerdiv').css("display", "block");
// 			$('#table_'+tab+'_containerdiv').html('<table id="table_'+tab+'" border="0" cellpadding="3" width="100%"></table>');
			etbl = actions.initTableOption(tab,subData,options,renderFirsEditColumn,null);
		}
	};

    var renderSumRow = function (api,columns){
    	$.each(columns, function( i, column ) {
            total = 0;
            $.each(api.columns(column).data()[0], function( index, value ) {
            	total += intVal(value);
			});
            // Update footer
            $( api.columns(column).footer() ).html(total.toFixed(3));
		});
    }

    var editId = false;
	actions['editRow'] = function (id,rowData){
		$('#tableEditGroup').html("<p> Loading...</p>");
		$('#tableEditGroup').css("display", "block");
		$('#contentview').css("display", "none");
		$('#divEditGroup').show("fast");
// 		$('#table_editrow').html("");
		$('#cationEditGroup').html(rowData.CODE);
		editDataPosting = {id:id};
		delete actions.editedData['editrowgas'];
		delete actions.editedData['editrowoil'];
		$.ajax({
			url: "/quality/edit",
			type: "post",
			data: editDataPosting,
			success:function(data){
// 				$('#tableEditGroup').html(JSON.stringify(data));
				editId = id;
				$('#tableEditGroup').css("display", "none");
				$('#contentview').css("display", "block");
				tab = 'editrowgas';
 				options = {
 		 					tableOption :	{
 	 		 									searching: false,
 	 		 									autoWidth: true,
 	 		 									bInfo 		: false,
 	 		 									scrollY		:	"320px",
 	 		 									footerCallback : function ( row, data3, start, end, display ) {
										            var api = this.api();
										            columns = [1,2,3];
										            renderSumRow(api,columns);
										        }
 	 		 								}
						};
				subData = data['MOLE_FACTION'];
				renderTable(tab,subData,options);

				tab = 'editrowoil';
				options = {
						tableOption :{	searching	: false,
										autoWidth	: true,
										bInfo 		: false,
		 								scrollY		:	"250px",
										footerCallback : function ( row, data3, start, end, display ) {
															var api = this.api();
												            columns = [1];
												            renderSumRow(api,columns);
											       		}
		        					}
				};
				subData = data['NONE_MOLE_FACTION'];
				renderTable(tab,subData,options);
				console.log ( "success:function editRow "+data );
			},
			error: function(data) {
				$('#tableEditGroup').html(JSON.stringify(data));
				console.log ( "error editRow "+JSON.stringify(data) );
			}
		});
	}

	var saveEditGroup = function() {
		if(editId&&editId!=null&&(actions.editedData.hasOwnProperty('editrowoil')||actions.editedData.hasOwnProperty('editrowgas'))){
			showWaiting();
			editData = {
						id:editId,
						oil: actions.editedData['editrowoil'],
						gas: actions.editedData['editrowgas'],
					};
			$.ajax({
				url: '/quality/edit/saving',
				type: "post",
				data: editData,
				success:function(data){
					console.log ( "success saveEditGroup "+JSON.stringify(data) );
					alert(JSON.stringify(data));
					hideWaiting();
					closeEditWindow();
				},
				error: function(data) {
					hideWaiting();
					console.log ( "error saveEditGroup ");
				}
			});
		}
		else{
			alert('data is empty');
		}
	}

</script>
@stop


@section('editBox')
<script type="text/javascript">
	editBox.fields = ['editrowgas','editrowoil'];
</script>
@stop