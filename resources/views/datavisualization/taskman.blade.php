<?php
	use \App\Models\Network;
	use \App\Models\AllocJob;
	
	$currentSubmenu ='/dv/taskman';
	$tables = ['TmTask'	=>['name'=>'Task'],
	];
	$isAction = true;
	
	$network	= NETWORK::getTableName();
	$allocJob	= AllocJob::getTableName();
	$networks 	= Network::join($allocJob,"$network.ID", '=', "$allocJob.NETWORK_ID")->distinct("$network.ID")->select("$network.ID","$network.NAME")->get();
?>

@extends('core.pm')

@section('adaptData')
@parent
<script src="/common/edittable/event.js"></script>

<script>
	var networks = <?php echo json_encode($networks); ?>

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


	var firstTime = true;
	function onAfterGotDependences(elementId,element,currentId){
	   if(elementId=="AllocJob"){
		   if(firstTime) {
			   var originValue = element.attr("originValue");
			   element.val(originValue);
			   firstTime = false;
		   }
	   }
   	}
   	
	actions.configEventType = function (editable,columnName,cellData,rowData){
		if(columnName=="task_config") {
			cellData.networks 	= networks;
			editable.configType = "TASK";
			editable.tpl = '<table class="eventTable" style="width:inherit;min-width:350px"><tbody>'+
				 '<tr><td><label><span>Network</span></label></td><td colspan="1"><select id="Network" class="editable-event" name="NETWORK"></select></td></tr>'+
				 '<tr><td><label><span>Job</span></label></td><td colspan="1"><select id="AllocJob" class="editable-event" name="JOB"></select></td></tr>'+
	        	 '<tr class="DATE" ><td><label><span>Date</span></label></td><td colspan="1"><span class="editable-event clickable" name="DATE">set datetime</span></td></tr>'+
				 '<tr><td><label><span>Send Logs</span></label></td><td colspan="5"><input class="editable-event eventTaskInput" name="SENDLOG"></input></td></tr>'+
	            '</tbody></table>'+
	            "<script>registerOnChange('Network',['AllocJob'])<\/script>";
		}
	}

	oPreEditableShow = actions.preEditableShow;
	actions.preEditableShow  = function(){
		oPreEditableShow();
		firstTime = true;
	};

	actions.renderEventConfig = function( columnName,data2, type2, row){
		if(columnName=="task_config") {
			return typeof data2=="object"&&typeof data2.name != "undefined"? data2.name:"config";
		}
		else  if(columnName=="time_config") {
			return typeof data2=="object"&&typeof data2.FREQUENCEMODE != "undefined"? data2.FREQUENCEMODE:"config";
		}
	}
	
	</script>
@stop

