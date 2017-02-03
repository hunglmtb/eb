<?php
	$currentSubmenu ='/dv/taskman';
	$tables = ['TmTask'	=>['name'=>'Task'],
	];
	$isAction = true;
?>

@extends('core.pm')

@section('adaptData')
@parent
<script src="/common/edittable/event.js"></script>

<script>
	actions.loadUrl 		= "/taskman/load";
	actions.saveUrl 		= "/taskman/save";
	
	actions.type = {
					idName:['ID'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
					},
	};

	actions.extraDataSetColumns = {'task_code':'task_group'};
	
	source['task_group']	={	dependenceColumnName	:	['task_code'],
								url						: 	'/taskman/loadsrc'
								};

	var renderFirsColumn = actions.renderFirsColumn;
	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var html = renderFirsColumn(data, type, rowData );
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
			var command	= rowData.status;
			html += '<a onclick="actions.startJob('+id+',this)" class="actionLink">Start '+command+'</a>';
			html += '<a onclick="actions.stopJob('+id+',this)" class="actionLink">Stop</a>';
			html += '<a onclick="actions.checkJob('+id+',this)" class="actionLink">Check</a>';
		}
		return html;
	};

	actions.startJob  = function ( id, element ) {
		$(element).text("attemping");
        var table 		= $('#table_TmTask').DataTable();
		var row 		= table.row('#'+id);
		var rowData 	= row.data();
		$.ajax({
			url	: '/taskman/start/'+id,
			type: "post",
			data: {id	: id},
			success:function(data){
				console.log ( "attemp starting Job success with code "+data.CODE+" status code"+data.status+" status");
				rowData.status	= data.status;
				row.data(rowData).draw();
						
			},
			error: function(data) {
				console.log ( "attemp starting Job error");
				row.data(rowData).draw();
			}
		});
	};
	</script>
@stop

