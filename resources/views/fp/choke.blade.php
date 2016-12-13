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
 	
//  	$tables = ['ConstraintDiagram'	:['name':'Constraint Diagram']];
 ?>

@extends('core.fp')

@section('funtionName')
Constrain diagrams
@stop

@section('script')
@parent
	<link rel="stylesheet" type="text/css" href="/common/tooltipster/css/tooltipster.bundle.min.css" />
    <script type="text/javascript" src="/common/tooltipster/js/tooltipster.bundle.min.js"></script>
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

@section('extra_editBoxContentview')
<div id="objectList" style="overflow-x: hidden;z-index: 1001;position: relative;float: right;">
</div>
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/choke/load";
	actions.saveUrl = "/choke/run";

	actions.enableUpdateView = function(tab,postData){
		return false;
	};
	
	actions.getTableOption	= function(data){
		return {tableOption :	{searching		: false,
								ordering		: true,
								scrollY			: false,
								},
				invisible:[]};
		
	}

	actions.tableIsDragable	= function(tab){
		return true;
	}


	var renderFirsColumn = actions.renderFirsColumn;
	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var html = renderFirsColumn(data, type, rowData );
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		html += '<a id="item_edit_'+id+'" class="actionLink">objects</a>';
		return html;
	};

	var tooltipAddMoreHandle	= function ( table,rowData,td,tab) {
			var id = rowData['DT_RowId'];
    		var telement	 = table.$('#item_edit_'+id);
				
    		telement.tooltipster({
	 		  	    	content: function(){
	 		  	    			var tooltipContent = editBox.renderObjectsList(rowData.OBJECTS);
	 		  	    			var actionsButton = $("<div>");
	 		  	    			var cancel = $("<button>cancel</button>");
	 		  	    			cancel.click(function() {
	 		  	    				telement.tooltipster('close');
		 		  	    			});
	 		  					cancel.appendTo(actionsButton);

	 		  					var add = $("<button>add</button>");
	 		  					add.click(function() {
	 		  						editBox.editRow(add,add);
		 		  	    		});
	 		  					add.appendTo(actionsButton);

	 		  					var apply = $("<button>apply</button>");
	 		  					apply.click(function() {
	 		  	    				telement.tooltipster('close');
		 		  	    		});
	 		  					apply.appendTo(actionsButton);
		 		  	 			actionsButton.appendTo(tooltipContent);
		 		  				return tooltipContent;
		 		  	    	},
	 		  	   		// if you use a single element as content for several tooltips, set this option to true
	 		  	   		contentCloning: false,
	 		  	   		interactive : true,
	 		  	   		trigger: 'custom',
		 		  	 	triggerOpen: {
			 		        click: true,
			 		        tap: true,
			 		       	mouseenter: false
		 		    	},
		 		    	triggerClose: {
		 		    		mouseleave: false,
		 		           	originClick: true,
		 		           	touchleave: true,
		 		       },
		 		      zIndex	: 99
	 		  	});
		};
		
		var addMoreHandle	= function ( table,rowData,td,tab) {
			var id = rowData['DT_RowId'];
			var moreFunction = function(e){
			    var list = editBox.renderObjectsList(rowData.OBJECTS);
			    $("#objectList").html("");
			    $("#objectList").css("width","49%");
			    $("#objectList").css("height","95%");
			    $("#objectList").css("z-index","1001");
			    $("#objectList").addClass("product_filter");
			    
			    $("#editBoxContentview").css("float","left");
			    $("#editBoxContentview").css("width","48%");
			    list.appendTo($("#objectList"));
			    editBox.editRow(td,td);
			};
			table.$('#item_edit_'+id).click(moreFunction);
		};
		actions['addMoreHandle']  = addMoreHandle;
</script>
@stop

@section('editBoxParams')
@parent
<script>
// 	editBox.fields = ['deferment'];
// 	editBox.loadUrl = "/choke/editcontrains";
// 	var contrainListData = false;
	editBox.loadConsList = function (){
// 							if(contrainListData!=false) return;
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

							editBox.showDialog(option,success);
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
 	 		  					'INPUT_TYPE': 1
 	 		  				},
 	 		  				{	'data' 		: 'GROUP',
 	 		  					'title' 	: 'Group'  ,
 	 		  					'width'		: 50,
 	 		  					'INPUT_TYPE': 1
 	 		  				},	 	
 	 		  				{	'data' 		: 'COLOR',
 	 		  					'title' 	: 'Color'  ,
 	 		  					'width'		: 40,
 	 		  					'INPUT_TYPE': 1
 	 		  				},
 	 		  				{	'data' 		: 'VALUE',
 	 		  					'title' 	: 'Value'  ,
 	 		  					'width'		: 60,
 	 		  					'INPUT_TYPE': 2
 	 		  				},
 	 		  				{	'data' 		: 'FACTOR',
 	 		  					'title' 	: 'Factor'  ,
 	 		  					'width'		: 60,
 	 		  					'INPUT_TYPE': 2
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
	  	$.each(objects, function( index, object) {
		    var text = editBox.renderOutputText(object);
		    object.text		= text;
		    var li 				= $("<li class='x_item'></li>");
			var span 			= $("<span></span>");
			var del				= $('<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png">');
			span.text(text);
			span.click(function() {
				editBox.editRow(span,span);
			});
			span.addClass("clickable");
			span.appendTo(li);
			del.appendTo(li);
			li.appendTo(ul);
		});
		ul.appendTo(tooltipContent);
		return 	tooltipContent;
	};
	</script>
@stop


