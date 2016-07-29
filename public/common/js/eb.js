//inline editable
//turn to inline mode
$.fn.editable.defaults.mode = 'inline';

var ebtoken = $('meta[name="_token"]').attr('content');
$.ajaxSetup({
	headers: {
		'X-XSRF-Token': ebtoken
	}
});
var enableSelect = function(dependentIds, value) {
	for (var i = 0; i < dependentIds.length; i++) {
		$('#'+dependentIds[i]).prop('disabled', value);
	}
};

var registerOnChange = function(id, dependentIds,more) {
	$('#'+id).change(function(e){
		enableSelect(dependentIds,'disabled');
		bundle = {};
		if (more!=null&&more.length>0) {
			$.each(more, function( i, value ) {
				bundle[value] = {};
//				bundle[value]['value'] = $("#"+value).val();
				bundle[value]['name'] = $("#"+value).find(":selected").attr( "name");
				bundle[value]['id'] = $("#"+value).val();
			});
		}
		/*extra = {};
		extra[id] = bundle;*/

		$.ajax({
			url: '/code/list',
			type: "post",
			data: {type:id,
				dependences:dependentIds,
				value:$(this).val(),
				extra:bundle
			},
			success: function(results){
				for (var i = 0; i < dependentIds.length; i++) {
					$('#'+dependentIds[i]).html('');   // clear the existing options
				}
				for (var i = 0; i < results.length; i++) {
					$(results[i].collection).each(function(){
						var option = $('<option />');
						option.attr('value', this.ID).text(this.NAME);
						$('#'+results[i].id).append(option);
					});
				}
				
				enableSelect(dependentIds,false);
			},
			error: function(data) {
				alert(data.responseText);
				enableSelect(dependentIds,false);
			}
		});
	});
};

var getActiveTabID = function() {
	var activeTabIdx = $("#tabs").tabs('option', 'active');
	var selector = '#tabs > ul > li';
	var activeTabID = $(selector).eq(activeTabIdx).attr('id');
	return activeTabID;
}


var typetoclass = function (data){
	switch(data){
		case 1:
		return "text";
		case 2:
			return "number";
		case 3:
			return "date";
		case 4:
			return "datetimepicker";
		case 5:
			return "checkbox";
		case 6:
			return "timepicker";
	}
	return "text";
};

var source = {
		initRequest	:	 function(tab,columnName,newValue,collection){
			postData = actions.loadedData[tab];
			srcData = {	name : columnName,
						value : newValue,
						};
			return srcData;
		}
	};

var actions = {
		
	loadUrl 			: false,
	saveUrl 			: false,
	historyUrl			: false,
	readyToLoad 		: false,
	loadedData 			: {},
	loadPostParams 		: null,
	initData 			: false,
	initSaveData 		: false,
	editedData 			: {},
	deleteData 			: {},
	objectIds 			: [],
	extraDataSetColumns : {},
	extraDataSet 		: {},
	loadSuccess 		: function(data){alert("success");},
	loadError 			: function(data){alert(JSON.stringify(data.responseText));},
	shouldLoad 			: function(data){return false;},
	addingNewRowSuccess	: function(data,table,tab,isAddingNewRow){},
	afterGotSavedData 	: function(data,table,key){},
	dominoColumns 		: function(columnName,newValue,tab,rowData,collection,td){},
	loadNeighbor		: function (){
							if (actions.shouldLoad()) {
								actions.doLoad(false);
							}
							else{
								var activeTabID = getActiveTabID();
								var postData = actions.loadedData[activeTabID];
								actions.updateView(postData);
							}
						},
	loadParams 			: function (reLoadParams){
							var params;
							if (reLoadParams) {
								params = {};
								for (var key in javascriptFilterGroups) {
									filterGroup = javascriptFilterGroups[key];
									for (var jkey in filterGroup) {
										entry = filterGroup[jkey];
										if($('.'+entry.id).css('display') != 'none'){ 
											   params[entry.id] = $('#'+entry.id).val();
										}
									}
								}
								actions.loadPostParams = params;
							} else {
								params = actions.loadPostParams;
							}
							if (typeof(actions.initData) == "function") {
								var extras = actions.initData();
								if (extras) {
									jQuery.extend(extras, params);
									return extras;
								}
							}
							return params;
						},
	
	loadSaveParams 		: function (reLoadParams){
							var params = actions.loadParams(reLoadParams);
							if (reLoadParams) {
								if(!jQuery.isEmptyObject(actions.editedData)){
									params['editedData'] = actions.editedData;
								}
								if(!jQuery.isEmptyObject(actions.deleteData)){
									params['deleteData'] = actions.deleteData;
								}
								params['objectIds'] = actions.objectIds;
							} else {
					//			params = actions.loadPostParams;
							}
							return params;
						},
	
	doLoad 				: function (reLoadParams){
							if (this.loadUrl) {
								validated = actions.loadValidating(reLoadParams);
								if(validated){
									console.log ( "doLoad url: "+this.loadUrl );
									actions.readyToLoad = true;
									showWaiting();
									actions.editedData = {};
									$.ajax({
										url: this.loadUrl,
										type: "post",
										data: actions.loadParams(reLoadParams),
										success:function(data){
											hideWaiting();
											if(data!=null&&data.hasOwnProperty('objectIds')){
												actions.objectIds = data.objectIds;
											}
											actions.editedData = {};
											if (typeof(actions.loadSuccess) == "function") {
												actions.loadSuccess(data);
											}
											else{
												alert("load success");
											}
										},
										error: function(data) {
											hideWaiting();
											if (typeof(actions.loadError) == "function") {
												actions.loadError(data);
											}
										}
									});
									return true;
								}
								else console.log ( "not validated");
							}
							else{
								alert("init load params");
							}
							return false;
						},
	updateView 			: function(postData){
							var noData = jQuery.isEmptyObject(postData);
							if (!noData) {
								for (var key in javascriptFilterGroups) {
									filterGroup = javascriptFilterGroups[key];
									for (var jkey in filterGroup) {
										entry = filterGroup[jkey];
										if($('.'+entry.id).css('display') != 'none'){
											if ($('#'+entry.id).val()!=postData[entry.id]) {
												$('#'+entry.id).val(postData[entry.id]).trigger('change');
											}
										}
									}
								}
							}
						},
	loadValidating 		: function (reLoadParams){return true;},
	validating 			: function (reLoadParams){
							isNoChange = (jQuery.isEmptyObject(actions.editedData))&&(jQuery.isEmptyObject(actions.deleteData));
							if(isNoChange) alert("no change to commit");
							return !isNoChange;
						},
	doSave 				: function (reLoadParams){
							if (this.saveUrl) {
								validated = actions.validating(reLoadParams);
					//			actions.readyToLoad = true;
								if(validated){
									console.log ( "doLoad url: "+this.saveUrl );
									showWaiting();
									$.ajax({
										url: this.saveUrl,
										type: "post",
										data: actions.loadSaveParams(reLoadParams),
										success:function(data){
											hideWaiting();
											if (typeof(actions.saveSuccess) == "function") {
												actions.saveSuccess(data);
											}
											else{
												alert("save success");
											}
										},
										error: function(data) {
											if (typeof(actions.loadError) == "function") {
												actions.loadError(data);
											}
											hideWaiting();
										}
									});
									return true;
								}
								else console.log ( "not validated ");
							}
							else{
								alert("save url not initial");
							}
							return false;
						},
	getExtraDataSetColumn :function(data,cindex,rowData){
							sourceColumn = data.properties[cindex].data;
							ecolumn = actions.extraDataSetColumns[sourceColumn];
					 		ecollectionColumn = rowData[ecolumn];
					 		ecollection = null;
					 		
					 		if(ecollectionColumn!=null&&
					 				ecollectionColumn!=''&&
					 				typeof(actions.extraDataSet[ecolumn]) !== "undefined"){
					 			if(actions.extraDataSet[ecolumn].hasOwnProperty(sourceColumn)){
					 				ecollection = actions.extraDataSet[ecolumn][sourceColumn];
					 			}
					 			else if(typeof(actions.extraDataSet[ecolumn][ecollectionColumn]) !== "undefined"){
						 			ecollection = actions.extraDataSet[ecolumn][ecollectionColumn];
						 		}
							}
					 		if(ecollection == null){
					 			if($.isArray(actions.extraDataSet[ecolumn]))  ecollection = actions.extraDataSet[ecolumn];
					 			else if(typeof(sourceColumn) !== "undefined" && sourceColumn==ecolumn){
					 	 			ecollection = actions.extraDataSet[sourceColumn][ecolumn];
					 	 		}
					 		} 
					 			
					 		return ecollection;
						},
	isEditable 			: function (column,rowData,rights){
						var rs = column.DATA_METHOD==1||column.DATA_METHOD=='1';
						if (rs) {
							if(rowData.RECORD_STATUS=="A"){
								rs =$.inArray("ADMIN_APPROVE", rights);
							}
							else if(rowData.RECORD_STATUS=="V"){
								rs =$.inArray("ADMIN_APPROVE", rights)&&$.inArray("ADMIN_VALIDATE", rights);
							}
						}
						return rs;
						
					},
	preDataTable 	: function (dataset){
						return dataset;
					},
	afterDataTable : function (table,tab){
		$("#toolbar_"+tab).html('');
	},
	deleteActionColumn : function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		var html = '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
		return html;
	},
	isDisableAddingButton	: function (tab,table) {
		return true;
	},
	renderFirsColumn : function ( data, type, rowData ) {
		var html = "<div class='firstColumn'>"+data+"</div>";
		var extraHtml = "<div class='extraFirstColumn'>";
		if(rowData.hasOwnProperty('PHASE_CODE')){
			extraHtml += "<div class='phase "+rowData['PHASE_CODE']+"'>"+
					rowData['PHASE_NAME']+"</div>";
		}
		else if(rowData.hasOwnProperty('PHASE_NAME')){
			extraHtml += "<div class='phase "+rowData['PHASE_NAME']+"'>"+
			rowData['PHASE_NAME']+"</div>";
		}
		if(rowData.hasOwnProperty('STATUS_NAME')){
			extraHtml +="<span class='eustatus'>"+rowData['STATUS_NAME']+"</span>";
		}
		if(rowData.hasOwnProperty('TYPE_CODE')){
			extraHtml +="<span class='eventType'>"+rowData['TYPE_CODE']+"</span>";
		}
		extraHtml += "</div>";
		return html+extraHtml;
	},
	applyEditable : function (tab,type,td, cellData, rowData, columnName,collection){
		var successFunction = actions.getEditSuccessfn(tab,td, rowData, columnName,collection);
		var  editable = {
	    	    title: 'edit',
	    	    emptytext: '',
	    	    showbuttons:false,
	    	    success: successFunction,
	    	};
		
		switch(type){
		case "text":
		case "number":
		case "date":
			editable['type'] = type;
			editable['step'] = 'any';
			editable['validate'] = function(value) {
						    	        if($.trim(value) == '') {
						    	            return 'This field is required';
						    	        }
						    	    };
    	    editable['onblur'] = 'cancel';
			if (type=='date') {
				editable['onblur'] = 'submit';
				editable['format'] = configuration.picker.DATE_FORMAT_UTC;
//				editable['format'] = 'mm/dd/yyyy';
				editable['viewformat'] = configuration.picker.DATE_FORMAT;
//				editable['viewformat'] = 'mm/dd/yyyy';
			}
			if (type=='number'&&this.historyUrl) {
				editable['extensionHandle'] = function() {
												actions.extensionHandle(tab,columnName,rowData,false,successFunction,true);
									    	  };
			}
	    	break;
	    	
		case "datetimepicker":
			editable['onblur'] = 'submit';
			editable['type'] = 'datetime';
			editable['format'] = configuration.picker.DATETIME_FORMAT_UTC;
//			editable['format'] = 'mm/dd/yyyy hh:ii';
			editable['viewformat'] = configuration.picker.DATETIME_FORMAT;
//			editable['viewformat'] = 'mm/dd/yyyy hh:ii';
			editable['datetimepicker'] 	= 	{
								          		weekStart: 1,
								          		minuteStep :5,
								          		showMeridian : true,
//								          		startView:1
								            };
	    	break;
	    	
		case "timepicker":
			editable['onblur'] = 'submit';
			editable['type'] = 'datetime';
			editable['format'] = configuration.picker.TIME_FORMAT_UTC;
//			editable['format'] = 'hh:ii:ss';
			editable['viewformat'] = configuration.picker.TIME_FORMAT;
//			editable['viewformat'] = 'HH:ii P';
			editable['datetimepicker'] 	= 	{
								          		minuteStep :5,
								          		showMeridian : true,
								          		startView:1,
								          		minView:0,
								          		maxView:1,
								            };
	    	break;
		case "select":
			editable['type'] = type;
			editable['source'] = collection;
			editable['value'] = cellData==null?(collection!=null&&collection[0]!=null?collection[0].ID:0):cellData;
			$(td).editable(editable);
			return;
	    	break;
		}	
		$(td).editable(editable);
    	$(td).on("shown", function(e, editable) {
    		  editable.input.$input.get(0).select();
    		  if(type=="timepicker") $(".table-condensed thead").css("visibility","hidden");
    		  $(".extension-buttons").css("display","none");
    		  if(type=="number") {
					$( editable.input.$input.get(0) ).closest( ".editable-container" ).css("float","right");
					if (actions.historyUrl) $(".extension-buttons").css("display","block");
    		  }
//    		  if(type=="timepicker") $(".table-condensed th").text("");
    	});
	},
	applyLockedCellHistory	:	function (tab,type,td, cellData, rowData, columnName){
		if(type=="number") {
			var successFunction = actions.getEditSuccessfn(tab,td, rowData, columnName);
			var  editable = {
					title			: 'edit',
					emptytext		: '',
					showbuttons		: false,
					success			: successFunction,
					onblur			: 'cancel',
					extensionHandle	: function() {
						actions.extensionHandle(tab,columnName,rowData,false,successFunction);
					}
			};
//			$(td).css("position","relative");
			var $newdiv1 = $( "<div class='fakeCell' style='width: 100%;height: 100%;position: absolute;left: 0; top: 0;'></div>" );
			$( td ).append( $newdiv1 );
			
			$($newdiv1).editable(editable);
			$($newdiv1).on("shown", function(e, editable) {
				$(".extension-buttons").css("display","none");
				$( editable.input.$input.get(0) ).closest( ".editable-container" ).css("float","right");
				if (actions.historyUrl) {
					$(".editable-input").css("display","none");
//					$(".editable-input input").prop("disabled", true);
					$(".extension-buttons").css("display","block");
					$(td).css("display","table-cell");
					$(".editable-extension").css("margin","0px");
					$(".editable-container").css("float","left");
				}
			});
		}
	},
	getEditSuccessfn  : function(tab, td, rowData, columnName,collection) {
		return function(response, newValue) {
        	rowData = actions.putModifiedData(tab,columnName,newValue,rowData);
        	rowData[columnName] = newValue;
        	var table = $('#table_'+tab).dataTable();
			table.api().row( '#'+rowData['DT_RowId'] ).data(rowData);
        	$(td).css('color', 'red');
        	table.api().draw(false);
        	//dependence columns
        	actions.dominoColumns(columnName,newValue,tab,rowData,collection,table,td);
        	 /* var tabindex = $(this).attr('tabindex');
            $('[tabindex=' + (tabindex +1)+ ']').focus(); */
	    };
	},
	extensionHandle		:	function(tab,columnName,rowData,limit,successFunction){
	},
	dominoColumnSuccess	:	function(data,dependenceColumnNames,rowData,tab){
		$.each(dependenceColumnNames, function( i, dependence ) {
			dataSet = data.dataSet[dependence].data;
			if(typeof(dataSet) !== "undefined"&&dataSet.length>0){
				sourceColumn = data.dataSet[dependence].sourceColumn;
				ofId = data.dataSet[dependence].ofId;
				cellData=dataSet[0]['ID'];
				rowData[dependence] = cellData;
				if(typeof(actions.extraDataSet[sourceColumn]) == "undefined"){
					actions.extraDataSet[sourceColumn] = [];
				}
				actions.extraDataSet[sourceColumn][ofId] = dataSet;
				dependencetd = $('#'+DT_RowId+" ."+dependence);
				actions.applyEditable(tab,'select',dependencetd, cellData, rowData, dependence,dataSet);
				actions.putModifiedData(tab,dependence,cellData,rowData);
//				createdFirstCellColumnByTable(table,rowData,dependencetd,tab);
			}
		});
		console.log ( "success dominoColumns "+data );
	},
	getKeyFieldSet : function(){
		if(typeof(actions.type.idName) == "function"){
			return actions.type.idName();
		}
		return actions.type.idName;
	},
	putModifiedData : function(tab,columnName,newValue,rowData){
		var table = $('#table_'+tab).dataTable();
		var id = rowData['DT_RowId'];
		var isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		var recordData = actions.editedData;
    	if (!(tab in recordData)) {
    		recordData[tab] = [];
    	}
    	var eData = recordData[tab];
    	var result = $.grep(eData, function(e){ 
						               	 return e[actions.type.keyField] == rowData[actions.type.keyField];
						                });
//    	var columnName = table.settings()[0].aoColumns[col].data;
    	if (newValue!=null&&newValue.constructor.name == "Date") {
    		newValue = actions.getTimeValueBy(newValue,columnName,tab);
		}
    	if (result.length == 0) {
        	var editedData = {};
        	idName = actions.getKeyFieldSet();
        	 $.each(idName, function( i, vl ) {
	        	editedData[vl] = rowData[vl];
             });
        	editedData[columnName] = newValue;
        	if(isAdding) {
        		editedData['isAdding'] = true;
        	}
        	editedData['DT_RowId'] = rowData['DT_RowId'];
    		eData.push(editedData);
    	}
    	else{
    		result[0][columnName] = newValue;
    	}
    	return rowData;
	},
	getTimeValueBy : function(newValue,columnName,tab){
		if(columnName=='EFFECTIVE_DATE'||columnName=='OCCUR_DATE'){
			return moment.utc(newValue).format(configuration.time.DATE_FORMAT_UTC);
//			return moment.utc(newValue).format("YYYY-MM-DD HH:mm:ss");
		}
		return moment(newValue).format(configuration.time.DATETIME_FORMAT_UTC);
//		return moment(newValue).format("YYYY-MM-DD HH:mm:ss");
	},
	getCellType : function(data,type,cindex){
		type = actions.extraDataSetColumns.hasOwnProperty(data.properties[cindex].data)?'select':type;
		return type;
	},
	getCellProperty : function(data,tab,type,cindex){
		var cell = {"targets"	: cindex};
		type = actions.getCellType(data,type,cindex);
		const columnName = data.properties[cindex].data;
		if (type!='checkbox') {
			cell["createdCell"] = function (td, cellData, rowData, row, col) {
					colName = data.properties[col].data;
	 				$(td).addClass( colName );
	 				$(td).addClass( "cell"+type );
		 			if(!data.locked&&actions.isEditable(data.properties[col],rowData,data.rights)){
		 				$(td).addClass( "editInline" );
		 				colName = data.properties[col].data;
//		 				$(td).addClass( colName );
		 	        	var table = $('#table_'+tab).DataTable();
		 	        	collection = null;
		 	        	if(type=='select'){
		 	        		collection = actions.getExtraDataSetColumn(data,cindex,rowData);
		 	        	}
		 				actions.applyEditable(tab,type,td, cellData, rowData, colName,collection);
		 			}
		 			else if (type=='number'&&actions.historyUrl)  actions.applyLockedCellHistory(tab,type,td, cellData, rowData, colName);
			    };
		}
		switch(type){
		case "text":
			if(columnName=='UOM'){
				cell["render"] = function ( data2, type2, row ) {
					var rendered = data2;
					if(data2==null){
						rendered = row.DEFAULT_UOM;
					}
					return rendered;
				};
			}
	    	break;
		case "number":
			cell["render"] = function ( data2, type2, row ) {
								value = actions.getNumberRender(columnName,data,data2, type2, row);
								if(value==null){
									var rendered = data2;
									if(data2!=null&&data2!=''){
										rendered = parseFloat(data2).toFixed(2);
										if(isNaN(rendered)) return '';
									}
									return rendered;
								}
								return value;
							};
	    	break;
		case "date":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null||data2=='') { 
									return "";
								}
								if (data2.constructor.name == "Date") { 
									return moment.utc(data2).format(configuration.time.DATE_FORMAT);
//									return moment(data2).format("MM/DD/YYYY");
									
								}
								return moment(data2,configuration.time.DATE_FORMAT_UTC).format(configuration.time.DATE_FORMAT);
//								return moment(data2,"YYYY-MM-DD").format("MM/DD/YYYY");
							};
	    	break;
		case "datetimepicker":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null||data2=='') { 
									return "";
								}
								if (data2.constructor.name == "Date") { 
									return moment(data2).format(configuration.time.DATETIME_FORMAT);
//									return moment(data2).format("MM/DD/YYYY HH:mm");
								}
								return moment.utc(data2,configuration.time.DATETIME_FORMAT_UTC).format(configuration.time.DATETIME_FORMAT);
//								return moment.utc(data2,"YYYY-MM-DD HH:mm").format("MM/DD/YYYY HH:mm");
							};
	    	break;
		case "timepicker":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null||data2=='') { 
									return "";
								}
								if (data2.constructor.name == "Date") { 
									return moment(data2).format(configuration.time.TIME_FORMAT);
//									return moment(data2).format("hh:mm A");
								}
								return moment(data2,configuration.time.TIME_FORMAT_UTC).format(configuration.time.TIME_FORMAT);
//								return moment(data2,"hh:mm:ss").format("hh:mm A");
							};
	    	break;
		case "checkbox":
//			cell["className"] = 'select-checkbox';
			cell["render"] = function ( data2, type2, row ) {
								checked = data2?'checked':'';
								return '<div  class="checkboxCell" ><input class="cellCheckboxInput" type="checkbox" value="'+data2+'"size="15" '+checked+'></div>';
							};
			cell["createdCell"] = function (td, cellData, rowData, row, col) {
				colName = data.properties[col].data;
 				$(td).addClass( colName );
 				$(td).addClass( "cell"+type );
 				$(td).find(".cellCheckboxInput").click(function(){
 					fn = actions.getEditSuccessfn(tab,td, rowData, columnName,collection);
 					fn(null,$(this).is(':checked')?1:0);
 				});
		    };
	    	break;
		case "select":
			cell["render"] = function ( data2, type2, row ) {
 	        		collection = actions.getExtraDataSetColumn(data,cindex,row);
		     		if(collection!=null){
		     			var result = $.grep(collection, function(e){
		     				if(typeof(e) !== "undefined"){
		     					if(e.hasOwnProperty('ID')) {
		     						return e['ID'] == data2;
		     					}
		     					else if(e.hasOwnProperty('value')) {
		     						return e['value'] == data2;
		     					}
		     				}
		    				return false;
		     			});
		     			if(typeof(result) !== "undefined" && typeof(result[0]) !== "undefined" &&result[0].hasOwnProperty('NAME')){
		     				return result[0]['NAME'];
		     			}
		     		}
					return '';
				};
	    	break;
		}
		return cell;
	},
	createdFirstCellColumn : function (td, cellData, rowData, row, col) {
		$(td).css('z-index','1');
	},
	getGrepValue : function (data,value,row) {
						return data;
	},
	notUniqueValue : function(uom,rowData){
		return true;
	},
	isShownOf : function(value,postData){
		return true;
	},
	getNumberRender: function(columnName,data,data2, type2, row){
		return null;
	},
	getExtendWidth: function(data,autoWidth,tab){
		return 0;
	},
	getTableWidth: function(data,autoWidth,tab){
		var  marginLeft = 0;
		var  tblWdth = 0;
		$.each(data.properties, function( ip, vlp ) {
			if(autoWidth){
				delete vlp['width'];
			}
			else{
				if(ip==0){
//  				vlp['className']= 'headcol';
					marginLeft = vlp['width'];
				}
				var iw = (vlp['width']>1?vlp['width']:100);
				tblWdth+=iw;
				vlp['width']= iw+"px";
				
				if(ip!=0&&(vlp.title==null||vlp.title=='')) {
					vlp.title=vlp.data;
					vlp['width']= (iw*1.5)+"px";
				}
			}
        });
		extendWidth = actions.getExtendWidth(data,autoWidth,tab);
		return tblWdth+extendWidth;
	},
	getTableHeight:function(tab){
		headerOffset = $('#ebTabHeader').offset();
		hhh = $(document).height() - (headerOffset?(headerOffset.top):0) - $('#ebTabHeader').outerHeight() - $('#ebFooter').outerHeight() - 100;
		tHeight = ""+hhh+'px';
		return tHeight;
	},
	getTableOption: function(data){
		return {tableOption :{searching: true},
				invisible:[]};
		
	},
	initTableOption : function (tab,data,options,renderFirsColumn,createdFirstCellColumn){
		if(typeof(data.uoms) == "undefined"||data.uoms==null){
			data.uoms = [];
		}
		var uoms = data.uoms;
		var invisible = options!=null&&(typeof(options.invisible) !== "undefined"&&options.invisible!=null)?options.invisible:null;
		var exclude = [0];
		if(typeof(renderFirsColumn) == "function"){
			exclude = [0];
		}
		else exclude = [];

		if(typeof(uoms) !== "undefined"&&uoms!=null){
			$.each(uoms, function( index, value ) {
				exclude.push(uoms[index]["targets"]);
				var collection = value['data'];
				if(value==null||!value.hasOwnProperty('render')) {
					uoms[index]["render"] = function ( data, type, row ) {
						var result = $.grep(collection, function(e){
							id = actions.getGrepValue(data,value,row);
							return e['ID'] == id;
						});
						if(typeof(result) !== "undefined" && typeof(result[0]) !== "undefined" &&result[0].hasOwnProperty('NAME')){
							return value['COLUMN_NAME']=="ALLOC_TYPE"?result[0]['NAME']:result[0]['NAME'];
						}
						return data;
					};
				}
	            $.each(collection, function( i, vl ) {
	            	vl['value']=vl['ID'];
	            	vl['text']=vl['NAME'];
	            });
	            uoms[index]["createdCell"] = function (td, cellData, rowData, row, col) {
	            	columnName = data.properties[col].data;
	 				$(td).addClass( columnName );
	            	if(!data.locked&&actions.isEditable(data.properties[col],rowData,data.rights)&&actions.notUniqueValue(uoms[index],rowData)){
		 				$(td).addClass( "editInline" );
		 				$(td).editable({
			        	    type: 'select',
			        	    title: 'edit',
			        	    emptytext: '',
			        	    value:cellData,
			        	    showbuttons:false,
			        	    source: collection,
			        	    success: actions.getEditSuccessfn(tab,td, rowData, columnName,collection),
			        	});
		 			}
	   			}
	            if(invisible!=null&&$.inArray(data.properties[index].data, invisible)>=0){
	            	uoms[index]['visible']=false;
				}
			});
		}

		var original = Array.apply(null, Array(data.properties.length)).map(function (_, i) {return i;});
		var finalArray = $(original).not(exclude).get();
		if(data.hasOwnProperty('extraDataSet')){
			actions.extraDataSet = data.extraDataSet;
		}
		$.each(finalArray, function( i, cindex ) {
			var type = typetoclass(data.properties[cindex].INPUT_TYPE);
			var cell = actions.getCellProperty(data,tab,type,cindex);
			if(invisible!=null&&$.inArray(data.properties[cindex].data, invisible)>=0){
				cell['visible']=false;
			}
    		uoms.push(cell);
        });
		
		if(typeof(renderFirsColumn) == "function"){
			var phase = {"targets": 0,
					"render": renderFirsColumn,
			};
			if(createdFirstCellColumn!=null) phase["createdCell"] = createdFirstCellColumn;
			uoms.push(phase);
		}
		
		var autoWidth = false;
		if( options!=null&&
				(typeof(options.tableOption) !== "undefined"&&
						options.tableOption!=null)&&
						(typeof(options.tableOption.autoWidth) !== "undefined"&&
								options.tableOption.autoWidth!=null)){
			autoWidth = options.tableOption.autoWidth;
		}
		
		var tblWdth = actions.getTableWidth(data,autoWidth,tab);
		if(!autoWidth) $('#table_'+tab).css('width',(tblWdth)+'px');
		
		tHeight = actions.getTableHeight(tab);
		option = {data: data.dataSet,
		          columns: data.properties,
		          destroy: true,
		          "columnDefs": uoms,
		          "scrollX": true,
		         "autoWidth": autoWidth,
//		       	"scrollY":        "37vh",
//		         "scrollY":        "250px",
		       	scrollY:        tHeight,
//		                "scrollCollapse": true,
				"paging":         false,
				"dom": 'rt<"#toolbar_'+tab+'">p<"bottom"i><"bottom"f><"clear">',
				/* initComplete: function () {
					var cls = this.api().columns();
		            cls.every( function () {
		                var column = this;
		                var ft = $(column.footer());
		                ft.html("keke");
		                var select = $('<select><option value=""></option></select>')
		                    .appendTo( $(column.footer()).empty() );
		            } );
		        }, */
		        /* "footerCallback": function ( row, data, start, end, display ) {
		            var cls = this.api().columns();
		            cls.every( function () {
		                var column = this;
		                var ft = $(column.footer());
		                ft.html("keke");
		            } );
		        }, */
//				 "dom": '<"top"i>rt<"bottom"flp><"clear">'
//		           paging: false,
//		          searching: false 
		    };
		    
	    if (options!=null) {
	 		if(typeof(options.tableOption) !== "undefined"&&options.tableOption!=null){
	 			jQuery.extend(option, options.tableOption);
	 		}
		}
		
	    /*isDestroyTable = typeof(options.destroy) !== "undefined"&&options.destroy;
	    if (isDestroyTable) {
	    	 $('#table_'+tab).DataTable(option);
	    }*/

		var tbl = $('#table_'+tab).DataTable(option);
		return tbl;
	},
	getExistRowId		: function(value,key){
		return value[actions.type.saveKeyField(key)];
	}
}
	