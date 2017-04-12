<?php
	use \App\Models\Network;
	use \App\Models\AllocJob;
	use \App\Models\TmTask;
	use \App\Models\Facility;
	use \App\Models\TmWorkflow;
	use \App\Models\EnergyUnitGroup;
	use \App\Models\CodeAllocType;
	use \App\Models\CodePlanType;
	use \App\Models\CodeForecastType;
	use \App\Models\CodeProductType;
	use \App\Models\EnergyUnit;
	use \App\Models\CodeFlowPhase ;
	use \App\Models\CodeEventType ;
	use \App\Models\CodeReadingFrequency ;
	
	$currentSubmenu ='/dv/taskman';
	$tables = ['TmTask'	=>['name'=>'Task'],
	];
	$isAction = true;
	
	$facilities			= Facility::all();
	$taskStatus			= TmTask::loadStatus();
	$network			= NETWORK::getTableName();
	$allocJob			= AllocJob::getTableName();
	$networks 			= Network::join($allocJob,"$network.ID", '=', "$allocJob.NETWORK_ID")->distinct("$network.ID")->select("$network.ID","$network.NAME")->get();
	$tmWorkflows		= TmWorkflow::loadActive();
	$codeFlowPhase		= CodeFlowPhase::loadActive();
	$codeReadingFrequency= CodeReadingFrequency::loadActive();
	$codeEventType		= CodeEventType::loadActive();
	$energyUnitGroup	= EnergyUnitGroup::all();
	$codeAllocType		= CodeAllocType::loadActive();
	$codePlanType		= CodePlanType::loadActive();
	$codeForecastType	= CodeForecastType::loadActive();
	$codeProductType	= CodeProductType::loadActive();
	$energyUnit			= EnergyUnit::all();
?>

@extends('core.pm')

@section('adaptData')
@parent
<script src="/common/edittable/event.js"></script>

<script>
	var networks 			= <?php echo json_encode($networks); ?>;
	var taskStatus 			= <?php echo json_encode($taskStatus); ?>;
	var facilities 			= <?php echo json_encode($facilities); ?>;
	var tmWorkflows 		= <?php echo json_encode($tmWorkflows); ?>;
	var codeReadingFrequency= <?php echo json_encode($codeReadingFrequency		);		?>;
	var codeFlowPhase		= <?php echo json_encode($codeFlowPhase		);		?>;
	var codeEventType		= <?php echo json_encode($codeEventType		);		?>;
	var energyUnitGroup		= <?php echo json_encode($energyUnitGroup	);	?>;
	var codeAllocType		= <?php echo json_encode($codeAllocType		);	?>;
	var codePlanType		= <?php echo json_encode($codePlanType		);	?>;
	var codeForecastType	= <?php echo json_encode($codeForecastType	); ?>;
	var codeProductType		= <?php echo json_encode($codeProductType	);	?>;
	var energyUnit			= <?php echo json_encode($energyUnit		);		?>;
	
	actions.loadUrl 		= "/taskman/load";
	actions.saveUrl 		= "/taskman/save";
	
	actions.type = {
					idName:['ID'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
					},
	};
// 	actions.extraDataSetColumns = {'task_config':'task_code'};
	
// 	source['task_code']	={	dependenceColumnName	:	['task_config'],};

	/* actions.extraDataSetColumns = {'task_code':'task_group'};
	
	source['task_group']	={	dependenceColumnName	:	['task_code'],
								url						: 	'/taskman/loadsrc'
								}; */
								

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

	actions.dominoColumns = function(columnName,newValue,tab,rowData,collection,table,td){
		if(columnName=="task_code"){
			var dependence = 'task_config';
			var DT_RowId = rowData['DT_RowId'];
			var dependencetd = $('#'+DT_RowId+" ."+dependence);
			actions.applyEditable(tab,"EVENT",dependencetd, null, rowData, dependence);
		}
		actions.createdFirstCellColumnByTable(table,rowData,td,tab);
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

	actions.configEventType = function (editable,columnName,cellData,rowData){
		if(columnName=="task_config") {
			if(cellData!=null) {
				cellData.networks 				= networks;
				cellData.facilities 			= facilities;
				cellData.tmWorkflows 			= tmWorkflows;
				cellData.codeFlowPhase			= codeFlowPhase;
				cellData.codeEventType			= codeEventType;
				cellData.energyUnitGroup		= energyUnitGroup	;
				cellData.codeAllocType	    	= codeAllocType	;    
				cellData.codePlanType	    	= codePlanType	;    
				cellData.codeForecastType   	= codeForecastType;   
				cellData.codeProductType		= codeProductType	;
				cellData.energyUnit		    	= energyUnit		;    
				cellData.codeReadingFrequency 	= codeReadingFrequency		;    
				
			}
			editable.configType = rowData.task_code;
			editable.placement 	= "bottom";
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

