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

var actions = {
		
	loadUrl : false,
	initData : false,
	loadParams : function (){
		var params = {};
		for (var key in javascriptFilterGroups) {
			filterGroup = javascriptFilterGroups[key];
			for (var jkey in filterGroup) {
				entry = filterGroup[jkey];
				params[entry.id] = $('#'+entry.id).val();
			}
		}
		if (typeof(actions.initData) == "function") {
			var extras = actions.initData();
			if (extras) {
				jQuery.extend(params, extras);
			}
		}
		return params;
	},
	loadSuccess : function(data){alert("success");},
	loadError : function(data){alert("error");},

	doLoad : function (){
		if (this.loadUrl) {
			console.log ( "doLoad url: "+this.loadUrl );
			$.ajax({
				url: this.loadUrl,
				type: "post",
				data: this.loadParams(),
				success:function(data){
					if (typeof(actions.loadSuccess) == "function") {
						actions.loadSuccess(data);
					}
					else{
						alert("load success");
					}
				},
				error: function(data) {
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
	/*loadData : function (data, valueDefault, columnName, width){
		alert("doSave"+this.url);
		return true;
	},*/
	doSave : function (data, valueDefault, columnName, width){
		alert("doSave"+this.url);
		return true;
	}
}


