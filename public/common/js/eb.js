//inline editable
//turn to inline mode
if(typeof($.fn.editable) !== "undefined") $.fn.editable.defaults.mode = 'inline';

$.fn.equals = function(compareTo) {
	  if (!compareTo || this.length != compareTo.length) {
	    return false;
	  }
	  for (var i = 0; i < this.length; ++i) {
	    if (this[i] !== compareTo[i]) {
	      return false;
	    }
	  }
	  return true;
	};
	
var ebtoken = $('meta[name="_token"]').attr('content');
$.ajaxSetup({
	headers: {
		'X-XSRF-Token': ebtoken
	}
});

function arrayUnique(array,equalFunction) {
    var a = array.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j] || (typeof equalFunction == "function" && equalFunction(a[i],a[j])))
                a.splice(j--, 1);
        }
    }

    return a;
}

function getJsDate(dateString){
	date = moment.utc(dateString,configuration.time.DATE_FORMAT_UTC);
	y = date.year();
	m = date.month();
	d = date.date();
	date = Date.UTC(y,m,d);
    return date;
}

function isInt(n){
    return Number(n) === n && n % 1 === 0;
}

function isFloat(n){
    return Number(n) === n && n % 1 !== 0;
}

var filters = {};
var renderDependenceHtml = function(elementId,dependenceData) {
	var option = $('<option />');
	var name = typeof(dependenceData.CODE) !== "undefined"?dependenceData.CODE:dependenceData.NAME;
	option.attr('name', name);
	option.attr('value', dependenceData.ID).text(dependenceData.NAME);
	return option;
};

var enableSelect = function(dependentIds, value) {
	for (var i = 0; i < dependentIds.length; i++) {
		$('#'+dependentIds[i]).prop('disabled', value);
		if (!value&&typeof(filters.afterRenderingDependences) == "function") {
			filters.afterRenderingDependences(dependentIds[i]);
		}
	}
};

var registerOnChange = function(sourceObject, dependentIds,more) {
	var model = null;
	var id = sourceObject;
	var dependeceNameFn = function(){};
	var initDependentSelectsFn = function(){};

	if(typeof sourceObject =="string"){
		var partials 	= sourceObject.split("_");
		var prefix 		= partials.length>1?partials[0]+"_":"";
		model 		= partials.length>1?partials[1]:id;
		dependeceNameFn = function(dvalue){
			return prefix+dvalue;
		};
		initDependentSelectsFn = function(tmpDependentIds){
			var dependentSelects = [];
			$.each(tmpDependentIds, function( dindex, dvalue ) {
				if (typeof dvalue === 'string' || dvalue instanceof String){
					var dependeceName = dependeceNameFn(dvalue);
					dependentSelects.push(dependeceName);
				}
				else if(typeof(dvalue["name"]) !== "undefined"
					&&(typeof(dvalue["independent"]) === "undefined")
						||!dvalue["independent"]){
					var dependeceName = dependeceNameFn(dvalue["name"]);
					dependentSelects.push(dependeceName);
				}
			});
			
			return dependentSelects;
		};
	}
	else{
		id 			= sourceObject.id;
		model 		= sourceObject.model;
		prefix 		= sourceObject.valueId;
		dependeceNameFn = function(dvalue){
			if(prefix!="") return dvalue+"-"+prefix;
			return dvalue;
		};
		
		initDependentSelectsFn = function(tmpDependentIds){
			var dependentSelects = [];
			$.each(sourceObject.targets, function( dindex, dvalue ) {
				var dependeceName = dependeceNameFn(dvalue);
				dependentSelects.push(dependeceName);
			});
			return dependentSelects;
		};
	}
	$('#'+id).change(function(e){
		if($.isArray(tmpDependentIds)){
			var tmpDependentIds = $.merge([], dependentIds);
		}
		else {
			var tmpDependentIds = Object.keys(dependentIds).map(function (key) {
				return dependentIds[key];
				/*if(typeof dependentIds[key] == "string") return dependentIds[key]; 
				else if(typeof dependentIds[key] == "object") return dependentIds[key]['name']; */
			});
		}
		
		if (typeof(filters.preOnchange) == "function") {
			filters.preOnchange(id,tmpDependentIds,more,prefix);
		}
		
		var ccontinue = false;
		var currentValue = $(this).val();
		if(typeof filters.moreDependence == 'function') 
			tmpDependentIds = filters.moreDependence(tmpDependentIds,model,currentValue,prefix);
		
		var dependentSelects = initDependentSelectsFn(tmpDependentIds);
		
		$.each(dependentSelects, function( dindex, dvalue ) {
			ccontinue = ccontinue|| $("#"+dvalue).is(":visible");
		});
		if(!ccontinue) return;
		
		enableSelect(dependentSelects,'disabled');
		bundle = {};
		if (more!=null&&more.length>0) {
			$.each(more, function( i, value ) {
				bundle[value] = {};
				var elementId = dependeceNameFn(value);
				var name = $("#"+elementId).find(":selected").attr( "name");
				var val = $("#"+elementId).val();
				name = typeof(name) !== "undefined"?name:val;
				bundle[value]['name'] 	= name;
				bundle[value]['id'] 	= val;
			});
		}
		$.ajax({
			url: '/code/list',
			type: "post",
			data: {	type		: model,
					dependences	: tmpDependentIds,
					value		: currentValue,
					extra		: bundle
				},
			success: function(results){
				$.each(dependentSelects, function( dindex, dvalue ) {
					$('#'+dvalue).html('');   // clear the existing options
				});
				for (var i = 0; i < results.length; i++) {
					var elementId = dependeceNameFn(results[i].id);
					if(typeof results[i]['default']=='object'){
						var option = renderDependenceHtml(results[i]['default'].ID,results[i]['default']);
						$('#'+elementId).append(option);
					}
					$(results[i].collection).each(function(){
						var option = renderDependenceHtml(elementId,this);
						$('#'+elementId).append(option);
					});
					$('#'+elementId).val(results[i].currentId);
				}
				
				enableSelect(dependentSelects,false);
			},
			error: function(data) {
				console.log(data.responseText);
				alert("Could not get dropdown menu");
//				enableSelect(dependentIds,false);
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
		case 7:
			return "EVENT";
		default:
			return data;
	}
	return "text";
};

var source = {
		initRequest	:	 function(tab,columnName,newValue,collection){
			postData = actions.loadedData[tab];
			srcData = {	name : columnName,
						value : newValue,
						Facility : postData['Facility'],
					};
			if(typeof source[columnName] == "object") srcData.target	= source[columnName].dependenceColumnName;
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
	objectIds 			: {},
	extraDataSetColumns : {},
	extraDataSet 		: {},
	loadSuccess 		: function(data){/*alert("success");*/},
	loadError 			: function(data){/*alert(JSON.stringify(data.responseText));*/alert('loading data error!');},
	shouldLoad 			: function(data){return false;},
	addingNewRowSuccess	: function(data,table,tab,isAddingNewRow){},
	afterGotSavedData 	: function(data,table,key){},
	dominoColumns 		: function(columnName,newValue,tab,rowData,collection,td){},
	tableIsDragable 	: function(tab){return false;},
	loadNeighbor		: function (){
							if (actions.shouldLoad()) {
								actions.doLoad(false);
							}
							else{
								var activeTabID = getActiveTabID();
								var postData = actions.loadedData[activeTabID];
								actions.updateView(postData);
								var table =$("#table"+activeTabID).DataTable();
//								table.columns.adjust().draw();
//								$("#table"+activeTabID).resize();
								table.draw();
							}
						},
	loadParams 			: function (reLoadParams){
							var params;
							if (reLoadParams) {
								params = {};
								if (typeof(javascriptFilterGroups) !== "undefined") {
									for (var key in javascriptFilterGroups) {
										filterGroup = javascriptFilterGroups[key];
										for (var jkey in filterGroup) {
											entry = filterGroup[jkey];
											if($('.'+entry.id).css('display') != 'none'){ 
												   params[entry.id] = $('#'+entry.id).val();
											}
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
//									actions.editedData = {};
									$.ajax({
										url: this.loadUrl,
										type: "post",
										data: actions.loadParams(reLoadParams),
										success:function(data){
											hideWaiting();
//											if(reLoadParams) actions.editedData = {};
											if (typeof(actions.loadSuccess) == "function") {
												actions.loadSuccess(data);
											}
											else{
												alert("load success");
											}
										},
										error: function(data) {
											console.log ( "doLoad error");
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
							if (!noData&&typeof(javascriptFilterGroups) !== "undefined") {
								for (var key in javascriptFilterGroups) {
									filterGroup = javascriptFilterGroups[key];
									for (var jkey in filterGroup) {
										entry = filterGroup[jkey];
										if(typeof(entry) !== "undefined"&&
												$('.'+entry.id).css('display') != 'none'){
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
	doSave 				: function (reLoadParams,edittedData){
							if (this.saveUrl) {
								validated = actions.validating(reLoadParams);
					//			actions.readyToLoad = true;
								if(validated){
									console.log ( "doLoad url: "+this.saveUrl );
									showWaiting();
									var postData	= typeof edittedData == "object"?postData:actions.loadSaveParams(reLoadParams);
									$.ajax({
										url	: this.saveUrl,
										type: "post",
										data: postData,
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
											console.log ( "doSave error");
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
		return actions.defaultRenderFirsColumn(data, type, rowData);
	},
	renderDatePicker : function (editable,columnName,cellData, rowData){
		editable['viewformat'] = configuration.picker.DATE_FORMAT;
		return editable;
	},
	renderDateFormat : function (data2,type2,row){
		if (data2.constructor.name == "Date") { 
			return moment.utc(data2).format(configuration.time.DATE_FORMAT);
//			return moment(data2).format("MM/DD/YYYY");
			
		}
		return moment.utc(data2,configuration.time.DATETIME_FORMAT_UTC).format(configuration.time.DATE_FORMAT);
//		return moment(data2,"YYYY-MM-DD").format("MM/DD/YYYY");
	},
	validates : function (input,value,property){
//		if(property)
	},
	defaultRenderFirsColumn : function ( data, type, rowData ) {
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
	validateNumberWithRules : function (property,strimValue){
		var minValue = typeof(property.VALUE_MIN) !== "undefined"&&
		property.VALUE_MIN != null &&
		property.VALUE_MIN != ""?
		parseFloat(property.VALUE_MIN):-1*Number.MAX_VALUE;
		var maxValue = typeof(property.VALUE_MAX) !== "undefined"&&
				property.VALUE_MAX != null &&
				property.VALUE_MAX != ""?
				parseFloat(property.VALUE_MAX):Number.MAX_VALUE;
		
		if(minValue>=maxValue) return;
		if(strimValue < minValue) return 'This field need greater or equal '+property.VALUE_MIN;
		if(strimValue > maxValue) return 'This field need less or equal '+property.VALUE_MAX;

	},
	applyEditable : function (tab,type,td, cellData, rowData, property,collection){
		var columnName = typeof property === 'string'?property:property.data;
		var successFunction = actions.getEditSuccessfn(property,tab,td, rowData, columnName,collection,type);
		var  editable = {
	    	    title: 'edit',
	    	    emptytext: '',
	    	    onblur		: 'submit',
	    	    showbuttons:false,
	    	    success: successFunction,
	    	};
		
		switch(type){
		case "text":
		case "number":
		case "date":
			editable['type'] = type;
    	    editable['onblur'] = 'submit';
			if (type=='date') {
				editable['onblur'] 		= 'submit';
				editable['format'] 		= configuration.picker.DATE_FORMAT_UTC;
				editable				= actions.renderDatePicker(editable,columnName,cellData, rowData); 
				editable['inputclass'] 	= "datePickerInput";
//				editable['format'] = 'mm/dd/yyyy';
//				editable['viewformat'] = 'mm/dd/yyyy';
			}
			else if(type=='number') {
				editable['type'] = "text";
				if(configuration.number.DECIMAL_MARK=='comma') 
					editable['tpl'] = "<input class='cellnumber' type=\"text\" pattern=\"^[-]?[0-9]+([,][0-9]{1,20})?\">";
				else  
					editable['tpl'] = "<input class='cellnumber' type=\"text\" pattern=\"^[-]?[0-9]+([\.][0-9]{1,20})?\">";
			}
	    	break;
		case "EVENT":
			editable['type'] 		= type;
			editable['title'] 		= "";
			editable['onblur'] 		= 'cancel';
			editable['value'] 		= cellData;
			editable['mode'] 		= "popup";
			editable['placement'] 	= "left";
			editable['showbuttons'] = true;
	    	break;
		case "datetimepicker":
			editable['onblur'] 	= 'submit';
			editable['type'] 	= 'datetime';
			editable['format'] 	= configuration.picker.DATETIME_FORMAT_UTC;
//			editable['format'] 	= 'mm/dd/yyyy hh:ii';
			editable['viewformat'] = configuration.picker.DATETIME_FORMAT;
//			editable['viewformat'] = 'mm/dd/yyyy hh:ii';
			editable['datetimepicker'] 	= 	{
//								          		weekStart: 1,
								          		minuteStep :5,
								          		showMeridian : true,
//								          		minViewMode	:1,
//								          		maxViewMode	:3,
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
		case "color":
			$(td).addClass( "_colorpicker" );
			$(td).data(cellData);
			$(td).css("background-color",'#'+cellData);
			$(td).css("color",'#'+cellData);
			$(td).ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val(hex);
					$(el).css({"background-color":"#"+hex,"color":"#"+hex});
					$(el).ColorPickerHide();
					rowData[columnName] = hex;
//					$(td).text(hex);
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(rowData[columnName]);
				}
			});
			return;	
		}
		editable['validate'] = function(value) {
			var validateResult	= null;
			var strimValue 		= $.trim(value);
			var objectRules		= actions.getObjectRules(property,rowData);
			switch(type){
			case "text":
			case "number":
			case "date":
//				if(strimValue == '')  validateResult =  'inputted data must not empty';
				if(type=="number"&&typeof property !== 'string'){
					var basicRules	= actions.getBasicRules(property,objectRules);
					validateResult 	= actions.validateNumberWithRules(basicRules,strimValue);
				}
				break;
			}
			if(validateResult==null&&objectRules!=null&&typeof objectRules.advance == "object"){
				var enforceEditNote = (objectRules.advance.ENFORCE_EDIT_NOTE==true)||objectRules.advance.ENFORCE_EDIT_NOTE=="true";
				if(enforceEditNote){
					var auditNote	= prompt("Please input memo","reason for new value "+strimValue);
					if(auditNote!="" && auditNote!=null){
						actions.putModifiedData(tab,"AUDIT_NOTE-"+columnName,auditNote,rowData,"text");
					}
					else validateResult =  'Please input memo';
				}
			}
			return validateResult;
	    };
		$(td).editable(editable);
    	$(td).on("shown", function(e, editable) {
//    		  var val = editable.input.$input.val();
//    		  if(val.trim()=="")editable.input.$input.val('');
    		  if(type=="timepicker") $(".table-condensed thead").css("visibility","hidden");
//    		  $(".extension-buttons").css("display","none");
    		  $("#more_actions").html("");
    		  if(typeof editable == "undefined") return;
    		  if(type=="number") {
					$( editable.input.$input.get(0) ).closest( ".editable-container" ).css("float","right");
					if (actions.historyUrl){
					//						$(".extension-buttons").css("display","block");
						var hid ='eb_' +tab+"_"+rowData.DT_RowId+"_"+columnName;
						if( $('#'+hid).length ){
						}
						else{
							var extensionButton = $("<div class=\"extension-buttons\"><img src=\"/common/css/images/hist.png\" height=\"16\" class=\"editable-extension\"></div>");
							extensionButton.css("display","block");
							extensionButton.attr( 'id', hid);
							extensionButton.click(function(e){
								actions.extensionHandle(tab,columnName,rowData,false,successFunction,true);
							});
							$("#more_actions").append(extensionButton);
						}
					}
					
					val = rowData[columnName];
		    		val = Math.floor(val) == val && $.isNumeric(val)?Math.floor(val):val;
		    		val = val!=null?""+val:"";
		    		if(configuration.number.DECIMAL_MARK=='comma') val = val.replace('.',',')
					editable.input.$input.val(val);
    		  }
    		  editable.input.$input.get(0).select();
//    		  if(type=="timepicker") $(".table-condensed th").text("");
    	});
    	
    	$(td).on('hidden', function(e, reason) {
			var hid ='eb_' +tab+"_"+rowData.DT_RowId+"_"+columnName;
    		$("#" +hid).remove();
    	});
    	
    	$(td).on('save', function(e, params) {
    	    if(type=="number") {
    	    	value = params.newValue;
    	    	if(value!=null&&value!=""){
    	    		if(configuration.number.DECIMAL_MARK=='comma') value = parseFloat(value.replace(',','.'));
					else value = parseFloat(value);
    	    		var numberValue = value;
					if(configuration.number.DECIMAL_MARK=='comma') value = value.toLocaleString('de-DE');
					else value = value.toLocaleString("en-US");
					params.newValue = value;
					params.submitValue = numberValue;
				}
    	    	else {
//    	    		rowData[columnName]	= " ";
//    	    		params.newValue = " ";
//					params.submitValue = " ";
    	    	}
    	    }
    	});
	},
	addCellNumberRules  : function(td, property,newValue,rowData,originColor,phase="manual") {
		if(typeof newValue == "string") newValue = newValue.replace(",",".");
		newValue	= parseFloat(newValue);
		if(isNaN(newValue)) return;
		if(newValue==null||newValue==""||newValue==" ") return;
		if(phase=="loading"){
			var minValue = typeof(property.VALUE_MIN) !== "undefined"&&
			property.VALUE_MIN != null &&
			property.VALUE_MIN != ""?
			parseFloat(property.VALUE_MIN):-1*Number.MAX_VALUE;
			var maxValue = typeof(property.VALUE_MAX) !== "undefined"&&
					property.VALUE_MAX != null &&
					property.VALUE_MAX != ""?
					parseFloat(property.VALUE_MAX):Number.MAX_VALUE;
			
			if(minValue<maxValue && (newValue < 	minValue || newValue > maxValue)) {
				$(td).css('background-color', 'red');
				return;
			}
		}
		var minWarningValue = typeof(property.VALUE_WARNING_MIN) !== "undefined"&&
		property.VALUE_WARNING_MIN != null &&
		property.VALUE_WARNING_MIN != ""?
		parseFloat(property.VALUE_WARNING_MIN):-1*Number.MAX_VALUE;
		var maxWarningValue = typeof(property.VALUE_WARNING_MAX) !== "undefined"&&
		property.VALUE_WARNING_MAX != null &&
		property.VALUE_WARNING_MAX != ""?
		parseFloat(property.VALUE_WARNING_MAX):Number.MAX_VALUE;
		if(newValue <= minWarningValue || newValue >= maxWarningValue) $(td).css('background-color', 'yellow');
		else {
			$(td).css('background-color', originColor);
			var rangePercent = typeof(property.RANGE_PERCENT) !== "undefined"&&
								property.RANGE_PERCENT != null &&
								property.RANGE_PERCENT != ""&&
								typeof property.LAST_VALUES == "object"&&
								typeof property.LAST_VALUES[rowData.DT_RowId] == "object"
								?parseFloat(property.RANGE_PERCENT):false;
			if(rangePercent!=false && rangePercent>0){
				var lastValue		= property.LAST_VALUES[rowData.DT_RowId][property.data];
				lastValue		= parseFloat(lastValue);
				lastValue		= !isNaN(lastValue)?lastValue:0;
				maxcompareValue	= (rangePercent+100)*lastValue/100;
				mincompareValue	= (-rangePercent+100)*lastValue/100;
				if(lastValue>0&&(newValue>maxcompareValue||newValue<mincompareValue)) $(td).css('color', 'blue');
				else $(td).css('color', '');
			}
		}
	},

	getBasicRules  : function(property,objectRules) {
		var rules	= (typeof(objectRules) == "object" &&(objectRules.OVERWRITE==true || objectRules.OVERWRITE=='true') 
						&& typeof(objectRules.basic) =="object")?
						jQuery.extend(jQuery.extend({},property), objectRules.basic):property;

		return rules;
	},
	
	getEditSuccessfn  : function(property,tab, td, rowData, columnName,collection,type) {
		var originColor		= $(td).css('background-color');
		return function(response, newValue) {
        	rowData = actions.putModifiedData(tab,columnName,newValue,rowData,type);
        	rowData[columnName] = newValue;
        	var table = $('#table_'+tab).DataTable();
        	$(td).css('color', 'black');
        	if(type=='number'){
        		var objectRules		= actions.getObjectRules(property,rowData);
        		var basicRules		= actions.getBasicRules(property,objectRules);
        		actions.addCellNumberRules(td,basicRules,newValue,rowData,originColor,"manual");
        	}
			table.row( '#'+rowData['DT_RowId'] ).data(rowData);
			table.columns().footer().draw(); 
//        	table.draw(false);
        	//dependence columns
        	actions.dominoColumns(columnName,newValue,tab,rowData,collection,table,td);
        	 /* var tabindex = $(this).attr('tabindex');
            $('[tabindex=' + (tabindex +1)+ ']').focus(); */
	    };
	},
	extensionHandle			:	function(tab,columnName,rowData,limit,successFunction){
	},
	deleteRowFunction		:	function(table,rowData,tab){
		var id = rowData['DT_RowId'];
		var isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		var rowData 	= table.row('#'+id).data();
		var recordData 	= actions.deleteData;
   		if (!(tab in recordData)) recordData[tab] = [];
    	//remove in postdata
    	var eData = recordData[tab];
    	if(isAdding) {
	    	
	   	}
    	else{
    		deleteObject = actions.initDeleteObject(tab,id,rowData);
	    	eData.push(deleteObject);
    	}
    	var editedData = actions.editedData[tab];
    	if(editedData!=null){
    		var result = $.grep(editedData, function(e){ 
           	 	return e[actions.type.keyField] == rowData[actions.type.keyField];
            });
		    if (result.length > 0) editedData.splice( $.inArray(result[0], editedData), 1 );
    	}
        	//remove on table
    	table.row('#'+id).remove().draw( false );
	},
	createdFirstCellColumnByTable : function(table,rowData,td,tab){
		var id = rowData['DT_RowId'];
		var deleteFunction = function(){
			actions.deleteRowFunction(table,rowData,tab);
		}
//		$(td).find('#delete_row_'+id).click(deleteFunction);
		table.$('#delete_row_'+id).click(deleteFunction);

		var editFunction = function(e){
			e.preventDefault();
//			var r = table.fnGetPosition(td)[0];
//		    var rowData = table.api().data()[ r];
		    var rowData = table.row('#'+id).data();
		    editBox.editRow(id,rowData);
		};
//		$(td).find('#edit_row_'+id).click(editFunction);
		table.$('#edit_row_'+id).click(editFunction);
		if(typeof(actions.addMoreHandle) == "function")actions.addMoreHandle(table,rowData,td,tab);
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
	getKeyFieldSet : function(tab){
		if(typeof(actions.type.idName) == "function"){
			return actions.type.idName(tab);
		}
		return actions.type.idName;
	},
	putModifiedData : function(tab,columnName,newValue,rowData,type){
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
    	if (newValue!=null) {
    		if (newValue.constructor.name == "Date") {
        		newValue = actions.getTimeValueBy(newValue,columnName,tab);
    		}
    		else if(type == "number") {
        		newValue = parseFloat(newValue.replace(',','.'));
    		}
		}
    	if (result.length == 0) {
        	var editedData = {};
        	idName = actions.getKeyFieldSet(tab);
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
	isDisplayOriginValue : function(property,rowData){
		var isKeep = false;
		var objectExtension	= property.OBJECT_EXTENSION;
		if(objectExtension!=null&&objectExtension!=""){
			var objects = $.parseJSON(objectExtension);
			var objectId = rowData[actions.type.idName[0]];
			var extension = objects[objectId];
			if(typeof(extension) == "object"){
				isKeep = typeof(extension.advance) == "object"
						&&(extension.advance.KEEP_DISPLAY_VALUE==true||extension.advance.KEEP_DISPLAY_VALUE=="true");
				/*var result = $.grep(extension, function(e){
    				return e == "KEEP_DISPLAY_VALUE";
     			});
     			isKeep = typeof(result) !== "undefined" && result.length > 0;*/
			}
		}
		return isKeep;
	},
	getCellType : function(data,type,cindex){
		type = actions.extraDataSetColumns.hasOwnProperty(data.properties[cindex].data)?'select':type;
		return type;
	},
	
	getObjectRules : function(property,rowData){
		var rules;
		var objectExtension	= property.OBJECT_EXTENSION;
		if(objectExtension!=null&&objectExtension!=""){
			var objects = $.parseJSON(objectExtension);
			var objectId = rowData[actions.type.idName[0]];
			rules = objects[objectId];
		}
		return rules;
	},
	
	createCommonCell	: function(td,data,type,property,rowData){
		colName 			= property.data;
		$(td).addClass( "contenBoxBackground");
		$(td).addClass( "cell"+type );
		$(td).addClass( colName );
		var isEdittable = !data.locked&&actions.isEditable(property,rowData,data.rights);
		if(isEdittable) $(td).addClass( "editInline" );
		return isEdittable;
	},
	
	getCellProperty : function(data,tab,type,cindex){
		var cell = {"targets"	: cindex};
		type = actions.getCellType(data,type,cindex);
		var property 		= data.properties[cindex];
		const columnName 	= property.data;
		if(typeof type == "function") {
			cell["createdCell"] = type;
			return cell;
		}
		cell["createdCell"] 	= function (td, cellData, rowData, row, col) {
			/*rowData.DT_RowId	= (typeof rowData.DT_RowId) == "undefined" || rowData.DT_RowId ==null || rowData.DT_RowId ==""?
									Math.random().toString(36).substring(10):
									rowData.DT_RowId;*/
									
			var property 		= data.properties[col];
			var isEdittable		= actions.createCommonCell(td,data,type,property,rowData);
			if (type=='checkbox') {
				/*checked 		= cellData?'checked':'';
 				var disabled 	= isEdittable?"":"disabled"; 
				var checkBox	= $('<input '+disabled+' class="cellCheckboxInput" type="checkbox" value="'+cellData+'"size="15" '+checked+'>');
				var checkBox	= $('<div  class="checkboxCell" ><input '+disabled+' class="cellCheckboxInput" type="checkbox" value="'+cellData+'"size="15" '+checked+'></div>');
				checkBox.change(function(){
// 					fn = actions.getEditSuccessfn(property,tab,td, rowData, columnName,collection);
 					var val = $(this).is(':checked');
// 					fn(null,val?1:0);
 					rowData.columnName	= val;
 				});
 				$(td).append(checkBox);*/
				$(td).click(function(){
					var val = rowData[columnName]==""?false:rowData[columnName];
					val		= !val;
	 				var fn = actions.getEditSuccessfn(property,tab,td, rowData, columnName,collection);
 					fn(null,val?1:0);
 				});
 				return;
			};

			var objectRules		= actions.getObjectRules(property,rowData);
			if(objectRules!=null&&typeof(objectRules) == "object"&& typeof objectRules.advance=="object"){
				//TODO more ruless
				$(td).css("background-color","#"+objectRules.advance.COLOR);
			}
			
 			if(isEdittable){
 				$(td).addClass( "editInline" );
 	        	var table = $('#table_'+tab).DataTable();
 	        	collection = null;
 	        	if(type=='select'){
 	        		collection = actions.getExtraDataSetColumn(data,cindex,rowData);
 	        	}
 				actions.applyEditable(tab,type,td, cellData, rowData, property,collection);
 			}
 			else if (type=='number'&&actions.historyUrl) {
 				$(td).click(function(e){
 					var hid ='eb_' +tab+"_"+rowData.DT_RowId+"_"+columnName;
 					if( $('#'+hid).length ){
 					}
 					else {
 						$("#more_actions").html("");
 						var extensionButton = $("<div class=\"extension-buttons\"><img src=\"/common/css/images/hist.png\" height=\"16\" class=\"editable-extension\"></div>");
 						extensionButton.css("display","block");
 						extensionButton.attr( 'id', hid);
 						extensionButton.attr('tabindex',-1);
 						extensionButton.blur(function() {
 							var hid ='eb_' +tab+"_"+rowData.DT_RowId+"_"+columnName;
 							$("#" +hid).remove();
//				 		    		$(td ).removeAttr( "tabindex" );
 						});
 						extensionButton.click(function(e){
 							actions.extensionHandle(tab,columnName,rowData,false,null,true);
//				 		    		$("#" +hid).remove();
 						});
 						$("#more_actions").append(extensionButton);
 						extensionButton.focus();
 					}
 				});
 			}
 			if(type=='number'){
        		var basicRules		= actions.getBasicRules(property,objectRules);
        		var originColor		= $(td).css('background-color');
        		actions.addCellNumberRules(td,basicRules,cellData,rowData,originColor,"loading");
        	}
		};

		switch(type){
		case "text":
		case "color":
			if(columnName=='UOM'){
				cell["render"] = function ( data2, type2, row ) {
					if (data2==null||data2=='') return "&nbsp";
					var rendered = data2;
					if(data2==null){
						rendered = row.DEFAULT_UOM;
					}
					return rendered;
				};
			}
			else cell["render"] = function ( data2, type2, row ) {
									if (data2==null||data2=='') return "&nbsp";
									return data2;
								};
	    	break;
		case "number":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null||data2=='') return "&nbsp";
								value = actions.getNumberRender(columnName,data,data2, type2, row);
								if(value==null){
									value = data2;
									if(data2!=null&&data2!=''){
										var pvalue = parseFloat(data2);
										if(isNaN(pvalue)) return '';
										value = Math.round(pvalue * 100) / 100;
										if(actions.isDisplayOriginValue(property,row))  value = pvalue;
									}
								}
								if(value!=null){
									if(configuration.number.DECIMAL_MARK=='comma') value = value.toLocaleString('de-DE');
									else value = value.toLocaleString("en-US"); 
								}
								return value;
							};
	    	break;
		case "date":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null||data2=='') return "&nbsp";
								return actions.renderDateFormat(data2,type2,row);
							};
	    	break;
		case "datetimepicker":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null||data2=='') return "&nbsp";
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
								if (data2==null||data2=='') return "&nbsp";
								if (data2.constructor.name == "Date") { 
									return moment.utc(data2).format(configuration.time.TIME_FORMAT);
//									return moment(data2).format("hh:mm A");
								}
								return moment.utc(data2,configuration.time.TIME_FORMAT_UTC).format(configuration.time.TIME_FORMAT);
//								return moment(data2,"hh:mm:ss").format("hh:mm A");
							};
	    	break;
		case "checkbox":
//			cell["className"] = 'select-checkbox';

			cell["render"] = function ( data2, type2, row ) {
//								if (data2==null||data2=='') return "&nbsp";
								checked = data2&&data2=="1"||data2==true||data2=="true"?'checked':'';
				 				var disabled = data.locked||!(actions.isEditable(data.properties[cindex],row,data.rights));
				 				disabled = disabled?"disabled":''; 
								return '<div  class="checkboxCell" ><input '+disabled+' class="cellCheckboxInput" type="checkbox" value="'+data2+'"size="15" '+checked+'></div>';
							};
	    	break;
		case "select":
			cell["render"] = function ( data2, type2, row ) {
					if (data2==null||data2=='') return "&nbsp";
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
	    	
		case "EVENT":
			cell["render"] = function ( data2, type2, row ) {
				return typeof data2=="object"&&typeof data2.FREQUENCEMODE != "undefined"? data2.FREQUENCEMODE:"config event";
			};
	    	break;

		}
		return cell;
	},
	createdFirstCellColumn : function (td, cellData, rowData, row, col) {
		$(td).css('z-index','1');
	},
	getRenderFirsColumnFn : function (tab) {
		return actions.renderFirsColumn;
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
	enableUpdateView : function(tab,postData){
		return true;
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
		headerOffset = $('#tabs').offset();
		var bonus = $('#ebTabHeader').is(':visible')?100:66;
		hhh = $(document).height() - (headerOffset?(headerOffset.top):0) - $('#ebTabHeader').outerHeight() - $('#ebFooter').outerHeight() - bonus;
		tHeight = ""+hhh+'px';
		return tHeight;
	},
	getTableOption: function(data,tab){
		return {tableOption :{searching: true},
				invisible:[]};
		
	},
	addClass2Header: function(table){
		var columns = table.settings()[0].aoColumns;
		$.each(columns, function( index, column ) {
			var header = table.columns(index).header();
        	var columnName = column.data;
        	$(header).addClass(columnName);
	   	});
		
	},
	getUomCollection: function(collection,columnName,rowData){
		return collection;
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
	            	vl['value']	=vl['ID'];
	            	vl['text']	=vl['NAME'];
	            });
	            uoms[index]["createdCell"] = function (td, cellData, rowData, row, col) {
	            	var property = data.properties[col];
	            	columnName = property.data;
	 				$(td).addClass( columnName );
	            	if(!data.locked&&actions.isEditable(data.properties[col],rowData,data.rights)&&actions.notUniqueValue(uoms[index],rowData)){
		 				$(td).addClass( "editInline" );
		 				$(td).editable({
			        	    type: 'select',
			        	    title: 'edit',
			        	    emptytext: '',
			        	    value:cellData,
			        	    showbuttons:false,
			        	    source: actions.getUomCollection(collection,columnName,rowData),
			        	    success: actions.getEditSuccessfn(property,tab,td, rowData, columnName,collection),
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
		if(!autoWidth) {
//			$('#table_'+tab).css('width',(tblWdth+20)+'px');
			$('#table_'+tab).css('min-width',(tblWdth+20)+'px');
//			$('#container_'+tab).css('min-width',(tblWdth+20)+'px');
			autoWidth = tblWdth < $(window).width()-30;
		}
//		if(!autoWidth && tblWdth>0) $('#table_'+tab).css('width',(tblWdth)+'px');

		tHeight = actions.getTableHeight(tab);
		option = {data: data.dataSet,
		          columns: data.properties,
		          destroy: true,
		          "columnDefs": uoms,
		          "scrollX": true,
//		         "autoWidth": false,
		         "autoWidth": autoWidth,
//		       	"scrollY":        "37vh",
//		         "scrollY":        "250px",
		       	scrollY:        tHeight,
//		                "scrollCollapse": true,
				"paging":         false,
				"dom": 'rt<"#toolbar_'+tab+'">p<"bottom"i><"bottom"f><"clear">',
				drawCallback	: function ( settings ) { 
			        var table = $('#table_'+tab).DataTable();
			        $('#table_'+tab+' tbody').on( 'click', 'tr', function () {
		                table.$('tr.selected').removeClass('selected');
			            if ( $(this).hasClass('selected') ) {
//			                 $(this).removeClass('selected');
			            }
			            else {
			                $(this).addClass('selected');
			            }
			        } );
			        
			        actions.addClass2Header(table);
			    },
			    language: {
		            "info": "Showing _TOTAL_ entries",
		        },
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
	 			if(options.tableOption.hasOwnProperty('emptyTable')
	 					&&options.tableOption.emptyTable) {
	 				 $('#container_'+tab ).html('<table border="0" cellpadding="3" id="table_'+tab +'" class="fixedtable nowrap display"></table>');
	 			}
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
	