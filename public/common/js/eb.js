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

var registerOnChange = function(id, dependentIds) {
	$('#'+id).change(function(e){
		enableSelect(dependentIds,'disabled');
		$.ajax({
			url: '/code/list',
			type: "post",
			data: {type:id,dependences:dependentIds,value:$(this).val()},
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
		/*case 6:
			return "_timepicker";*/
	}
	return "text";
};


var actions = {
		
	loadUrl : false,
	saveUrl : false,
	readyToLoad : false,
	loadedData : {},
	loadPostParams : null,
	initData : false,
	initSaveData :false,
	editedData : {},
	deleteData : {},
	insertingData : {},
	objectIds : [],
	extraDataSetColumns : {},
	loadSuccess : function(data){alert("success");},
	loadError : function(data){
					alert(JSON.stringify(data.responseText));
				},
	shouldLoad : function(data){return false;},
	afterGotSavedData : function(data,table,key){},
	dominoColumns : function(columnName,newValue,tab,rowData,collection){},
	loadNeighbor: function (){
		if (actions.shouldLoad()) {
			actions.doLoad(false);
		}
		else{
			var activeTabID = getActiveTabID();
			var postData = actions.loadedData[activeTabID];
			actions.updateView(postData);
		}
	},
	loadParams : function (reLoadParams){
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
	
	loadSaveParams : function (reLoadParams){
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
	
	doLoad : function (reLoadParams){
		if (this.loadUrl) {
			console.log ( "doLoad url: "+this.loadUrl );
			actions.readyToLoad = true;
			showWaiting();
			actions.editedData = {};
			$.ajax({
				url: this.loadUrl,
				type: "post",
				data: actions.loadParams(reLoadParams),
				success:function(data){
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
					hideWaiting();
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
		else{
			alert("init load params");
			return false;
		}
	},
	updateView : function(postData){
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
	/*loadData : function (data, valueDefault, columnName, width){
		alert("doSave"+this.url);
		return true;
	},*/
	doSave : function (reLoadParams){
		if (this.saveUrl) {
			console.log ( "doLoad url: "+this.saveUrl );
//			actions.readyToLoad = true;
			showWaiting();
			$.ajax({
				url: this.saveUrl,
				type: "post",
				data: actions.loadSaveParams(reLoadParams),
				success:function(data){
					if (typeof(actions.saveSuccess) == "function") {
						actions.saveSuccess(data);
					}
					else{
						alert("save success");
					}
					hideWaiting();
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
		else{
			alert("save url not initial");
			return false;
		}
	},
	isEditable : function (row,rowData,rights){
		var rs = row.DATA_METHOD==1||row.DATA_METHOD=='1';
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
	preDataTable : function (dataset){
		return dataset;
	},
	afterDataTable : function (table,tab){
		$("#toolbar_"+tab).html('');
	},
	renderFirsColumn : function ( data, type, rowData ) {
		var html = data;
		if(rowData.hasOwnProperty('PHASE_CODE')){
			html += "<div class='phase "+rowData['PHASE_CODE']+"'>"+
					rowData['PHASE_NAME']+"</div>";
		}
		else if(rowData.hasOwnProperty('PHASE_NAME')){
			html += "<div class='phase "+rowData['PHASE_NAME']+"'>"+
			rowData['PHASE_NAME']+"</div>";
		}
		if(rowData.hasOwnProperty('STATUS_NAME')){
			html +="<span class='eustatus'>"+rowData['STATUS_NAME']+"</span>";
		}
		if(rowData.hasOwnProperty('TYPE_CODE')){
			html +="<span class='eventType'>"+rowData['TYPE_CODE']+"</span>";
		}
		return html;
	},
	applyEditable : function (tab,type,td, cellData, rowData, columnName,collection){
		var  editable = {
	    	    title: 'edit',
	    	    emptytext: '',
	    	    showbuttons:false,
	    	    success: actions.getEditSuccessfn(tab,td, rowData, columnName,collection),
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
				editable['format'] = 'mm/dd/yyyy';
				editable['viewformat'] = 'mm/dd/yyyy';
			}
	    	break;
		case "datetimepicker":
			editable['onblur'] = 'submit';
			editable['type'] = 'datetime';
			editable['format'] = 'mm/dd/yyyy hh:ii';
			editable['viewformat'] = 'mm/dd/yyyy hh:ii';
			editable['datetimepicker'] 	= 	{
								          		weekStart: 1,
								          		minuteStep :10
								            };
	    	break;
		case "select":
			editable['type'] = type;
			editable['source'] = collection;
			editable['value'] = cellData==null?(collection[0]!=null?collection[0].ID:0):cellData;
			$(td).editable(editable);
			return;
	    	break;
		}	
		$(td).editable(editable);
    	$(td).on("shown", function(e, editable) {
    		  editable.input.$input.get(0).select();
    	});
	},
	getEditSuccessfn  : function(tab, td, rowData, columnName,collection) {
		return function(response, newValue) {
			var table = $('#table_'+tab).DataTable();
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
//        	var columnName = table.settings()[0].aoColumns[col].data;
        	if (newValue!=null&&newValue.constructor.name == "Date") { 
        		newValue = moment(newValue).format("YYYY-MM-DD HH:mm:ss");
			}
        	if (result.length == 0) {
	        	var editedData = {};
	        	 $.each(actions.type.idName, function( i, vl ) {
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
        	rowData[columnName] = newValue;
			table.row( '#'+rowData['DT_RowId'] ).data(rowData);
        	$(td).css('color', 'red');
        	table.draw(false);
        	//dependence columns
        	actions.dominoColumns(columnName,newValue,tab,rowData,collection);
        	 /* var tabindex = $(this).attr('tabindex');
            $('[tabindex=' + (tabindex +1)+ ']').focus(); */
	    };
	},
	getCellType : function(data,type,cindex){
		type = actions.extraDataSetColumns.hasOwnProperty(data.properties[cindex].data)?'select':type;
		return type;
	},
	getCellProperty : function(data,tab,type,cindex){
		var cell = {"targets"	: cindex};
		type = actions.getCellType(data,type,cindex);
		if (type!='checkbox') {
			cell["createdCell"] = function (td, cellData, rowData, row, col) {
		 			if(!data.locked&&actions.isEditable(data.properties[col],rowData,data.rights)){
		 				$(td).addClass( "editInline" );
		 				columnName = data.properties[col].data;
		 				$(td).addClass( columnName );
		 	        	var table = $('#table_'+tab).DataTable();
		 	        	collection = null;
		 	        	if(type=='select'){
		 	        		column = actions.extraDataSetColumns[data.properties[cindex].data];
		 	        		collectionColumn = rowData[column];
		 	        		collection = data.extraDataSet[collectionColumn];
		 	        	}
		 				actions.applyEditable(tab,type,td, cellData, rowData, columnName,collection);
		 			}
			    };
		}
		switch(type){
		case "text":
			columnName = data.properties[cindex].data;
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
								var rendered = data2;
								if(data2!=null){
									rendered = parseFloat(data2).toFixed(2);
								}
								return rendered;
							};
	    	break;
		case "date":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null) { 
									return "";
								}
								if (data2.constructor.name == "Date") { 
									return moment(data2).format("MM/DD/YYYY");
								}
								return moment(data2,"YYYY-MM-DD").format("MM/DD/YYYY");
							};
	    	break;
		case "datetimepicker":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null) { 
									return "";
								}
								if (data2.constructor.name == "Date") { 
									return moment(data2).format("MM/DD/YYYY HH:mm");
								}
								return moment(data2,"YYYY-MM-DD HH:mm").format("MM/DD/YYYY HH:mm");
							};
	    	break;
		case "checkbox":
//			cell["className"] = 'select-checkbox';
			cell["render"] = function ( data2, type2, row ) {
								return '<input style="width:20px; " type="checkbox" value="'+data2+'" class="CTV204 " size="15">';
							};
	    	break;
		case "select":
			cell["render"] = function ( data2, type2, row ) {
					column = actions.extraDataSetColumns[data.properties[cindex].data];
		     		collectionColumn = row[column];
		     		collection = data.extraDataSet[collectionColumn];
		     		if(collection!=null){
		     			var result = $.grep(collection, function(e){ 
		     				return e['ID'] == data2;
		     			});
		     			if(typeof(result) !== "undefined" && typeof(result[0]) !== "undefined" &&result[0].hasOwnProperty('NAME')){
		     				return result[0]['NAME'];
		     			}
		     		}
					return data2;
				};
	    	break;
		}
		return cell;
	},
	createdFirstCellColumn : function (td, cellData, rowData, row, col) {},
	initTableOption : function (tab,data,options,renderFirsColumn,createdFirstCellColumn){
		var exclude = [0];
		if(typeof(data.uoms) == "undefined"||data.uoms==null){
			data.uoms = [];
		}
		var uoms = data.uoms;
		var invisible = options!=null&&(typeof(options.invisible) !== "undefined"&&options.invisible!=null)?options.invisible:null;
		if(typeof(data.extraDataSet) !== "undefined"&&data.extraDataSet!=null){
			$.each(data.extraDataSet, function( index, value ) {
				if(value!=null){
					var collection = value;
		            $.each(collection, function( i, vl ) {
		            	vl['value']=vl['ID'];
		            	vl['text']=vl['NAME'];
		            });
				}
			});
		}
		if(typeof(uoms) !== "undefined"&&uoms!=null){
			$.each(uoms, function( index, value ) {
				exclude.push(uoms[index]["targets"]);
				if(value!=null&&value.hasOwnProperty('render')) return;
				
				var collection = value['data'];
				uoms[index]["render"] = function ( data, type, row ) {
								                var result = $.grep(collection, function(e){ 
									                return e['ID'] == data;
									                });
												if(typeof(result) !== "undefined" && typeof(result[0]) !== "undefined" &&result[0].hasOwnProperty('NAME')){
			                						return value['COLUMN_NAME']=="ALLOC_TYPE"?result[0]['NAME']:result[0]['NAME'];
												}
												return data;
									                
	                					};
	            $.each(collection, function( i, vl ) {
	            	vl['value']=vl['ID'];
	            	vl['text']=vl['NAME'];
	            });
	            uoms[index]["createdCell"] = function (td, cellData, rowData, row, col) {
	            	if(!data.locked&&actions.isEditable(data.properties[col],rowData,data.rights)){
		 				$(td).addClass( "editInline" );
		 				columnName = data.properties[col].data;
		 				$(td).addClass( columnName );
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
			});
		}

		var original = Array.apply(null, Array(data.properties.length)).map(function (_, i) {return i;});
		var finalArray = $(original).not(exclude).get();

		$.each(finalArray, function( i, cindex ) {
			var type = typetoclass(data.properties[cindex].INPUT_TYPE);
			var cell = actions.getCellProperty(data,tab,type,cindex);
			if(invisible!=null&&$.inArray(data.properties[cindex].data, invisible)>=0){
				cell['visible']=false;
			}
    		uoms.push(cell);
        });
		
		var phase = {"targets": 0,
					"render": renderFirsColumn,
		  			};
		if(createdFirstCellColumn!=null) phase["createdCell"] = createdFirstCellColumn;
		uoms.push(phase);
		
		var autoWidth = false;
		if( options!=null&&
				(typeof(options.tableOption) !== "undefined"&&
						options.tableOption!=null)&&
						(typeof(options.tableOption.autoWidth) !== "undefined"&&
								options.tableOption.autoWidth!=null)){
			autoWidth = options.tableOption.autoWidth;
		}
		
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
			}
        });
		
		if(!autoWidth) $('#table_'+tab).css('width',(tblWdth)+'px');
		
		option = {data: data.dataSet,
		          columns: data.properties,
		          destroy: true,
		          "columnDefs": uoms,
		          "scrollX": true,
		         "autoWidth": autoWidth,
		       	"scrollY":        "270px",
//		                "scrollCollapse": true,
				"paging":         false,
				"dom": '<"#toolbar_'+tab+'">frtip',
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
		
		var tbl = $('#table_'+tab).DataTable(option);
		return tbl;
	}
}


	