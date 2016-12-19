<?php
	$currentSubmenu =	'/fp/choke';
	$key 			= 'choke';
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
<div class="action_filter" style="float:left;">
	<input type="button" value="New" id="buttonNewContrain" name="buttonNew" onClick="editBox.newConstrain()" style="width: 85px; height: 26px;clear:both;">
	<br/>
	<input type="button" value="Load" id="buttonLoadContrain" name="buttonLoad" onClick="editBox.loadConsList()" style="margin-top:5px;width: 85px; height: 26px;clear:both;">
	<br/>
	<input type="button" value="Save" id="buttonSaveContrain" name="buttonSave" onClick="editBox.saveConstrain()" style="margin-top:5px;width: 85px; height: 26px;clear:both;">
</div>
<div class="action_filter" style="clear:both;">
	<input type="button" value="Generate Diagram" id="buttonGenContrain" name="buttonGen" onClick="editBox.genDiagramOfTable()" style="top: -20px;position: relative;width: 255px; height: 26px;float:left;">
</div>

@stop

@section('first_filter')
<div id="container_{{$tableTab}}" class="date_filter" style="overflow-x: hidden;float:left;margin-right:10px">
	<table border="0" cellpadding="3" id="table_{{$tableTab}}"
		class="fixedtable nowrap display">
	</table>
</div>

@stop

@section('content')
	@include('choke.diagram')
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
	actions.saveUrl = "/choke/save";

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
		html += '<a id="item_edit_'+id+'" class="actionLink clickable">objects</a>';
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
		    	if(lis.length>0){
					var objects		= [];
					$.each(lis, function( index, li) {
						var span = $(li).find("span:first");
						objects.push(span.data());
					});
					rowData.OBJECTS = objects;
	 				editBox.closeEditWindow(true);
		    	}
		    	else alert("please add object!");
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

	var currentDiagram = null;
	editBox.initNewDiagram = function(){
		currentDiagram = {	
			ID			: 'NEW_RECORD_DT_RowId_'+(index++),
			CONFIG		: '[]',
			NAME		: '',
			YCAPTION	: 'Oil Limit'
		};
	}

	var oAfterDataTable	= actions.afterDataTable;
	actions.afterDataTable = function (table,tab){
		oAfterDataTable(table,tab);
		var diagramTitle			= $('<input type="text" style="width:300px;margin-bottom: 3px;" id="txtDiagramName" name="txtDiagramName" size="15" value="">');
		diagramTitle.val(currentDiagram!=null?currentDiagram.NAME:"");
		var contraintDiagramName 	= $('<div style="padding: 3px 0 0 0;"><b>Contraint Diagram Name </b></div>');
		diagramTitle.appendTo(contraintDiagramName);
		contraintDiagramName.css("float","right");
		contraintDiagramName.appendTo($("#toolbar_"+tab));
		$("#toolbar_"+tab).css("width","100%");
		var ycaptionButton	= $(".dataTables_scrollHeadInner table thead th.YCAPTION");
		ycaptionButton.addClass('clickable');
		ycaptionButton.editable({
		    type			: 'text',
		    title			: 'Enter caption',	
		    showbuttons		: false,
		});
		
	};

	$(document).ready(editBox.newConstrain);
	
</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = "/choke/filter";

	editBox.size = {
						height 	: 420,
						width 	: 950,
					};

	editBox.renderContrainTable = function (value,convertJson=true){
		var tableData = {
				dataSet		: convertJson?JSON.parse(value.CONFIG):value.CONFIG,
				properties	: editBox.buildTableProperties(value),
				postData	: {'{{config("constants.tabTable")}}'	: '{{$tableTab}}'},
// 				columnDefs	: [],
				};
		currentDiagram		= value;
		actions.loadSuccess(tableData);
	}
		
	editBox.loadConsList = function (){
 		success = function(data){
	    	$("#contrainList").html("");
	 		var dataSet = data.dataSet;
	 		var ul = $("<ul class='ListStyleNone'></ul>");
			$.each(dataSet, function( index, value) {
		    	var li 				= $("<li class='x_item'></li>");
				var span 			= $("<span></span>");
				var del				= $('<img valign="middle" class="xclose" src="/img/x.png">');
				span.appendTo(li);
				del.appendTo(li);

				del.click(function() {
					if(!confirm("Are you sure you want to delete this item?")) return;
					showWaiting();
					$.ajax({
						url			: actions.saveUrl,
						type		: "post",
						data		: {
											deleteData	: {
															{{$tableTab}}	: [value.ID]
															}
									},
						success		: function(data){
							hideWaiting();
							li.remove();
							console.log ( "delConstrain success ");
						},
						error		: function(data) {
							hideWaiting();
							console.log ( "delConstrain error "/*+JSON.stringify(data)*/);
							alert("delete Constrain error ");
						}
					});
				});
				
				span.data(value);
				span.click(function() {
					editBox.closeEditWindow(true);
					editBox.renderContrainTable(value);
					editBox.loadContrainValue();
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
			 		url 		: actions.loadUrl,
			 		viewId 		: 'contrainList',
			 		size		: {
										height 	: 300,
										width 	: 500,
									},
    	    	};
		$("#objectList").css('display','none');
		editBox.showDialog(option,success);
	    $("button[id=actionsavefilter]").remove();
	}


	editBox.updateCurrentContrain = function (convertJson){
		var table				= $('#table_{{$tableTab}}').DataTable();
		var rows				= table.data().toArray();
		$.each(rows, function( index, row) {
			row.FACTOR			= row.FACTOR.replace(',','.');
			row.DT_RowId		= Math.random().toString(36).substring(10);
		});
		currentDiagram.CONFIG	= convertJson?JSON.stringify(rows):rows;
		currentDiagram.NAME		= $("#txtDiagramName").val();
		currentDiagram.YCAPTION	= $(".dataTables_scrollHeadInner table thead th.YCAPTION:first").text();
	}
						
	editBox.saveConstrain = function (){
		editBox.updateCurrentContrain(true);
		var saveData	= {
							editedData	: {
											{{$tableTab}}	: [currentDiagram]
										}
						};
		showWaiting();
		$.ajax({
			url			: actions.saveUrl,
			type		: "post",
			data		: saveData,
			success		: function(data){
				hideWaiting();
				console.log ( "saveConstrain success ");
				editBox.renderContrainTable(data.updatedData.ConstraintDiagram[0]);
// 				alert("save successfully ");
			},
			error		: function(data) {
				hideWaiting();
				console.log ( "saveConstrain error "/*+JSON.stringify(data)*/);
				alert("saveConstrain error ");
			}
		});
	}	
	
	editBox.newConstrain = function (){
		editBox.initNewDiagram();
		editBox.renderContrainTable(currentDiagram);
	}

	editBox.genDiagramOfTable = function (){
		editBox.loadContrainValue();
	}

	editBox.loadContrainValue	= function (){
		editBox.updateCurrentContrain(false);
		var constraintPostData 			= {	
											date_begin	: $("#date_begin").val(),
											date_end	: $("#date_end").val(),
 											constraints	: currentDiagram,
// 											constraintId	: 9,
											};
		editBox.requestGenDiagram(constraintPostData,false,true,function(data){
			editBox.renderContrainTable(data.constraints,false);
		});
	}
	
 	editBox.buildTableProperties = function (constrain){
 	 	var first		= {};
 	 	first.width		= 80;
 	 	first.title		= "";
 	 	first.data		= "ID";
 		var properties 	= [
							first,
 	 		  				{	'data' 		: 'NAME',
 	 		  					'title' 	: 'Summary Items'  ,
 	 		  					'width'		: 90,
 	 		  					'INPUT_TYPE': 1,
 	 		  					DATA_METHOD	: 1
 	 		  				},
 	 		  				{	'data' 		: 'GROUP',
 	 		  					'title' 	: 'Group'  ,
 	 		  					'width'		: 40,
 	 		  					'INPUT_TYPE': 1,
 	 		  					DATA_METHOD	: 1
 	 		  				},	 	
 	 		  				{	'data' 		: 'COLOR',
 	 		  					'title' 	: 'Color'  ,
 	 		  					'width'		: 30,
 	 		  					'INPUT_TYPE': 'color',
 	 		  					DATA_METHOD	: 1
 	 		  				},
 	 		  				{	'data' 		: 'VALUE',
 	 		  					'title' 	: 'Value'  ,
 	 		  					'width'		: 40,
 	 		  					'INPUT_TYPE': 2,
 	 		  				},
 	 		  				{	'data' 		: 'FACTOR',
 	 		  					'title' 	: 'Factor'  ,
 	 		  					'width'		: 30,
 	 		  					'INPUT_TYPE': 2,
 	 		  					DATA_METHOD	: 1
 	 		  				},
 	 		  				{	'data' 		: 'YCAPTION',
 	 		  					'title' 	: constrain.YCAPTION,
 	 		  					'width'		: 80,
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


