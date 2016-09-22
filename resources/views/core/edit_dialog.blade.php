@extends('core.float_dialog')

@section('editBoxParams')
<script>
	editBox['initSavingDetailData'] = function(editId,success) {
		var editData = {id:editId};
		$.each(editBox.fields, function( index, value ) {
			editData[value] = actions.editedData[value];
   		 });
  		 return editData;
	};

    editBox['notValidatedData'] = function(editId) {
        return false;
    };
    	
    editBox.saveDetail = function(editId,success) {
    	if(editId&&editId!=null){
    		isEmpty = true;
    		$.each(editBox.fields, function( index, value ) {
    			isEmpty= isEmpty&&(!actions.editedData.hasOwnProperty(value))&&(!actions.deleteData.hasOwnProperty(value));
       		 });
      		if(isEmpty) {
        		alert('no change to commit');
        		return;
        	}
    		var editData = editBox.initSavingDetailData(editId);

    		if(!editData) {
        		alert('no change to commit');
        		return;
        	}
        	if(editBox.notValidatedData(editId)) return;
    		showWaiting();
    		$.ajax({
    			url: editBox.saveUrl,
    			type: "post",
    			data: editData,
    			success:function(data){
    				hideWaiting();
    				console.log ( "success saveDetail "+JSON.stringify(data) );
//     				alert("success");
    				if(editBox.enableRefresh) actions.doLoad(true);
    				close = true
    				if (typeof(success) == "function") {
    					close = success(data);
					}
    				editBox.closeEditWindow(close);
    			},
    			error: function(data) {
    				hideWaiting();
    				alert("error!");
    				console.log ( "error saveDetail ");
    			}
    		});
    	}
    	else{
    		alert('no change to commit');
    	}
    }
</script>
@stop