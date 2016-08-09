@extends('core.float_dialog')

@section('editBoxParams')
<script>
    editBox.saveDetail = function(editId,success) {
    	if(editId&&editId!=null){
    		isEmpty = true;
    		$.each(editBox.fields, function( index, value ) {
    			isEmpty= isEmpty&&(!actions.editedData.hasOwnProperty(value));
       		 });
      		if(isEmpty) {
        		alert('data is empty');
        		return;
        	}
    		showWaiting();
    		editData = {id:editId};
    		$.each(editBox.fields, function( index, value ) {
    			editData[value] = actions.editedData[value];
       		 });
      		 
    		$.ajax({
    			url: editBox.saveUrl,
    			type: "post",
    			data: editData,
    			success:function(data){
    				hideWaiting();
    				console.log ( "success saveEditGroup "+JSON.stringify(data) );
    				alert("success");
    				if(editBox.enableRefresh) actions.doLoad(true);
    				close = true
    				if (typeof(success) == "function") {
    					close = success(data);
					}
    				editBox.closeEditWindow(close);
    			},
    			error: function(data) {
    				hideWaiting();
    				alert("error saveEditGroup ");
    				console.log ( "error saveEditGroup ");
    			}
    		});
    	}
    	else{
    		alert('data is empty');
    	}
    }
</script>
@stop