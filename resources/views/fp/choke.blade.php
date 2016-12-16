<?php
	$currentSubmenu =	'/fp/choke';
	$key 			= 	'choke';
 	$active 		= 0;
 	$isAction 		= true;
 	$floatContents 	= ['editBoxContentview','contrainList'];
 	$tableTab		= "ConstraintDiagram";
 	$useFeatures	= [
 							['name'	=>	"filter_modify",
 							"data"	=>	["isFilterModify"	=> true,
 										"isAction"			=> $isAction]],
 	];
 	$filterGroups	= \Helper::getCommonGroupFilter();
 	if(isset($filterGroups['dateFilterGroup'])) unset($filterGroups['dateFilterGroup']);
//  	$tables = ['ConstraintDiagram'	:['name':'Constraint Diagram']];
 ?>

@extends('core.fp')

@section('funtionName')
Constrain diagrams
@stop

@section('script')
@parent
	<!-- <link rel="stylesheet" type="text/css" href="/common/tooltipster/css/tooltipster.bundle.min.css" />
    <script type="text/javascript" src="/common/tooltipster/js/tooltipster.bundle.min.js"></script> -->
    <link rel="stylesheet" media="screen" type="text/css" href="/common/colorpicker/css/colorpicker.css" />
	<script type="text/javascript" src="/common/colorpicker/js/colorpicker.js"></script>
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

@section('editBoxContentview')
	@include('choke.editfilter',['filters'			=> $filterGroups,
				    			'prefix'			=> "secondary_",
						    	])
@stop

@section('extra_editBoxContentview')
<div id="objectListContainer" style="overflow-x: hidden;z-index: 1001;position: relative;float: right;width:44%;height:100%">
	<div id="objectList" style="height:90%;width:100%;overflow-x: hidden;"></div>
</div>
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/choke/load";
	actions.saveUrl = "/choke/run";

	actions.type = {
			idName:['ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};
	
	actions.enableUpdateView = function(tab,postData){
		return false;
	};
	
	actions.getTableOption	= function(data){
		return {tableOption :	{searching		: false,
								ordering		: true,
								scrollY			: false,
								"info"			: false,
								},
				invisible:[]};
		
	}

	actions.tableIsDragable	= function(tab){
		return true;
	}

	addingOptions.keepColumns = ['GROUP','COLOR'];

	var renderFirsColumn = actions.renderFirsColumn;
	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var html = renderFirsColumn(data, type, rowData );
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		html += '<a id="item_edit_'+id+'" class="actionLink">objects</a>';
		return html;
	};

	var addMoreHandle	= function ( table,rowData,td,tab) {
		var id = rowData['DT_RowId'];
		var moreFunction = function(e){
		    var list = editBox.renderObjectsList(rowData.OBJECTS);
		    $("#objectList").html("");
		    $("#objectList").addClass("product_filter");
		    $("#editBoxContentview").css("float","left");
		    $("#editBoxContentview").css("width","54%");
		    list.appendTo($("#objectList"));

		    $("button[id=actionsavefilter]").remove();
		    var actionsBtn = $("<button id ='actionsavefilter' class='myButton' style='width: 61px;float:right'>Save</button>");
		    actionsBtn.click(function() {
		    	var lis			= $("#objectList ul:first li");
				var objects		= [];
				$.each(lis, function( index, li) {
					var span = $(li).find("span:first");
					objects.push(span.data());
				});
				rowData.OBJECTS = objects;
 				editBox.closeEditWindow(true);
			});
		    actionsBtn.appendTo($("#objectListContainer"));
		    $("#floatBox").dialog( {
				editId	: "editBoxContentview",
				height	: editBox.size.height,
				width	: editBox.size.width,
				position: { my: 'top', at: 'top+150' },
				modal	: true,
				title	: "Edit Summary Item",
				close	: function(event) {
							$("#objectList").css('display','none');
							$("button[id=actionsavefilter]").css('display','none');
						    $("button[id=actionsavefilter]").remove();
					   	 },
		   	 	open	: function( event, ui ) {
							$("#objectList").css('display','block');
// 						    $("#actionsavefilter").css('display','block');
						},
			});
			$("#box_loading").css("display","none");
		    $("#editBoxContentview").show();
		    $("#contrainList").hide();
		    editBox.renderFilter();
		    currentSpan = null;
		};
		table.$('#item_edit_'+id).click(moreFunction);
	};
	actions['addMoreHandle']  = addMoreHandle;

	var obuildFilterText = editBox.buildFilterText;
	editBox.buildFilterText = function(){
		 	var resultText 		= obuildFilterText();
			var	operationVal	= $("#txtConstant").val();
			var pvalue 			= parseFloat(operationVal);
			pvalue 				= isNaN(pvalue)? 0:pvalue;
			if(pvalue!=0){
				var	operation	= $("#cboOperation").val();
				var extraText	= operation!=null&&operationVal!=""&&operation!=""?""+operation+operationVal:"";
				resultText		+= extraText;
			}
			return resultText;
		}


	var currentDiagram = {	
							CONFIG		: [],
							NAME		: '',
							YCAPTION	: 'Oil Limit'
						};
	var oAfterDataTable	= actions.afterDataTable;
	actions.afterDataTable = function (table,tab){
		oAfterDataTable(table,tab);
		var diagramTitle			= $('<input type="text" style="width:300px" id="txtDiagramName" name="txtDiagramName" size="15" value="">');
		diagramTitle.val(currentDiagram!=null?currentDiagram.NAME:"");
		var contraintDiagramName 	= $('<div><b>Contraint Diagram Name </b></div>');
		diagramTitle.appendTo(contraintDiagramName);
		contraintDiagramName.css("float","right");
		contraintDiagramName.appendTo($("#toolbar_"+tab));
		$("#toolbar_"+tab).css("width","100%");
	};

	$(document).ready(function () {
		var cfirstColumn = {data	: '{{$tableTab}}'};
		var cproperties = editBox.buildTableProperties(currentDiagram,[cfirstColumn]);
		var tableData = {
				'{{config("constants.tabTable")}}'	: '{{$tableTab}}',
				dataSet		: currentDiagram.CONFIG,
				properties	: cproperties,
				postData	: {'{{config("constants.tabTable")}}' : "{{$tableTab}}"},
				};
		actions.loadSuccess(tableData);
	});
	
</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = "/choke/filter";

	editBox.size = {
						height 	: 470,
						width 	: 950,
					};
	
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
												properties	: editBox.buildTableProperties(value,data.properties),
												postData	: data.postData,
												};
										currentDiagram		= value;
										actions.loadSuccess(tableData);
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
							$("#objectList").css('display','none');
							editBox.showDialog(option,success);
						    $("button[id=actionsavefilter]").remove();
						}
						
	editBox.isNotSaveGotData = function (url,viewId){
		
		return viewId=="contrainList"?editBox.gotData==false:true;
	}					
	
 	editBox.buildTableProperties = function (constrain,column1){
 	 	var first		= column1[0];
 	 	first.width		= 100;
 	 	first.title		= "";
 		var properties 	= [
							first,
 	 		  				{	'data' 		: 'NAME',
 	 		  					'title' 	: 'Summary Items'  ,
 	 		  					'width'		: 100,
 	 		  					'INPUT_TYPE': 1,
 	 		  					DATA_METHOD	: 1
 	 		  				},
 	 		  				{	'data' 		: 'GROUP',
 	 		  					'title' 	: 'Group'  ,
 	 		  					'width'		: 50,
 	 		  					'INPUT_TYPE': 1,
 	 		  					DATA_METHOD	: 1
 	 		  				},	 	
 	 		  				{	'data' 		: 'COLOR',
 	 		  					'title' 	: 'Color'  ,
 	 		  					'width'		: 40,
 	 		  					'INPUT_TYPE': 'color',
 	 		  					DATA_METHOD	: 1
 	 		  				},
 	 		  				{	'data' 		: 'VALUE',
 	 		  					'title' 	: 'Value'  ,
 	 		  					'width'		: 60,
 	 		  					'INPUT_TYPE': 2,
 	 		  				},
 	 		  				{	'data' 		: 'FACTOR',
 	 		  					'title' 	: 'Factor'  ,
 	 		  					'width'		: 60,
 	 		  					'INPUT_TYPE': 2,
 	 		  					DATA_METHOD	: 1
 	 		  				},
 	 		  				{	'data' 		: 'YCAPTION',
 	 		  					'title' 	: constrain.YCAPTION,
 	 		  					'width'		: 60,
 	 		  					'INPUT_TYPE': 2
 	 		  				},
 		  		];
 		return properties;
	};

 	editBox.renderObjectsList = function (objects){
 		var tooltipContent = $("<div>");
   		var ul = $("<ul class='ListStyleNone'></ul>");
   		ul.sortable();
   		if(typeof objects == "object"){
		  	$.each(objects, function( index, object) {
		  		editBox.add2ObjectList(object,ul);
			});
   		}
		ul.appendTo(tooltipContent);
		return 	tooltipContent;
	};

	var focusOnCurrentSpan = function (span){
		if(typeof currentSpan != 'undefined') $(currentSpan).css("color","");
		span.css("color","#830253");
		currentSpan		=	span;
	}
	
	editBox.add2ObjectList = function (object,ul){
		if(typeof object.text == 'undefined' || object.text =='')
			object.text		= editBox.renderOutputText(object);
		var text			= object.text;
	    var li 				= $("<li class='x_item'></li>");
		var span 			= $("<span></span>");
		var del				= $('<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png">');
		span.text(text);
		span.click(function() {
			if(currentSpan==span) return;
			focusOnCurrentSpan(span);
			editBox.editRow(span,span);
		});
		span.addClass("clickable");
		span.data(object);
		span.appendTo(li);
		del.appendTo(li);
		li.appendTo(ul);
		return span;
	};

	editBox.addObject 	= function (close){
		var object 		= editBox.buildFilterData();
		var ul 			= $("#objectList ul:first");
		var text 		= editBox.buildFilterText();
		object.text		= text;
		var span		= editBox.add2ObjectList(object,ul);
		focusOnCurrentSpan(span);
	}

	</script>
	
	<style>
	#table_ConstraintDiagram tbody th, #table_ConstraintDiagram tbody td {
		padding: 2px;
	}
	</style>
@stop


