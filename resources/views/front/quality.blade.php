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
	
	actions.dominoColumns = function(columnName,newValue,tab,rowData,collection,table,td){
		if(columnName=='SRC_TYPE') {
			postData = actions.loadedData[tab];
			var srcType = null;
			var result = $.grep(collection, function(e){ 
	          	 return e['ID'] == newValue;
	           });
			if (result.length > 0) {
				srcType = result[0]['CODE'];
			}
			else return;

			DT_RowId = rowData['DT_RowId'];
			srcData = {name : columnName,
						value : newValue,
						srcType : srcType,
						Facility : postData['Facility']};

			dependenceColumnName = 'SRC_ID';
			rowData[dependenceColumnName] = '';
			dependencetd = $('#'+DT_RowId+" ."+dependenceColumnName);
			dependencetd.editable("destroy");
			table.api().row( '#'+DT_RowId ).data(rowData);
			
			$.ajax({
				url: "/quality/loadsrc",
				type: "post",
				data: srcData,
				success:function(data){
	// 				rowData[]
					dataSet = data.dataSet;
// 					var table = $('#table_'+tab).DataTable();

// 					dependencetd = $('#'+DT_RowId+" ."+dependenceColumnName);
// 					dependencetd.editable("destroy");
					cellData=null;
					if(typeof(dataSet) !== "undefined"&&dataSet!=null){
						$.each(dataSet, function( index, value ) {
							if(value!=null){
								value['value']=value['ID'];
								value['text']=value['NAME'];
								cellData=cellData==null?value['ID']:cellData;
							}
						});
					}

					rowData[dependenceColumnName] = cellData;
 	 				actions.applyEditable(tab,'select',dependencetd, cellData, rowData, dependenceColumnName,dataSet);
// 					table.api().row( '#'+DT_RowId ).data(rowData);
					createdFirstCellColumnByTable(table,rowData,td,tab);
					console.log ( "success:function dominoColumns "+data );
				},
				error: function(data) {
					console.log ( "error dominoColumns "+data );
				}
			});
		}
		createdFirstCellColumnByTable(table,rowData,td,tab);
	};


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

	 // Remove the formatting to get integer data for summation
    var intVal = function ( i ) {
        return typeof i === 'string' ?
            i.replace(/[\$,]/g, '')*1 :
            typeof i === 'number' ?
                i : 0;
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


@section('floatWindow')
<div style="background:#eee;border:2px solid #666;display:none;position: fixed; width: 950px; height: 430px; z-index: 1; left:50%; margin-left:-450px; top:145px" id="divEditGroup">
	<div onClick="saveEditGroup()" style="cursor:pointer; position: absolute; right:72px;top:-27px;border:2px solid #666;background:#eee; width: 82px; height: 23px;line-height:23px; z-index: 1" id="layer1">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font size="2">Save</font></div>
	<div onClick="closeEditWindow()" style="cursor:pointer;position: absolute; right:-2px;top:-27px;border:2px solid #666;background:#eee; width: 75px; height: 23px;line-height:23px; z-index: 1" id="layer1">&nbsp;&nbsp;&nbsp;&nbsp;
		<font size="2">Close</font>
	</div>
	<div id="contentview" style="width:100%;height:100%">
		<table border='0' cellpadding='0' style='width:100%;height:100%'>
			<caption style='background:gray;color:white;height:20px;font-size:10.5pt' id = 'cationEditGroup'></caption>
			<tr>
				<td valign='top'>
					<div id="table_editrowoil_containerdiv" class="secondaryTable" style='height:400px;overflow:auto'>
						<table id="table_editrowoil" class="fixedtable nowrap display">
							<tfoot>
								<tr>
									<td style="text-align:left">Sum:</td>
									<td style="text-align:left"></td>
									<td style="text-align:left" colspan="1"></td>
								</tr>
							</tfoot>
						</table>
					</div>
				</td>
				<td valign='top' width="10">
					<div class="paddingOfTable" style='width:10px;overflow:auto'>
					</div>
				</td>
				<td valign='top'>
					<div id="table_editrowgas_containerdiv" class="secondaryTable" style='height:400px;overflow:auto'>
						<table id="table_editrowgas" class="fixedtable nowrap display">
						<tfoot>
							<tr>
								<td style="text-align:left">Sum:</td>
								<td style="text-align:left"></td>
								<td style="text-align:left"></td>
								<td style="text-align:left" colspan="3"></td>
							</tr>
						</tfoot>
					</table>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div id="tableEditGroup" style="width:100%;height:100%"></div>
</div>
@stop