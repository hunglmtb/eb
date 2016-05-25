<?php
$currentSubmenu = 'validatedata';

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
		
		'IntObjectType' => array (
				'label' => 'Object Type',
				'ID' => 'IntObjectType'
		),
		
		'ObjectName' => array (
				'label' => 'Object  Name',
				'ID' => 'Object Name',
				'default' => 'All' 
		),
		
		'save' => array(
				'label' => 'Save',
				'ID' => 'Save',
				'TYPE' => 'BUTTON',
				'onclick' => '_tagsmapping.saveTags()'
		),
		
		'loadData' => array(
				'label' => 'load Tags',
				'ID' => 'loadData',
				'TYPE' => 'BUTTON',
				'onclick' => '_tagsmapping.loadData()'
		),
		
		'addTags' => array(
				'label' => 'Add Tag',
				'ID' => 'addTag',
				'TYPE' => 'BUTTON',
				'onclick' => '_tagsmapping.addTag()'
		)
];

?>

@extends('core.bstagsmapping', ['listControls' => $listControls])

@section('title')
<div class="title">TAG MAPPING CONFIG</div>

@section('group')
	@include('group.adminControl')
@stop

@stop @section('content')

<script type="text/javascript">
$(function(){
		
});

var _validatedata = {
		loadData : function (){
			param = {
				'FACILITY_ID' : $('#Facility').val(),
				'GROUP_ID' : $('#DataTableGroup').val(),
				'OBJECTTYPE' : $('#ObjectType').val()
			}

			sendAjax('/am/loadValidateData', param, function(data){
				_validatedata.listData(data.result);
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
				str += '	<td class="vcolumn105"><input type="button" onclick="_validatedata.validateData('+i+')" value="Validate" class="btnValidate"/></td>';
				str += '</tr>';
			}

			$('#bodyList').html(str);

			 $( "input[type='text']" ).datepicker({
				changeMonth:true,
				changeYear:true,
				dateFormat:"mm/dd/yy"
			}); 

			
		},

		validateData : function(index){ 
			var tableName = $('#table_name_'+index).text();
			var dateFrom = $('#txtDateFrom_'+index).val();
			var dateTo = $('#txtDateTo_'+index).val();
 
			if(dateFrom == "" || dateTo == ""){
				alert("Please select date range to validate data");
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

			sendAjax('/am/validateData', param, function(data){
				_validatedata.listData(data.result);
			});
		},

		validateAll : function(){
			var tableName="";
			$('.chckbox').each(function(){
				if(this.checked) tableName+=(tableName==""?"":",")+$(this).attr("table_name");
			});
			if(tableName=="")
			{
				alert("Please select tables that you want to validate data");
				return;
			}

			if(!confirm("Are you sure to validate all data of facility "+$("#Facility option:selected").text()+"?")) return;

			var dateFrom = $('#begin_date').val().replace('-','/');
			var dateTo = $('#end_date').val().replace('-','/');
			param = {
					'FACILITY_ID' : $('#Facility').val(),
					'DATE_FROM' : dateFrom,
					'DATE_TO' : dateTo,
					'TABLE_NAMES' : tableName,
					'GROUP_ID' : $('#DataTableGroup').val(),
					'OBJECTTYPE' : $('#ObjectType').val()
			}

			sendAjax('/am/validateData', param, function(data){
				_validatedata.listData(data.result);
			});
		}
}
</script>

<div id="EditGroup"
	style="display: none; width: 100%; height: 100%; background: #ffffff; overflow: hidden;">
	<iframe id="iframeWorkflow"
		style="border: none; padding: 0px; margin-left:-21px; width: 110%; height: 100%; box-sizing: border-box;"></iframe>
</div>

<div>
	<table style="table-layout: fixed;">
		<thead id="table5">
			<tr>
				<td class="column30">Tag</td>
				<td class="column200">Object Name</td>
				<td class="column200">Table</td>
				<td class="column160">Column</td>
				<td class="column160">Event Type</td>
				<td class="column100">Flow Phase</td>
				<td class="column160">System</td>
				<td class="column160">Frequency</td>
				<td class="column100">Override</td>
				<td class="column160">Begin Date</td>
				<td class="column160">End Date</td>
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
