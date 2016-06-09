<?php
$currentSubmenu = 'approvedata';

$listControls = [ 
		'LoProductionUnit' => array (
				'label' => 'Production Unit',
				'ID' => 'LoProductionUnit' 
		),
		
		'LoArea' => array (
				'label' => 'Area',
				'ID' => 'LoArea',
				'fkey' => 'production_unit_id' 
		),
		
		'Facility' => array (
				'label' => 'Facility',
				'ID' => 'Facility',
				'fkey' => 'area_id' 
		),
		
		'DataTableGroup' => array (
				'label' => 'DataTableGroup',
				'ID' => 'DataTableGroup',
				'default' => 'All' 
		),
		
		'IntObjectType' => array (
				'label' => 'Object Type',
				'ID' => 'IntObjectType',
				'default' => 'All' 
		),
		
		'loadData' => array(
				'label' => 'Load Data',
				'ID' => 'loadData',
				'TYPE' => 'BUTTON',
				'onclick' => '_approve.loadData()'
		),
		
		'begin_date' => array (
				'label' => 'From Date (<span style="font-size:9px; color: red;">for approve All</span>)',
				'ID' => 'begin_date',
				'TYPE' => 'DATE' 
		),
		
		'end_date' => array (
				'label' => 'To Date (<span style="font-size:9px; color: red;">for approve All</span>)',
				'ID' => 'end_date',
				'TYPE' => 'DATE' 
		) ,
		
		'validateData' => array(
				'label' => 'Approve All',
				'ID' => 'Approve All',
				'TYPE' => 'BUTTON',
				'onclick' => '_approve.approveAll()'
		)
];

?>

@extends('core.am', ['listControls' => $listControls])

@section('title')
<div class="title">APPROVE DATA</div>
@stop @section('content')

<script type="text/javascript">
$(function(){
	$("#checkAll").click(function () {
        $('.chckbox').prop('checked', this.checked);
	});
	
});

var _approve = {
		loadData : function (){
			param = {
				'FACILITY_ID' : $('#Facility').val(),
				'GROUP_ID' : $('#DataTableGroup').val(),
				'OBJECTTYPE' : $('#ObjectType').val()
			}

			sendAjax('/am/loadApproveData', param, function(data){
				_approve.listData(data.result)
			});
		},
		
		listData : function(data){
			var str = '';
			$('#bodyList').html('');
			
			for(var i = 0; i< data.length; i++){
				var id = data[i].ID;
				var k = i+"abc"+data[i].TABLE_NAME;
				var cssClass = "row1";
				if(i%2 == 0){
					cssClass = "row2";
				} 
				str += '<tr class='+ cssClass +'>';
				str += '	<td class="vcolumn35"><input class="chckbox" table_name = '+data[i].TABLE_NAME+' name='+data[i].ID +' type="checkbox" ' + (data[i].T_ID) + '></td>';
				str += '	<td class="vcolumn205" id="table_name_'+i+'">'+ checkValue(data[i].TABLE_NAME,'') +'</td>';
				str += '	<td class="vcolumn205">'+ checkValue(data[i].FRIENDLY_NAME,'') +'</td>';
				str += '	<td class="vcolumn165"><input type="text" id="txtDateFrom_'+i+'" value="'+checkValue(data[i].DATE_FROM,'')+'"/></td>';
				str += '	<td class="vcolumn165"><input type="text" id="txtDateTo_'+i+'" value="'+checkValue(data[i].DATE_TO,'')+'"/></td>'; 
				str += '	<td class="vcolumn105"><input type="button" onclick="_approve.approveData('+i+')" value="Approve" class="btnValidate"/></td>';
				str += '</tr>';
			}

			$('#bodyList').html(str);

			 $( "input[type='text']" ).datepicker({
				changeMonth:true,
				changeYear:true,
				dateFormat:"mm/dd/yy"
			}); 

			
		},

		approveData : function(index){ 
			var tableName = $('#table_name_'+index).text();
			var dateFrom = $('#txtDateFrom_'+index).val();
			var dateTo = $('#txtDateTo_'+index).val();
 
			if(dateFrom == "" || dateTo == ""){
				alert("Please select date range to approve data");
				return;
			}
			
			param = {
					'FACILITY_ID' : $('#Facility').val(),
					'DATE_FROM' : dateFrom,
					'DATE_TO' : dateTo,
					'TABLE_NAMES' : tableName,
					'GROUP_ID' : $('#DataTableGroup').val(),
					'OBJECTTYPE' : $('#ObjectType').val()
			}

			sendAjax('/am/approveData', param, function(data){
				_approve.listData(data.result)
			});
		},

		approveAll : function(){
			var tableName="";
			$('.chckbox').each(function(){
				if(this.checked) tableName+=(tableName==""?"":",")+$(this).attr("table_name");
			});
			if(tableName=="")
			{
				alert("Please select tables that you want to approve data");
				return;
			}

			if(!confirm("Are you sure to approve all data of facility "+$("#Facility option:selected").text()+"?")) return;

			var dateFrom = $('#begin_date').val().replace('-', '/');
			var dateTo = $('#end_date').val().replace('-', '/');
			param = {
					'FACILITY_ID' : $('#Facility').val(),
					'DATE_FROM' : dateFrom,
					'DATE_TO' : dateTo,
					'TABLE_NAMES' : tableName,
					'GROUP_ID' : $('#DataTableGroup').val(),
					'OBJECTTYPE' : $('#ObjectType').val()
			}

			sendAjax('/am/approveData', param, function(data){
				_approve.listData(data.result)
			});
		}
}
</script>

<div>
	<table style="table-layout: fixed;">
		<thead id="table5">
			<tr>
				<td class="column30"><input class="chckAll" type="checkbox"
					id="checkAll"></td>
				<td class="column200">Data table</td>
				<td class="column200">Name</td>
				<td class="column160">From date</td>
				<td class="column160">To date</td>
				<td class="column100">Action</td>
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
