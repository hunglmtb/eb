<?php
$currentSubmenu ='equipment';
$tables = ['EquipmentDataValue'	=>['name'=>'DAY_VALUE']];
?>
@extends('core.fo')

@section('funtionName')
EQUIPMENT DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/equipment/load";
	actions.saveUrl = "/equipment/save";
	actions.type = {
					idName:['ID','EQP_FUEL_CONS_TYPE'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
					},
				};
	
	actions.getGrepValue = function (data,uom,rowData) {
		if(uom.COLUMN_NAME == 'EQP_FUEL_CONS_TYPE' && rowData.FUEL_TYPE!=null) return rowData.FUEL_TYPE;
		if(uom.COLUMN_NAME == 'EQP_GHG_REL_TYPE' && rowData.GHG_REL_TYPE!=null) return rowData.GHG_REL_TYPE;
		return data;
	};

	actions.notUniqueValue = function(uom,rowData){
		if(uom.COLUMN_NAME == 'EQP_FUEL_CONS_TYPE' && rowData.FUEL_TYPE!=null) return false;
		if(uom.COLUMN_NAME == 'EQP_GHG_REL_TYPE' && rowData.GHG_REL_TYPE!=null) return false;
		return true;
	}
</script>
@stop
