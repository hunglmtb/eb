<?php
if (!isset($subMenus)) $subMenus = [];
if (!isset($active)) $active =1;
?>
@extends('core.bsmain',['subMenus' => $subMenus])

@section('content')
	@if(isset($tables))
			<div id="tabs">
			<ul>
				@foreach($tables as $key => $table )
					<li id="{{$key}}"><a href="#tabs-{{$key}}"><font size="2">{{$table['name']}}</font></a></li>
		 		@endforeach
			</ul>
			@foreach($tables as $key => $table )
				<div id="tabs-{{$key}}">
					<div id="container_{{$key}}" style="width:1280px;overflow-x:hidden">
						<table border="0" cellpadding="3" id="table_{{$key}}" class="fixedtable nowrap display compact">
						</table>
					</div>
				</div>
	 		@endforeach
		</div>
		@section('script')
			@parent
			<script>
					$(document).ready(function () {
						$("#tabs").tabs({
							active:{{$active}},
							activate: function(event, ui) {
						        actions.loadNeighbor(event, ui);
						    }
						});
					});
			</script>
		@stop
	@endif
@stop

@section('adaptData')
<script>
	actions.initData = function(){
		var activeTabID = getActiveTabID();
		var tab = {'{{config("constants.tabTable")}}':activeTabID}
		return tab;
	}
</script>
@stop

