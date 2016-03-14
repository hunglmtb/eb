<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
	$tables = ['flow_data_fdc_value'=>['name'=>'FDC VALUE'],
			'flow_data_value'=>['name'=>'STD VALUE'],
			'flow_data_theor'=>['name'=>'THEORETICAL'],
			'flow_data_alloc'=>['name'=>'ALLOCATION'],
			'flow_comp_data_alloc'=>['name'=>'COMPOSITION ALLOC'],
			'flow_data_plan'=>['name'=>'PLAN'],
			'flow_data_forecast'=>['name'=>'FORECAST'],
			];
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])

@section('main')
<div style="padding-left:10px">
	<div style="padding:10px 10px 10px 0px;font-size:16pt;">@yield('funtionName')</div>
	<form name="form_fdc" id="form_fdc" action="saveeufdc.php" method="POST"> 
		<input name="fields_fdc" value="" type="hidden">
		<input name="fields_value" value="" type="hidden">
		<input name="fields_theor" value="" type="hidden">
		<input name="fields_alloc" value="" type="hidden">
		<input name="fields_plan" value="" type="hidden">
		<input name="fields_forecast" value="" type="hidden">
		@include('group.production')
		<br>
		@yield('content')
		
		
		
		
		
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
	</form>
</div>
@stop


@section('script')
 	<link rel="stylesheet" href="/common/css/jquery-ui.css" />
	<script src="/common/js/jquery-1.10.2.js"></script>
	<script src="/common/js/jquery-ui.js"></script>
	<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
	
	<script>
		$(document).ready(function () {
	 	    $("#tabs").tabs();
// 			$("#tabs").tabs({active:2});
	
		});
	</script>
@stop

