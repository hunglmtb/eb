@extends('core.float_dialog')

@section('editBoxParams')
<script>
	/* editBox.preSendingRequest = function() {
		editDataPosting = editBox.initExtraPostData(id,rowData);
		return editDataPosting;
	} */
    editBox.saveDetail = function(editId) {
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
    				console.log ( "success saveEditGroup "+JSON.stringify(data) );
    				alert(JSON.stringify(data));
    				hideWaiting();
    				editBox.closeEditWindow();
    				if(editBox.enableRefresh) actions.doLoad(true);
    			},
    			error: function(data) {
    				hideWaiting();
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