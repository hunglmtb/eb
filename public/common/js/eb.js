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


var actions = {
		
	loadUrl : false,
	saveUrl : false,
	readyToLoad : false,
	loadedData : {},
	loadPostParams : null,
	initData : false,
	initSaveData :false,
	editedData : {},
	objectIds : [],
	loadSuccess : function(data){alert("success");},
	loadError : function(data){alert("error");},
	shouldLoad : function(data){return false;},
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
					params[entry.id] = $('#'+entry.id).val();
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
			params['editedData'] = actions.editedData;
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
			$.ajax({
				url: this.loadUrl,
				type: "post",
				data: actions.loadParams(reLoadParams),
				success:function(data){
					if(data!=null&&data.hasOwnProperty('objectIds')){
						actions.objectIds = data.objectIds;
					}
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
					alert(JSON.stringify(data.responseText));
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
					if ($('#'+entry.id).val()!=postData[entry.id]) {
						$('#'+entry.id).val(postData[entry.id]).trigger('change');
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
					alert(JSON.stringify(data.responseText));
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
	}
}


