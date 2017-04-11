<?php
	use \App\Models\Network;
	use \App\Models\AllocJob;
	use \App\Models\TmTask;
	
	$currentSubmenu ='/dv/taskman';
	$tables = ['TmTask'	=>['name'=>'Task'],
	];
	$isAction = true;
	
	$taskStatus	= TmTask::loadStatus();
	$network	= NETWORK::getTableName();
	$allocJob	= AllocJob::getTableName();
	$networks 	= Network::join($allocJob,"$network.ID", '=', "$allocJob.NETWORK_ID")->distinct("$network.ID")->select("$network.ID","$network.NAME")->get();
?>

@extends('core.pm')

@section('adaptData')
@parent
<script src="/common/edittable/event.js"></script>

<script>
	var networks 	= <?php echo json_encode($networks); ?>;
	var taskStatus 	= <?php echo json_encode($taskStatus); ?>;

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
		var id = rowData['DT_RowId'];
		var html = '<a id="delete_row_'+id+'" class="actionLink"><img alt="delete" title="Delete" src="/images/delete.png"></a>';
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
 			var status	= rowData.status;
 			switch(status){
 			case {{TmTask::STOPPED}}:
 			case {{TmTask::CANCELLING}}:
 			case '{{TmTask::STOPPED}}':
 			case '{{TmTask::CANCELLING}}':
				html += '<a onclick="actions.sendCommandJob('+id+',this,\'start\')" class="actionLink"><img alt="Run" title="Run" src="/images/run.png"></a>';
 	 			break;
 			case {{TmTask::STARTING}}:
 			case {{TmTask::READY}}:
 			case {{TmTask::RUNNING}}:
 			case '{{TmTask::STARTING}}':
 			case '{{TmTask::READY}}':
 			case '{{TmTask::RUNNING}}':
				html += '<a onclick="actions.sendCommandJob('+id+',this,\'stop\')" class="actionLink"><img alt="Stop" title="Stop" src="/images/stop.png"></a>';
 	 			break;
 			}
 			html += '<a onclick="actions.sendCommandJob('+id+',this,\'refresh\')" class="actionLink"><img alt="Stop" title="Stop" src="/ckeditor/skins/moono/images/refresh.png"></a>';
 			var commandText	= actions.getTaskStatus(status);
			html += commandText;
		}
		return html;
	};
	actions.getTaskStatus  = function ( command) {
		var result = $.grep(taskStatus, function(e){ 
       	 	return e["ID"] == command;
        });
	    if (result.length > 0) return result[0]["NAME"];
	    return command;
	}

	actions.sendCommandJob  = function ( id, element,command) {
		$(element).html('<a class="actionLink"><img alt="loading" title="loading" src="/ckeditor/skins/moono/images/spinner.gif"></a>');
        var table 		= $('#table_TmTask').DataTable();
		var row 		= table.row('#'+id);
		var rowData 	= row.data();
		$.ajax({
			url	: '/taskman/update/'+command+'/'+id,
			type: "post",
			data: {id	: id},
			success:function(data){
				console.log ( "attemp "+command+" Job success with code "+data.CODE+" status code"+data.status+" status");
				rowData.status	= data.task.status;
				rowData.command	= data.task.command;
				row.data(rowData).draw();
						
			},
			error: function(data) {
				console.log ( "attemp "+command+" Job error");
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
			if(cellData!=null) cellData.networks 	= networks;
			editable.configType = "TASK";
			editable.placement 	= "bottom";
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
		if(data2==null) return "config";
		if(columnName=="task_config") {
			return typeof data2=="object"&&typeof data2.name != "undefined"? data2.name:"config";
		}
		else  if(columnName=="time_config") {
			return typeof data2=="object"&&typeof data2.FREQUENCEMODE != "undefined"? data2.FREQUENCEMODE:"config";
		}
	}
	
	</script>
@stop

