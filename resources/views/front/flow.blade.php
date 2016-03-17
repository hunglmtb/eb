<?php
	$currentSubmenu ='flow';
	$tables = ['FlowDataFdcValue'	=>['name'=>'FDC VALUE'],
				'FlowDataValue'		=>['name'=>'STD VALUE'],
				'FlowDataTheor'		=>['name'=>'THEORETICAL'],
				'FlowDataAlloc'		=>['name'=>'ALLOCATION'],
				'FlowCompDataAlloc'	=>['name'=>'COMPOSITION ALLOC'],
				'FlowDataPlan'		=>['name'=>'PLAN'],
				'FlowDataForecast'	=>['name'=>'FORECAST'],
	];
?>

@extends('core.pm')
@section('funtionName')
FLOW DATA CAPTURE
@stop

@section('adaptData')
<script>
actions.loadUrl = "/code/load";
actions.initData = function(){
	var activeTabIdx = $("#tabs").tabs('option', 'active');
	var selector = '#tabs > ul > li';
	var activeTabID = $(selector).eq(activeTabIdx).attr('id');
	var tab = {'{{config("constants.tabTable")}}':activeTabID}
	return tab;
}
actions.loadSuccess =  function(data){
	postData = data.postData;
	alert("len nao "+postData['{{config("constants.tabTable")}}']);
	var tbl = $('#table_'+postData['{{config("constants.tabTable")}}']).DataTable( {
//          data: data.dataSet,
          columns: data.properties
//         columns: [{ "data": "FL_NAME",title:"keke" },{ "data": "X_FL_ID" ,title:"jiji" },{ "data": "FL_FLOW_PHASE" }]
        /* columns: [
                  { title: "Name" },
                  { title: "Position" },
                  { title: "Office" }] */
    } );

	tbl.clear();
	tbl.rows.add(data.dataSet);     // You might need to use eval(result)
	tbl.draw();
//     columns: data.properties
};
</script>
@stop
