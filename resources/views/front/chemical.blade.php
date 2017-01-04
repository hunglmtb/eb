<?php
$currentSubmenu ='/fo/chemical';
$tables = ['KeystoreTankDataValue'			=>['name'=>'CHEMICAL TANK VALUE'],
		'KeystoreStorageDataValue'			=>['name'=>'CHEMICAL STORAGE VALUE'],
		'KeystoreInjectionPointChemical'	=>['name'=>'CHEMICAL INJECTION POINT']
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
			idName:['ID','KEYSTORE_TANK_ID','OCCUR_DATE'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};

	var osaveSuccess = actions.saveSuccess;
	actions.saveSuccess = function (data,noDelete){
		osaveSuccess(data,noDelete);
		actions.doLoad(true);
	};
</script>
@stop
