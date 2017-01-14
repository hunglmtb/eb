<?php

$tab = array();
$tab['FLOW'] =collect([
						(object)['NAME'=>'FDC VALUE', 'ID'=>'FlowDataFdcValue'],
						(object)['NAME'=>'STD VALUE', 'ID'=>'FlowDataValue'],
						(object)['NAME'=>'THEORETICAL', 'ID'=>'FlowDataTheor'],
						(object)['NAME'=>'ALLOCATION', 'ID'=>'FlowDataAlloc'],
						(object)['NAME'=>'COMPOSITION ALLOC', 'ID'=>'FlowCompDataAlloc'],
						(object)['NAME'=>'PLAN', 'ID'=>'FlowDataPlan'],
						(object)['NAME'=>'FORECAST', 'ID'=>'FlowDataForecast']
				]);

$tab['ENERGY_UNIT'] 	= collect([
								(object)['NAME'=>'FDC VALUE', 	'ID'=>'EnergyUnitDataFdcValue'],
								(object)['NAME'=>'STD VALUE', 	'ID'=>'EnergyUnitDataValue'],
								(object)['NAME'=>'THEORETICAL', 'ID'=>'EnergyUnitDataTheor'],
								(object)['NAME'=>'ALLOCATION', 	'ID'=>'EnergyUnitDataAlloc'],
								(object)['NAME'=>'COMPOSITION ALLOC', 'ID'=>'EnergyUnitCompDataAlloc'],
								(object)['NAME'=>'PLAN', 		'ID'=>'EnergyUnitDataPlan'],
								(object)['NAME'=>'FORECAST', 	'ID'=>'EnergyUnitDataForecast'],
								(object)['NAME'=>'Deferment', 	'ID'=>'DEFERMENT'],
								(object)['NAME'=>'Energy Unit', 'ID'=>'EnergyUnit'],
]);

$tab['STORAGE'] 	=	collect([
							(object)['NAME'=>'VALUE', 'ID'=>'StorageDataValue'],
							(object)['NAME'=>'PLAN', 'ID'=>'StorageDataPlan'],
							(object)['NAME'=>'FORECAST', 'ID'=>'StorageDataForecast']
						]);

$tab['TANK'] 	=	collect([
		(object)['NAME'=>'FDC', 'ID'=>'TankDataFdcValue'],
		(object)['NAME'=>'VALUE', 'ID'=>'TankDataValue'],
		(object)['NAME'=>'PLAN', 'ID'=>'TankDataPlan'],
		(object)['NAME'=>'FORECAST', 'ID'=>'TankDataForecast'],
]);

$tab['TICKET'] =collect([
		(object)['NAME'=>'FDC', 'ID'=>'RunTicketFdcValue'],
		(object)['NAME'=>'VALUE', 'ID'=>'RunTicketFdcValue'],
]);

$tab['EU_TEST'] =collect([
		(object)['NAME'=>'EuTestDataFdcValue', 	'ID'=>'EuTestDataFdcValue'],
		(object)['NAME'=>'EuTestDataStdValue', 	'ID'=>'EuTestDataStdValue'],
		(object)['NAME'=>'EuTestDataValue', 	'ID'=>'EuTestDataValue'],
]);

$tab['DEFERMENT'] =collect([
		(object)['NAME'=>'DEFERMENT', 'ID'=>'Deferment']
]);

$tab['QUALITY'] =collect([
		(object)['NAME'=>'QUALITY DATA', 'ID'=>'QltyData']
]);

$tab['KEYSTORE'] =collect([
		(object)['NAME'=>'KEYSTORE_INJECTION_POINT_DAY', 	'ID'=>'KeystoreInjectionPointDay'],
		(object)['NAME'=>'KEYSTORE_TANK_DATA_VALUE', 		'ID'=>'KeystoreTankDataValue'],
		(object)['NAME'=>'KEYSTORE_STORAGE_DATA_VALUE', 	'ID'=>'KeystoreStorageDataValue'],
]);

return [
        'tabTable'					=> 'tabTable',
		'keyField'					=> 'keyField',
		'flowId' 					=> 'X_FLOW_ID',
		'flowIdColumn' 				=> 'FLOW_ID',
 		'flowPhase' 				=> 'FLOW_PHASE',
		'flFlowPhase' 				=> 'FL_FLOW_PHASE',
		'euIdColumn' 				=> 'EU_ID',
		'euId' 						=> 'X_EU_ID',
		'euFlowPhase' 				=> 'EU_FLOW_PHASE',
		'euPhaseConfigId' 			=> 'EU_PHASE_CONFIG_ID',
		'eventType' 				=> 'EU_CONFIG_EVENT_TYPE',
		'tankId' 					=> 'X_TANK_ID',
		'tankIdColumn' 				=> 'TANK_ID',
		'tankFlowPhase' 			=> 'OBJ_FLOW_PHASE',
		'storageIdColumn' 			=> 'STORAGE_ID',
		'idColumn'					=>	['FLOW'=>'FLOW_ID','ENERGY_UNIT'=>'EU_ID','TANK'=>'TANK_ID'],
		'extraFields' 				=> 'extraFields',
		'mainFields' 				=> 'mainFields',
		'subProductFilterMapping' 	=> [
										'Tank'					=>	array('filterName'	=>'Tank',
																		'name'			=>'tank'),
										'EnergyUnit'			=>	array('filterName'	=>'Energy Unit',
																		'name'			=>'EnergyUnit'),
										'Storage'				=>	array('filterName'	=>'Storage',
																		'name'			=>'Storage',
																		'dependences'	=>['PdLiftingAccount']),
										'IntObjectType'			=>	array('filterName'	=>'Object Type',
																		'name'			=>'IntObjectType',
																		'dependences'	=>['ObjectName'],
																		'extra'			=>['Facility','IntObjectType','ExtensionValueType']),
										'ObjectName'			=>	array('filterName'	=>'Object Name',
																		'name'			=>'ObjectName',
																		'id'			=>'ObjectName',
// 																		'default'		=>['ID'=>0,'NAME'=>'All']
																		),
										'EnergyUnitGroup'		=>	array('filterName'	=>'Energy Unit Group',
																		'name'			=>'energyUnitGroup',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
										'CodeReadingFrequency'	=>	array('filterName'	=>'Record Frequency',
																		'name'			=>'CodeReadingFrequency',
																		'id'			=>'CodeReadingFrequency',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
										'CodeFlowPhase'			=>	array('filterName'	=>'Phase Type',
																		'name'			=>'CodeFlowPhase',
																		'id'			=>'CodeFlowPhase',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
										'ExtensionPhaseType'	=>	array('filterName'	=>'Phase',
																		'name'			=>'ExtensionPhaseType',
																		'id'			=>'ExtensionPhaseType',
																		'dependences'	=>['ObjectName'],
																		'extra'			=>['Facility','IntObjectType','ExtensionPhaseType']),
										'ExtensionValueType'	=>	array('filterName'	=>'Property',
																		'name'			=>'ExtensionValueType',
																		'id'			=>'ExtensionValueType'),
										'ExtensionDataSource'	=>	array('filterName'	=>'Data source',
																		'name'			=>'ExtensionDataSource',
																		'id'			=>'ExtensionDataSource'),
				
										'CodeEventType'			=>	array('filterName'	=>'Event Type',
																		'name'			=>'CodeEventType',
																		'id'			=>'CodeEventType',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
										'CodeAllocType'			=>	array('filterName'	=>'Alloc Type',
																		'name'			=>'CodeAllocType',
																		'id'			=>'CodeAllocType',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
										'CodeProductType'		=>	array('filterName'	=>'Product',
																		'name'			=>'CodeProductType',
																		'id'			=>'CodeProductType',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
										'CodeQltySrcType'		=>	array('filterName'	=>'Source Type',
																		'name'			=>'CodeQltySrcType',
																		'id'			=>'CodeQltySrcType',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
										'CodeCommentType'		=>	array('filterName'	=>'Comment Type',
																		'name'			=>'CodeCommentType'),
										'EquipmentGroup'		=>	array('filterName'	=>'Equipment Group',
																		'name'			=>'EquipmentGroup',
																		'id'			=>'EquipmentGroup',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
										'CodeEquipmentType'		=>	array('filterName'	=>'Equipment Type',
																		'name'			=>'CodeEquipmentType',
																		'id'			=>'CodeEquipmentType',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
										'CodeInjectPoint'		=>	array('filterName'	=>'Object Type',
																		'name'			=>'CodeInjectPoint',
																		'id'			=>'CodeInjectPoint'),
										'PdLiftingAccount'		=>	array('filterName'	=>'	Lifting Acct',
																		'name'			=>'PdLiftingAccount',
										),
										],
		'tab'							=>$tab
		// etc
];
