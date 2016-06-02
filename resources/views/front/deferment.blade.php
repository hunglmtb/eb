<?php
	$currentSubmenu ='deferment';
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
	actions.loadUrl = "/deferment/load";
	actions.saveUrl = "/deferment/save";
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

	var closeEditWindow = function() {
		$('#divEditGroup').hide('fast');
		delete actions.editedData['editrowgas'];
		delete actions.editedData['editrowoil'];
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
