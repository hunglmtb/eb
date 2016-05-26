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
					idName:['{{config("constants.qualityId")}}','{{config("constants.flFlowPhase")}}'],
					keyField:'{{config("constants.qualityId")}}',
					saveKeyField : function (model){
						return '{{config("constants.qualityIdColumn")}}';
						},
					};
	actions.extraDataSetColumns = {'SRC_ID':'SRC_TYPE'};
	
	actions.dominoColumns = function(columnName,newValue,tab,rowData,collection){
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
	
			srcData = {name : columnName,
						value : newValue,
						srcType : srcType,
						Facility : postData['Facility']};
			$.ajax({
				url: "/quality/loadsrc",
				type: "post",
				data: srcData,
				success:function(data){
	// 				rowData[]
					dataSet = data.dataSet;
					var table = $('#table_'+tab).DataTable();
					dependenceColumnName = 'SRC_ID';
					colName = 'SRC_ID';

					td = $('#'+rowData['DT_RowId']+" ."+colName);
					td.editable("destroy");
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

 	 				actions.applyEditable(tab,'select',td, cellData, rowData, colName,dataSet);
					rowData[dependenceColumnName] = cellData;
					table.row( '#'+rowData['DT_RowId'] ).data(rowData);
					
					console.log ( "success:function dominoColumns "+data );
				},
				error: function(data) {
					console.log ( "error dominoColumns "+data );
				}
			});
		}
			
	};


	options.keepColumns = ['SRC_ID','SRC_TYPE'];

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

	actions['editRow'] = function (id,rowData){
		$('#tableEditGroup').html("<p> Loading...</p>");
		$('#tableEditGroup').css("display", "block");
		$('#table_editrow').css("display", "none");
		$('#divEditGroup').show("fast");
// 		$('#table_editrow').html("");
		$('#cationEditGroup').html(rowData.CODE);
		editDataPosting = {id:id};
		$.ajax({
			url: "/quality/edit",
			type: "post",
			data: editDataPosting,
			success:function(data){
// 				$('#tableEditGroup').html(JSON.stringify(data));
				$('#tableEditGroup').css("display", "none");
				$('#table_editrow').css("display", "block");
				tab = 'editrow';
				options = {tableOption :{searching: false},
							invisible:['VALUE','UOM']};
				etbl = actions.initTableOption(tab,data,options,renderFirsEditColumn,null);
				console.log ( "success:function editRow "+data );
			},
			error: function(data) {
				$('#tableEditGroup').html(JSON.stringify(data));
				console.log ( "error editRow "+JSON.stringify(data) );
			}
		});
	}

	
</script>
@stop


@section('floatWindow')
<div style="background:#eee;border:2px solid #666;display:none;position: fixed; width: 900px; height: 430px; z-index: 1; left:50%; margin-left:-450px; top:145px" id="divEditGroup">
	<div onClick="saveEditGroup()" style="cursor:pointer; position: absolute; right:72px;top:-27px;border:2px solid #666;background:#eee; width: 82px; height: 23px;line-height:23px; z-index: 1" id="layer1">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font size="2">Save</font></div>
	<div onClick="$('#divEditGroup').hide('fast')" style="cursor:pointer;position: absolute; right:-2px;top:-27px;border:2px solid #666;background:#eee; width: 75px; height: 23px;line-height:23px; z-index: 1" id="layer1">&nbsp;&nbsp;&nbsp;&nbsp;
		<font size="2">Close</font>
	</div>
	<table border='0' cellpadding='0' style='width:100%;height:100%'>
		<caption style='background:gray;color:white;height:20px;font-size:10.5pt' id = 'cationEditGroup'></caption>
		<tr>
			<td valign='top'>
				<div id="tableEditGroup" style="width:100%;height:100%"></div>
				<div style='height:400px;overflow:auto'></div>
			</td>
			<td valign='top'>
				<div style='height:400px;overflow:auto'>
					<table id="table_editrow"></table>
				</div>
			</td>
		</tr>
	</table>
</div>
@stop