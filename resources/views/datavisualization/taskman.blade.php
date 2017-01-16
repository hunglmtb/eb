<?php
	$currentSubmenu ='/dv/taskman';
	$tables = ['TmTask'	=>['name'=>'Task'],
	];
	$isAction = true;
?>

@extends('core.pm')

@section('adaptData')
@parent
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
	/* actions.validating = function (reLoadParams){
		return true;
	} */


	/* var aLoadParams = actions.loadParams;
	actions.loadParams = function(reLoadParams) {
		var pr = aLoadParams(reLoadParams);
		pr['CodePlanType']		= $('#CodePlanType').val();
		pr['CodeForecastType']	= $('#CodeForecastType').val();
		return pr;
	} */
</script>
@stop

