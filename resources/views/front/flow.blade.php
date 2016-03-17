<?php
	$currentSubmenu ='flow';
	$groups = [array('name' => 'group.date','data' => 'Date'),
				array('name' => 'group.production','data' => 'data'),
				array('name' => 'group.frequency','data' => 'frequency')
				];
?>

@extends('core.pm')
@section('funtionName')
FLOW DATA CAPTURE
@stop

@section('adaptData')
<script>
var dataSet = [
               [ "Tiger Nixon","Tiger Nixon", "Edinburgh", "5421", "2011/04/25", "$320,800" ],
               [ "Tiger Nixon","Garrett Winters", "Tokyo", "8422", "2011/07/25", "$170,750" ],
               [ "Tiger Nixon","Ashton Cox", "San Francisco", "1562", "2009/01/12", "$86,000" ]
           ];
actions.loadUrl = "/code/load";
actions.initData = function(data){
	
}
actions.loadSuccess =  function(data){
	alert("len nao");
// 	$('#'+"container_flow_data_value").empty();
// 	$('#'+"table_flow_data_value").dataTable().clear();
// 	data.properties.unshift({ title: "Object name" });
	var tbl = $('#'+"table_flow_data_value").DataTable( {
//          data: data.dataSet,
//         data: dataSet,
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
