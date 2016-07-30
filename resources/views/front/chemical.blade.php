<?php
$currentSubmenu ='/fo/chemical';
$tables = ['KeystoreTankDataValue'	=>['name'=>'CHEMICAL TANK VALUE'],
		'KeystoreStorageDataValue'	=>['name'=>'CHEMICAL STORAGE VALUE'],
		'KeystoreInjectionPointDay'	=>['name'=>'CHEMICAL INJECTION POINT']
];

?>
@extends('core.fo')

@section('funtionName')
CHEMICAL DATA
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/chemical/load";
	actions.saveUrl = "/chemical/save";
	actions.type = {
					idName:['EQUIPMENT_ID','EQP_FUEL_CONS_TYPE','EQP_GHG_REL_TYPE'],
					keyField:'EQUIPMENT_ID',
					saveKeyField : function (model){
						return 'EQUIPMENT_ID';
					},
				};
	
	/* actions.getGrepValue = function (data,uom,rowData) {
		if(uom.COLUMN_NAME == 'EQP_FUEL_CONS_TYPE' && rowData.FUEL_TYPE!=null) return rowData.FUEL_TYPE;
		if(uom.COLUMN_NAME == 'EQP_GHG_REL_TYPE' && rowData.GHG_REL_TYPE!=null) return rowData.GHG_REL_TYPE;
		return data;
	};

	actions.notUniqueValue = function(uom,rowData){
		if(uom.COLUMN_NAME == 'EQP_FUEL_CONS_TYPE' && rowData.FUEL_TYPE!=null) {
			rowData.EQP_FUEL_CONS_TYPE = rowData.FUEL_TYPE;
			return false;
		}
		if(uom.COLUMN_NAME == 'EQP_GHG_REL_TYPE' && rowData.GHG_REL_TYPE!=null){
			rowData.EQP_GHG_REL_TYPE = rowData.GHG_REL_TYPE;
			return false;
		 }
		return true;
	}

	actions.isShownOf = function (value,postData) {
		return moment(value.OCCUR_DATE).isSame(postData.date_begin,'day');
	}; */
</script>
@stop
