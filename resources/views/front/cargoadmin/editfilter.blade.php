@extends('partials.editfilter')

@section($prefix.'filter_extra')
@parent
	<script type='text/javascript'>
		editBox.updateExtraFilterData = function(dataStore){
		    $("#container_{{$prefix}}Tank").hide();
// 		    $("#edit_chkMinus").prop('checked',dataStore.chkMinus)
		}

		editBox.addMoreFilterText = function(texts){
// 			texts.chkMinus		= $("#edit_chkMinus").prop('checked');
		}
	</script>
@stop
