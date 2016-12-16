<?php
?>
@extends('group.production',['prefix'			=> "secondary_",])

@section('secondary_filter_extra') 
	<div class="action_filter">
		<input type="button" value="Save" id="secondary_buttonLoadData" name="B33"
				onClick="editBox.finishSelectingObjects(true)" style="width: 85px; height: 26px;">
	</div>
	
	<script type='text/javascript'>
		editBox.buildFilterData = function(){
			var dataStore = {};
			var selects = $("#ebFilters_ select");
			selects.each(function(index, element) {
				dataStore[element.name] = element.value;
			});
			return dataStore;
		}
		
		editBox.finishSelectingObjects = function(close){
			var dataStore 	= editBox.buildFilterData();
			var resultText 	= editBox.buildFilterText();
			if(typeof editBox.editSelectedObjects == "function") 
				editBox.editSelectedObjects(dataStore,resultText);
			editBox.closeEditWindow(close);
			console.log("finishSelectingObjects done");
		}
	</script>
@stop
