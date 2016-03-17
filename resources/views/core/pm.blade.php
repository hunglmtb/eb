<?php
$subMenus = [array('title' => 'FLOW STREAM', 'link' => 'flow'),
		array('title' => 'ENERGY UNIT', 'link' => 'eu'),
		array('title' => 'STORAGE', 'link' => 'storage'),
		array('title' => 'TICKET', 'link' => 'ticket'),
		array('title' => 'WELL TEST', 'link' => 'eutest'),
		array('title' => 'DEFERMENT', 'link' => 'deferment'),
		array('title' => 'QUALITY', 'link' => 'quality')
];
?>
@extends('core.bsmain',['subMenus' => $subMenus])

@section('content')
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
@stop

@section('adaptData')
@stop