<?php
	$detailTableTab = 'TerminalTimesheetData';
	$attributeTableTab = 'PdCodeLoadActivity';
	$isAction = true;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions['idNameOfDetail'] = ['PARENT_ID', 'ACTIVITY_ID','ID','IS_LOAD'];

	editBox['getEditTableColumns'] = function(tab){
		return [{title:'NAME',data:'NAME',width:305}];
	}
	
	parentId = 0;
	editBox['filterField'] = 'ACTIVITY_ID';
	
	editBox['addAttribute'] = function(addingRow,selectRow){
		addingRow['ACTIVITY_ID'] 		= selectRow.CODE;
		addingRow['PARENT_ID'] 			= parentId;
		addingRow['IS_LOAD'] 			= {{$isLoad}};
		return addingRow;
	};

	
	editBox.initExtraPostData = function (id,rowData){
										parentId = id;
								 		return 	{
									 			id			: id,
									 		};
								 	};

	oAfterTable = actions.afterDataTable;
	var currentBox;
	actions.afterDataTable = function (table,tab){
		 oAfterTable(table,tab);
		 if(tab='{{$detailTableTab}}'){
			var box = $("#toolbar_"+tab).find('#box_select_activity_set').first();
			box = box.length>0?box:$("#box_select_activity_set").clone();
			currentBox = box;
			 jQuery('<button/>', {
				    id: 'more_'+tab,
				    title: 'Load activity set',
				    text: 'Load activity set'
				}).on( 'click', function(e){
					if(box.is(":visible")){
						box.hide("slide", { direction: "down" }, 100);
					}
					else{
 						box.show("slide", { direction: "down" }, 300);
					}
				})
			.appendTo("#toolbar_"+tab);
			box.appendTo($("#toolbar_"+tab));
				
		 }
	};

	function setActivitySet(id){
		currentBox.hide("slide", { direction: "down" }, 100);
		showWaiting();
	    $.ajax({
			url: '/timesheet/activities',
			type: "post",
			data: {id:id},
			success:function(data){
				hideWaiting( "send  timesheet/activities success ");
				console.log ( "send  timesheet/activities success ");
				updatedData = data.updatedData['{{$detailTableTab}}'];
				otable = $('#table_{{$detailTableTab}}').DataTable();
				tableData =  otable.data();
				filterData = [];
				$.each(updatedData, function( index, value ) {
					filters = $.grep(tableData,function(element,index) {
					  	return element['ACTIVITY_ID']==value.ACTIVITY_ID;
					});
					
					if(filters.length<=0){
						value['PARENT_ID'] 			= parentId;
						value['START_TIME'] 		= '';
						value['END_TIME'] 			= '';
						value['COMMENT'] 			= '';
						value['IS_LOAD'] 			= {{$isLoad}};
						value['DT_RowId'] 			= 'NEW_RECORD_DT_RowId_'+(index++);
						value['ID'] 				= value['DT_RowId'];
						filterData.push(value);
					}
	            });
				data.updatedData['{{$detailTableTab}}'] = filterData;
				actions.saveSuccess(data);

				$.each(filterData, function( index, rowData ) {
					$('#'+rowData['DT_RowId']).effect("highlight", {}, 5000);
	            });
	            
			},
			error: function(data) {
				hideWaiting();
				console.log ( "timesheet/activities error: ");
			}
		});

	}

	editBox['saveFloatDialogSucess'] = function(data,id){
		actions.saveSuccess(data);
		otable = $('#table_{{$detailTableTab}}').DataTable();
		$.each(otable.data(), function( i, rowData ) {
			var id = rowData['DT_RowId'];
			if ((typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1)) {
				table.row($('#'+id)).remove().draw(false);
			}
		});
	}
								 	
</script>
@stop

@section('editBoxParams')
@parent
<script>
// 	editBox.loadUrl = "/timesheet/load";
	editBox.saveUrl = '/timesheet/save';
</script>
@stop
@section('floatMoreBox')
@parent
	@if(isset($activities))
		<div id="box_select_activity_set" style="display:none;border:1px solid #888;position:absolute;width:250px;bottom:44px;left:120px;background:white">
				<table border='0' style='width:100%' cellpadding='5' cellspacing='0'>
					@foreach($activities as $activity )
						<tr class='row_activity' style='cursor:pointer' onclick="setActivitySet({{$activity->SET_ID}})">
						<td>{{$activity->SET_NAME}}</td>
						</tr>
			 		@endforeach
				</table>
		</div>
	@endif
@stop


