@extends('partials.editfilter')
@section($prefix.'action_extra')
@parent
	<div class="product_filter" style="width: 97%;">
		<table border="0" class="clearBoth" style="width: inherit;">
			<tr>
				<td>
					<b>Operation</b> 
					<select id="cboOperation" style="width: auto;">
						<option value='+'>+</option><option value='-'>-</option><option value='*'>*</option><option value='/'>/</option>
					</select>
					<input id="txtConstant" type='text' class='_numeric' style='width:80px'>
				</td>
				<td align="right" colspan="1">
					<button id="updateFilterBtn" class="myButton"onclick="editBox.finishSelectingObjects(false)" style="width: 61px;display:none">Update</button>
					<button id="addFilterBtn" class="myButton"onclick="editBox.addObject(false)" style="width: 61px">Add</button>
				</td>
			</tr>
		</table>
	</div>
@stop


@section($prefix.'filter_extra')
@parent
	<script type='text/javascript'>
		var oBuildFilterData		= editBox.buildFilterData;
		editBox.buildFilterData 	= function(){
			var dataStore 			= oBuildFilterData();
			dataStore.cboOperation 	= $("#cboOperation").val();
			dataStore.txtConstant 	= $("#txtConstant").val();
			return dataStore;
		}

		editBox.updateExtraFilterData = function(dataStore){
			if(currentSpan==null) $("#updateFilterBtn").hide();
			else $("#updateFilterBtn").show();
			
// 			$("#edit_cboYPos").val(dataStore.cboYPos);
// 			$("#edit_txt_y_unit").val(dataStore.txt_y_unit);
		}
	</script>
@stop