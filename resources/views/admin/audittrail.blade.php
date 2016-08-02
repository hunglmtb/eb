<?php
$currentSubmenu = '/am/audittrail';

$listControls = [ 
		'LoProductionUnit' => array (
				'label' => 'Production Unit',
				'ID' => 'LoProductionUnit',
		),
		
		'LoArea' => array (
				'label' => 'Area',
				'ID' => 'LoArea',
				'fkey' => 'production_unit_id',
		),
		
		'Facility' => array (
				'label' => 'Facility',
				'ID' => 'Facility',
				'fkey' => 'area_id' 
		),
		
		'IntObjectType' => array (
				'label' => 'Object Type',
				'ID' => 'IntObjectType',
				'default' => 'All'
		),
		
		'begin_date' => array (
				'label' => '	From Date',
				'ID' => 'begin_date',
				'TYPE'=> 'DATE'				
		),
		
		'end_date' => array (
				'label' => '	To Date',
				'ID' => 'end_date',
				'TYPE'=> 'DATE'
		),
		
		'loadData' => array(
				'label' => 'load Data',
				'ID' => 'loadData',
				'TYPE' => 'BUTTON',
				'onclick' => '_audittrail.loadData()'
		)
];


?>

@extends('core.am', ['listControls' => $listControls])

@section('content')

<script type="text/javascript">
$(function(){	
});

var _audittrail = {
		loadData : function (){
			param = {
				'FACILITY_ID' : $('#Facility').val(),
				'BEGIN' : $('#begin_date').val(),
				'END' : $('#end_date').val(),
				'OBJECTTYPE' : $("#ObjectType option:selected").text()
			}

			sendAjax('/am/loadAudittrail', param, function(data){
				_audittrail.listData(data.result);
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
				str += '	<td class="vcolumn145">' + checkValue(data[i].ACTION,'') + '</td>';
				str += '	<td class="vcolumn75">'+ checkValue(data[i].WHO,'') +'</td>';
				str += '	<td class="vcolumn75">'+ checkValue(data[i].WHEN,'') +'</td>';
				str += '	<td class="vcolumn125">'+ checkValue(data[i].REASON,'') +'</td>';
				str += '	<td class="vcolumn185">'+ checkValue(data[i].OBJECT_DESC,'') +'</td>'; 
				str += '	<td class="vcolumn75">'+ checkValue(data[i].RECORD_ID,'') +'</td>';
				str += '	<td class="vcolumn185">'+ checkValue(data[i].TABLE_NAME,'') +'</td>';
				str += '	<td class="vcolumn160">'+ checkValue(data[i].COLUMN_NAME,'') +'</td>';
				str += '	<td class="vcolumn65">'+ checkValue(data[i].OLD_VALUE,'') +'</td>';
				str += '	<td class="vcolumn65">'+ checkValue(data[i].NEW_VALUE,'') +'</td>';
				str += '</tr>';
			}

			$('#bodyList').html(str);

			hideWaiting(); 
		}
}
</script>

<div>	
	<table style="table-layout: fixed;">
		<thead id="table5">
			<tr>
				<td class="column140"><b>Action</b></td>
				<td class="column70"><b>By</b></td>
				<td class="column70"><b>Time</b></td>
				<td class="column120"><b>Reason</b></td>
				<td class="column180"><b>Object</b></td>
				<td class="column70"><b>Record ID</b>	</td>
				<td class="column180"><b>Table</b></td>
				<td class="column160"><b>Column</b></td>
				<td class="column60"><b>Old Value</b></td>
				<td class="column60"><b>New Value</b></td>
			</tr>
		</thead>
	</table>
	<div id="listAudittrail">	
	<table class="tableBody">
		<tbody id="bodyList">
		</tbody>
	</table>
	</div>
</div>
@stop
