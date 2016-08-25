<?php
	$currentSubmenu ='/pd/contractdata';
	$tables = ['PdContract'	=>['name'=>'Load']];
	$detailTableTab = 'PdContractData';
	$attributeTableTab = 'PdCodeContractAttribute';
	
	$isAction = true;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/contractdata/load";
	actions.saveUrl = "/contractdata/save";
	actions['idNameOfDetail'] = ['CONTRACT_ID_INDEX', 'ATTRIBUTE_ID_INDEX','ID'];

	addingOptions.keepColumns = ['BEGIN_DATE','END_DATE','CONTRACT_TEMPLATE','CONTRACT_TYPE','CONTRACT_PERIOD','CONTRACT_EXPENDITURE'];

	currentContractId = 0;
	editBox['filterField'] = 'ATTRIBUTE_ID';
	
	editBox['addAttribute'] = function(addingRow,selectRow){
		addingRow['ATTRIBUTE_ID'] 		= selectRow.CODE;
		addingRow['CONTRACT_ID'] 		= selectRow.NAME;
		addingRow['ATTRIBUTE_ID_INDEX'] = selectRow.ID;
		addingRow['CONTRACT_ID_INDEX'] 	= currentContractId;
		addingRow['CONTRACT_ID_INDEX'] 	= currentContractId;
		return addingRow;
	};

	editBox.initExtraPostData = function (id,rowData){
									currentContractId = id;
								 		return 	{
									 		id			: id,
									 		templateId	: rowData.CONTRACT_TEMPLATE};
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
	editBox.loadUrl = "/contractdetail/load";
	editBox.saveUrl = '/contractdetail/save';

</script>
@stop
