@extends('partials.editfilter')
@section('action_extra')
@parent
	<table border="0" class="clearBoth" style="">
		<tr>
			<td>
				<b>Y axis: Position </b>
				<select id="edit_cboYPos" style="width: auto">
					<option value="L">Left</option>
					<option value="R">Right</option>
				</select>
			</td>
			<td>
				<b> Text </b>
				<input name="txt_y_unit" id="edit_txt_y_unit" value="">
			</td>
		</tr>
	</table>
	
	<script type='text/javascript'>
		var oBuildFilterData		= editBox.buildFilterData;
		editBox.buildFilterData 	= function(){
			var dataStore 			= oBuildFilterData();
			dataStore.cboYPos 		= $("#edit_cboYPos").val();
			dataStore.txt_y_unit 	= $("#edit_txt_y_unit").val();
			return dataStore;
		}

		editBox.updateExtraFilterData = function(dataStore){
			$("#edit_cboYPos").val(dataStore.cboYPos);
			$("#edit_txt_y_unit").val(dataStore.txt_y_unit);
		}
	</script>
@stop
