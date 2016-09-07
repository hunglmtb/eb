<?php
	$currentSubmenu ='/pd/contracttemplate';
	$tables = ['PdContractTemplate'	=>['name'=>'Load']];
	$detailTableTab = 'PdContractTemplateAttribute';
	$attributeTableTab = 'PdCodeContractAttribute';
	$isAction = true;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/contracttemplate/load";
	actions.saveUrl = "/contracttemplate/save";

	actions['idNameOfDetail'] = ['CONTRACT_TEMPLATE','ATTRIBUTE'];
	actions.type['keyField'] = 'DT_RowId';
	addingOptions.keepColumns = ['EFFECTIVE_DATE','END_DATE','CONTACT_TYPE',];
	templateId = 0;

	editBox['addAttribute'] = function(addingRow,selectRow){
		addingRow['CODE'] 		= selectRow.CODE;
		addingRow['NAME'] 		= selectRow.NAME;
		addingRow['ATTRIBUTE']	= selectRow.ID;
		addingRow['CONTRACT_TEMPLATE'] 	= templateId;
		return addingRow;
	};
	
	editBox.initExtraPostData = function (id,rowData){
		templateId = id;
	 		return 	{
		 			id			: id,
		 			tab			: 'PdCodeContractAttribute',
		 		};
	 	}

	/* actions['initDeleteObject']  = function (tab,id, rowData) {
		 if(tab=='{{$detailTableTab}}') return {'ID':id, CONTRACT_TEMPLATE : rowData.CONTRACT_TEMPLATE};
		return {'ID':id};
	 }; */
</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = "/contracttemplateattribute/load";
	editBox.saveUrl = '/contracttemplateattribute/save';
</script>
@stop
