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
@extends('core.bscontent',['subMenus' => $subMenus])
@section('adaptData')
@parent
<script>
	actions.loadSuccess =  function(data){
		postData = data.postData;
		var tab = postData['{{config("constants.tabTable")}}'];
		actions.loadedData[tab] = postData;
// 		alert("len nao "+tab);
		
		if($.fn.dataTable.isDataTable( '#table_'+tab ))
			tbl.destroy();
		
		tbl = $('#table_'+tab).DataTable( {
	          data: data.dataSet,
	          columns: data.properties
	    } );
	
		/* tbl.clear();
		tbl.rows.add(data.dataSet);     // You might need to use eval(result)
		tbl.draw(); */
	};
</script>
@stop
