<?php
	$currentSubmenu ='/pd/cargoload';
	$tables = ['PdCargoLoad'	=>['name'=>'Load']];
	$detailTableTab = 'TerminalTimesheetData';
	$attributeTableTab = 'PdCodeLoadActivity';
	
	$isAction = true;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargoload/load";
	actions.saveUrl = "/cargoload/save";
	actions['idNameOfDetail'] = ['PARENT_ID', 'ACTIVITY_ID','ID'];

	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="PdCargoLoad";
	};

	editBox['getEditTableColumns'] = function(tab){
		return [{title:'NAME',data:'NAME',width:300}];
	}
	
	parentId = 0;
	editBox['filterField'] = 'ACTIVITY_ID';
	
	editBox['addAttribute'] = function(addingRow,selectRow){
		addingRow['ACTIVITY_ID'] 		= selectRow.CODE;
		addingRow['PARENT_ID'] 			= parentId;
		return addingRow;
	};

	editBox.initExtraPostData = function (id,rowData){
										parentId = id;
								 		return 	{
									 			id			: id,
									 		};
								 	};

 	actions['initDeleteObject']  = function (tab,id, rowData) {
		 if(tab=='{{$detailTableTab}}') return {'ID':id, PARENT_ID : rowData.PARENT_ID};
		return {'ID':id};
	 };
</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = "/timesheet/load";
	editBox.saveUrl = '/timesheet/save';

</script>
@stop
