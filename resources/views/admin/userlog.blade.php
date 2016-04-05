<?php
$currentSubmenu = 'userlog';

$listControls = [ 		
		'USER' => array (
				'label' => 'User',
				'ID' => 'USER',
				'default' => 'All' 
		),		
		
		'begin_date' => array (
				'label' => 'Login from date',
				'ID' => 'begin_date',
				'TYPE' => 'DATE' 
		),
		
		'end_date' => array (
				'label' => 'To date',
				'ID' => 'end_date',
				'TYPE' => 'DATE' 
		) ,
		
		'loadData' => array(
				'label' => 'Load Data',
				'ID' => 'Load Data',
				'TYPE' => 'BUTTON',
				'onclick' => '_userLog.loadData()'
		)
];

?>

@extends('core.am', ['listControls' => $listControls])

@section('title')
<div class="title">USERS LOG</div>
@stop @section('content')

<script type="text/javascript">
$(function(){	
	$('#listValidate').css('width', 855);
});

var _userLog = {
		loadData : function (){
			var dateFrom = $('#begin_date').val().replace('-', '/');
			var dateTo = $('#end_date').val().replace('-', '/');
			param = {
				'DATE_FROM' : dateFrom,
				'DATE_TO' : dateTo,
				'USERNAME' : $('#User option:selected').text()
			}

			sendAjax('/am/loadUserLog', param, function(data){
				_userLog.listData(data.result)
			});	
		},
		
		listData : function(data){
			var str = '';
			$('#bodyList').html('');
			
			for(var i = 0; i< data.length; i++){
				var id = data[i].ID;
				var cssClass = "row1";
				if(i%2 == 0){
					cssClass = "row2";
				} 
				str += '<tr class='+ cssClass +'>';
				str += '	<td class="vcolumn205">'+ checkValue(data[i].USERNAME,'') +'</td>';
				str += '	<td class="vcolumn205">'+ checkValue(data[i].LOGIN_TIME,'') +'</td>';
				str += '	<td class="vcolumn205">'+ checkValue(data[i].LOGOUT_TIME,'') +'</td>';
				str += '	<td class="vcolumn205">'+ checkValue(data[i].IP,'') +'</td>';
				str += '</tr>';
			}

			$('#bodyList').html(str);			
		}
}
</script>

<div>
	<table style="table-layout: fixed;">
		<thead id="table5">
			<tr>
				<td class="column200">Username</td>
				<td class="column200">Login time</td>
				<td class="column200">Logout time</td>
				<td class="column200">IP address</td>
			</tr>
		</thead>
	</table>

	<div id="listValidate">
		<table style="table-layout: fixed;">
			<tbody id="bodyList">
			</tbody>
		</table>
	</div>
</div>
@stop
