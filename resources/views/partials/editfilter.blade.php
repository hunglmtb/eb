<?php
?>
@extends('group.production')
@section('action_extra') 
	<div class="action_filter">
		<input type="button" value="Done" id="buttonLoadData" name="B33"
				onClick="finishSelectingObjects()" style="width: 85px; height: 26px;foat:left;">
	</div>
	
	<script type='text/javascript'>
		function finishSelectingObjects(){
			var dataStore = {};
			var texts = {};
			var selects = $("#ebFilters_ select:visible");
			selects.each(function(index, element) {
				dataStore[element.name] = element.value;
				texts[element.name]		= $("#"+element.id+" option:selected").text();
			});
			if(typeof editBox.renderOutputText == "function")
				resultText	= editBox.renderOutputText(texts);
			else 
				resultText	= JSON.stringify(texts);
			if(typeof editBox.editSelectedObjects == "function") 
				editBox.editSelectedObjects(dataStore,resultText);
			editBox.closeEditWindow(true);
		}
	</script>
@stop
