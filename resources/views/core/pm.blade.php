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
		var uoms = data.uoms;
		$.each(uoms, function( index, value ) {
			var collection = value['data'];
			/* var option = ["<select size='1' style='width:100%;'>"];
			$.each(collection, function(key, value)
			{
				option.push('<option value="'+ value['ID'] +'">'+ value['CODE'] +'</option>');
			});
			option.push("</select>"); */

			/* var combo = $("<select  size='1' style='width:100%;'></select>").attr("id", value['id']).attr("name", value['id']);
		    $.each(collection, function (key, value) {
		        combo.append('<option value="'+ value['ID'] +'">'+ value['CODE'] +'</option>');
		    }); */

			
			uoms[index]["render"] = function ( data, type, row ) {
							                var result = $.grep(collection, function(e){ 
								                return e['ID'] == data;
								                });
	                						return result[0]['CODE'];
                					};
		});
				
		$('#table_'+tab).DataTable( {
 	          data: data.dataSet,
	          columns: data.properties,
	          destroy: true,
	          "columnDefs": uoms,
	          /* paging: false,
	          searching: false */
	    } );
	};

	actions.shouldLoad = function(data){
		var activeTabID = getActiveTabID();
		var postData = actions.loadedData[activeTabID];
		var noData = jQuery.isEmptyObject(postData);
		var dataNotMatching = false;
		if (!noData) {
			
		}
		
		var shouldLoad = actions.readyToLoad&&(noData||dataNotMatching);
		return shouldLoad;
	};
</script>
@stop
