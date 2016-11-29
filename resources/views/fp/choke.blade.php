<?php
	$currentSubmenu =	'/fp/choke';
	$key 			= 	'choke';
 	$active 		= 0;
 	$isAction 		= true;
 	$floatContents 	= ['editBoxContentview','contrainList'];
 	$tableTab		= "ConstraintDiagram"
//  	$tables = ['ConstraintDiagram'	=>['name'=>'Constraint Diagram']];
 ?>

@extends('core.fp')
@section('funtionName')
Constrain diagrams
@stop

@section('action_extra')
<div class="action_filter">
	<input type="button" value="Load" id="buttonLoad" name="buttonLoad" onClick="editBox.loadConsList()" style="width: 85px; height: 26px;foat:left;">
</div>
@stop

@section('content')
<div id="container_{{$tableTab}}" style="overflow-x: hidden">
	<table border="0" cellpadding="3" id="table_{{$tableTab}}"
		class="fixedtable nowrap display">
	</table>
</div>
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/choke/load";
	actions.saveUrl = "/choke/run";

	actions.enableUpdateView = false;
	
	actions.getTableOption	= function(data){
		return {tableOption :	{searching		: false,
								ordering		: false,
								scrollY			: '350px',
								},
				invisible:[]};
		
	}
	
</script>
@stop


@section('editBoxParams')
@parent
<script>
// 	editBox.fields = ['deferment'];
	editBox.loadUrl = "/choke/editcontrains";
	/* editBox['size'] = {	height : 420,
						width : 900,
						}; */
	editBox.loadConsList = function (){
					 		success = function(data){
						    	$("#contrainList").html("");
						 		var dataSet = data.dataSet;
						 		var ul = $("<ul class='ListStyleNone'></ul>");
								$.each(dataSet, function( index, value) {
							    	var li 				= $("<li class='x_item'></li>");
									var span 			= $("<span></span>");
									var del				= $('<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png">');
									span.appendTo(li);
									del.appendTo(li);
									span.data(value);
									span.click(function() {
										editBox.closeEditWindow(true);
										var tableData = {
												'{{config("constants.tabTable")}}'	: '{{$tableTab}}',
												dataSet		: value.CONFIG,
												properties	: data.properties,
												postData	: data.postData,
												};
										actions.loadSuccess(tableData);
// 										editBox.editRow(span,span);
									});
									span.addClass("clickable");
									span.text(value.NAME);
									li.appendTo(ul);
									
								});
								ul.appendTo($("#contrainList"));
							}
					    	option = {
								    	title 		: "Plot Item list",
								 		postData 	: {tabTable : "{{$tableTab}}"},
								 		url 		: "/choke/load",
								 		viewId 		: 'contrainList',
					    	    	};

							editBox.showDialog(option,success);
						}
						
	var currentSpan = null;
	editBox.initExtraPostData = function (span,rowData){
	 						isFirstDisplay = false;
 							currentSpan = span;
 							return 	span.data();
 	};
 	isFirstDisplay = false;
 	editBox.editGroupSuccess = function(data,span){
 		$("#editBoxContentview").html(data);
 		filters.afterRenderingDependences("secondary_ObjectName");
 		filters.preOnchange("secondary_IntObjectType");
 		filters.preOnchange("secondary_ObjectDataSource");
 		isFirstDisplay = true;
 		if($("#secondary_IntObjectType").val()=="KEYSTORE") $("#secondary_ObjectDataSource").change();
	};

	editBox.editSelectedObjects = function (dataStore,resultText,x){
		if(currentSpan!=null) {
			currentSpan.data(dataStore);
			currentSpan.text(resultText);
			var li = currentSpan.closest( "li" );
			editBox.updateObjectAttributes(li,dataStore,x);
		}
	};

	editBox.renderOutputText = function (texts){
		return 	texts.ObjectName +"("+
				texts.IntObjectType+"."+
				texts.ObjectDataSource+"."+
				(texts.hasOwnProperty('CodeFlowPhase')? 		(texts["CodeFlowPhase"]+".")	:"")+
				(texts.hasOwnProperty('ObjectTypeProperty')? 	texts["ObjectTypeProperty"]		:"")+
				")";
	};

	editBox.addObjectItem 	= function (color,dataStore,texts,x){
		var li 				= $("<li class='x_item'></li>");
		var sel				= "<select class='x_chart_type' style='width:100px'><option value='line'>Line</option><option value='spline'>Curved line</option><option value='column'>Column</option><option value='area'>Area</option><option value='areaspline'>Curved Area</option><option value='pie'>Pie</option></select>";
		var inputColor 		= "<input type='text' maxlength='6' size='6' style='background:"+color+";color:"+color+";' class='_colorpicker' value='"+(color=="transparent"?"7e6de3":color.replace("#", ""))+"'>";
		var select			= $(sel);
		var colorSelect		= $(inputColor);
		var span 			= $("<span></span>");
		var del				= $('<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png">');

		if(dataStore.hasOwnProperty('chartType')) select.val(dataStore.chartType);
		select.appendTo(li);
		colorSelect.appendTo(li);
		span.appendTo(li);
		del.appendTo(li);
		
		currentSpan 		= span;
		span.click(function() {
			editBox.editRow(span,span);
		});
		span.addClass("clickable");
		var rstext 			= typeof texts =="string"? texts:editBox.renderOutputText(texts);
		editBox.editSelectedObjects(dataStore,rstext,x);
		
		li.appendTo($("#chartObjectContainer"));
		setColorPicker();
	}

	editBox.updateObjectAttributes = function (li,dataStore,x){
		if(typeof x !="string")
			x = editBox.getObjectValue(dataStore);
		li.attr("object_value",x);
	};

	editBox.getObjectValue = function (dataStore){
		var s3	="";
		var d0 	= dataStore.IntObjectType;
		if(d0=="ENERGY_UNIT") s3+=":"+dataStore.CodeFlowPhase;
		var x	= 	d0+":"+
					dataStore.ObjectName+":"+
					dataStore.ObjectDataSource+":"+
					dataStore.ObjectTypeProperty+
					s3+"~"+
					dataStore.CodeAllocType+"~"+
					dataStore.CodePlanType+"~"+
					dataStore.CodeForecastType;
		return x;
	};
	</script>
@stop


