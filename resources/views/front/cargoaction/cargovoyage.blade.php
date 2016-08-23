<?php
	$currentSubmenu ='/pd/cargovoyage';
	$tables = ['PdVoyage'	=>['name'=>'Load']];
	$detailTableTab = 'PdVoyageDetail';
	$attributeTableTab = 'PdCodeContractAttribute';
	
	$isAction = true;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargovoyage/load";
	actions.saveUrl = "/cargovoyage/save";

	actions['idNameOfDetail'] = ['CONTRACT_ID_INDEX', 'ATTRIBUTE_ID_INDEX'];

	addingOptions.keepColumns = ['BEGIN_DATE','END_DATE','CONTRACT_TEMPLATE','CONTRACT_TYPE','CONTRACT_PERIOD','CONTRACT_EXPENDITURE'];

	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="PdVoyage";
	};

	editBox['getSaveButton'] = function (){
		return $("<a id='savebtn' href='#' style='right: 60px;position: absolute;display:none'>Generate transport detail</a>")
		.button({/* icons:{primary: "ui-icon-plus"}, */text: true});
 	}
 	
	/*  oAfterTable = actions.afterDataTable;
	 actions.afterDataTable = function (table,tab){
		 oAfterTable(table,tab);
		 if(tab='PdVoyageDetail'){
			 jQuery('<button/>', {
				    id: 'more_'+tab,
				    title: 'Generate transport detail',
				    text: 'Generate transport detail'
				}).on( 'click', function(e){
					alert('Generate transport detail');
					})
				.appendTo("#toolbar_"+tab);
		 }
	}; */
	
	currentContractId = 0;
	editBox['filterField'] = 'ATTRIBUTE_ID';
	
	editBox['addAttribute'] = function(addingRow,selectRow){
		addingRow['ATTRIBUTE_ID'] 		= selectRow.CODE;
		addingRow['CONTRACT_ID'] 		= selectRow.NAME;
		addingRow['ATTRIBUTE_ID_INDEX'] = selectRow.ID;
		addingRow['CONTRACT_ID_INDEX'] 	= currentContractId;
		return addingRow;
	};

	editBox.initExtraPostData = function (id,rowData){
									currentContractId = id;
								 		return 	{
									 		id			: id,
									 		Facility	: actions.loadedData['PdVoyage'].Facility};
								 	};

 	actions['initDeleteObject']  = function (tab,id, rowData) {
		 if(tab=='{{$detailTableTab}}') return {'ID':id, CONTRACT_ID : rowData.CONTRACT_ID_INDEX};
		return {'ID':id};
	 };

</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = "/voyage/load";
	editBox.saveUrl = '/voyage/save';

</script>
@stop
