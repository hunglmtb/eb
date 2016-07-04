<?php
$subMenus = [	array('title' => 'WELL FORECAST', 'link' => 'forecast'),
				array('title' => 'PREoS', 'link' => 'preos'),
				array('title' => 'MANUAL ALLOCATE PLAN', 'link' => 'allocateplan'),
				array('title' => 'LOAD PLAN/FORECAST', 'link' => 'loadplanforecast'),
];
?>
@extends('core.bscontent',['subMenus' => $subMenus])


@section('actionPanel')
	<div id="selected_objects" style="padding:5px"></div>
	<br>
	<button onClick="actions.doLoad(true)" style="width:100%;height:30px;margin:0px 0px">Load PREoS input data</button>
	<br>
	<br>
	<input type="checkbox" name="chk_update_db" id="chk_update_db"> Update database<br>
	<button onClick="actions.doSave(true)" style="width:120px;height:30px;margin:20px 0px">Run PREoS</button>
@stop

@section('actionInputTable')
	<div id="container_{{$key}" style="overflow-x:hidden">
		<table border="0" cellpadding="3" id="table_{{$key}" class="fixedtable nowrap display"></table>
	</div>
@stop

@section('actionOutputView')
	<div id="result">
		<b>Forecast log:</b><br>
		<b>Input data:</b> <div id="result_data"></div><br>
		<b>Time forecast:</b> <div id="result_time"></div><br>
		<b>Params:</b> <div id="result_params"></div><br>
		<span id="result_warning" style='background:orange;color:black'><b>Warning: </b></span><br>
		<b>Result:</b><br> <div id="result_result"></div><br>
		<br><span id="result_error" style='background:red;color:white'><b></b></span>
	</div>
@stop


@section('content')
<table cellspacing="5" cellpadding="0" style="background:#e0e0e0;margin-top:10px">
<tr>
	<td valign="top" style="padding:5px">
		<table>
		<tr>
			<td valign="top" style="background:#e0e0e0;padding:5px">
				@yield('actionPanel')
			</td>
		</tr>
		</table>
	</td>
	<td id="box_load_input_data" valign="top" style="padding:5px">
		@yield('actionInputTable')
	</td>
	<td valign="top" style="background:#e0e0e0;padding:5px">
		<div id="boxOutputData" style="width:850px;height:400px;overflow:auto">
			@yield('actionOutputView')
		</div>
	</td>
</tr>
</table>
@stop
 