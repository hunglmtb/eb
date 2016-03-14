<?php
$subMenus = [array('title' => 'FLOW STREAM', 'link' => 'flow'),
		array('title' => 'ENERGY UNIT', 'link' => 'eu'),
		array('title' => 'STORAGE', 'link' => 'storage'),
		array('title' => 'TICKET', 'link' => 'ticket'),
		array('title' => 'WELL TEST', 'link' => 'eutest'),
		array('title' => 'DEFERMENT', 'link' => 'deferment'),
		array('title' => 'QUALITY', 'link' => 'quality')
];
$tables = ['flow_data_fdc_value'=>['name'=>'FDC VALUE'],
		'flow_data_value'=>['name'=>'STD VALUE'],
		'flow_data_theor'=>['name'=>'THEORETICAL'],
		'flow_data_alloc'=>['name'=>'ALLOCATION'],
		'flow_comp_data_alloc'=>['name'=>'COMPOSITION ALLOC'],
		'flow_data_plan'=>['name'=>'PLAN'],
		'flow_data_forecast'=>['name'=>'FORECAST'],
];
?>
@extends('core.bsmain',['subMenus' => $subMenus])

@section('content')
<div id="tabs">
			<ul>
				@foreach($tables as $key => $table )
					<li><a href="#tabs-{{$key}}"><font size="2">{{$table['name']}}</font></a></li>
		 		@endforeach
			</ul>
			
			@foreach($tables as $key => $table )
				<div id="tabs-{{$key}}">
					<div id="container_{{$key}}" style="width:1280px;overflow-x:hidden">
						<table border="0" cellpadding="3" id="table_{{$key}}" class="fixedtable nowrap display compact">
							<thead>
				                <tr style="height:26"><th style='font-size:9pt;text-align:left;white-space: nowrap; background:#FFF'><div style="width:230px">Object name</div></th>
								</tr>
							</thead>
							<tbody id="body_{{$key}}">
							{{$table['name']}}
							</tbody>
						</table>
					</div>
				</div>
	 		@endforeach
		</div>
@stop