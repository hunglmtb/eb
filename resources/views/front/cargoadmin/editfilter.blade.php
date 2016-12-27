@extends('partials.editfilter')
@section($prefix.'action_extra')
@parent
	<table border="0" class="clearBoth" style="">
		<tr>
			<td align="right">
				<input type="checkbox" id="edit_chkMinus"> Negative
			</td>
		</tr>
	</table>
@stop

@section($prefix.'filter_extra')
@parent
	<script type='text/javascript'>
		var oBuildFilterData		= editBox.buildFilterData;
		editBox.buildFilterData 	= function(){
			var dataStore 			= oBuildFilterData();
			dataStore.chkMinus 		= $("#edit_chkMinus").prop('checked');
			return dataStore;
		}

		editBox.updateExtraFilterData = function(dataStore){
		    $("#container_{{$prefix}}Tank").hide();
		    $("#edit_chkMinus").prop('checked',dataStore.chkMinus)
		}

		editBox.addMoreFilterText = function(texts){
			texts.chkMinus		= $("#edit_chkMinus").prop('checked');
		}
	</script>
@stop
