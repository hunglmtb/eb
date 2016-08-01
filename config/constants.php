<?php

$tab = array();
$tab['FLOW'] =[
		['NAME'=>'FDC VALUE', 'ID'=>'FlowDataFdcValue'],
		['NAME'=>'STD VALUE', 'ID'=>'FlowDataValue'],
		['NAME'=>'THEORETICAL', 'ID'=>'FlowDataTheor'],
		['NAME'=>'ALLOCATION', 'ID'=>'FlowDataAlloc'],
		['NAME'=>'COMPOSITION ALLOC', 'ID'=>'FlowCompDataAlloc'],
		['NAME'=>'PLAN', 'ID'=>'FlowDataPlan'],
		['NAME'=>'FORECAST', 'ID'=>'FlowDataForecast']
];

$tab['ENERGY_UNIT'] =[
		['NAME'=>'FDC VALUE', 'ID'=>'EnergyUnitDataFdcValue'],
		['NAME'=>'STD VALUE', 'ID'=>'EnergyUnitDataValue'],
		['NAME'=>'THEORETICAL', 'ID'=>'EnergyUnitDataTheor'],
		['NAME'=>'ALLOCATION', 'ID'=>'EnergyUnitDataAlloc'],
		['NAME'=>'COMPOSITION ALLOC', 'ID'=>'EnergyUnitCompDataAlloc'],
		['NAME'=>'PLAN', 'ID'=>'EnergyUnitDataPlan'],
		['NAME'=>'FORECAST', 'ID'=>'EnergyUnitDataForecast']
];

$tab['STORAGE'] =[
		['NAME'=>'TANK FDC', 'ID'=>'TankDataFdcValue'],
		['NAME'=>'TANK VALUE', 'ID'=>'TankDataValue'],
		['NAME'=>'TANK PLAN', 'ID'=>'TankDataPlan'],
		['NAME'=>'TANK FORECAST', 'ID'=>'TankDataForecast'],
		['NAME'=>'STORAGE VALUE', 'ID'=>'StorageDataValue'],
		['NAME'=>'STORAGE PLAN', 'ID'=>'StorageDataPlan'],
		['NAME'=>'STORAGE FORECAST', 'ID'=>'StorageDataForecast']
];

$tab['TICKET'] =[
		['NAME'=>'TICKET FDC', 'ID'=>'RunTicketFdcValue'],
		['NAME'=>'TICKET VALUE', 'ID'=>'RunTicketFdcValue'],
];

$tab['EU_TEST'] =[
		['NAME'=>'FDC VALUE', 'ID'=>'EuTestDataFdcValue'],
		['NAME'=>'STD VALUE', 'ID'=>'EuTestDataStdValue'],
		['NAME'=>'DAY VALUE', 'ID'=>'EuTestDataValue'],
];

$tab['DEFERMENT'] =[
		['NAME'=>'DEFERMENT', 'ID'=>'Deferment']
];

$tab['QUALITY'] =[
		['NAME'=>'QUALITY DATA', 'ID'=>'QltyData']
];

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
																		'name'			=>'Storage'),
										'IntObjectType'			=>	array('filterName'	=>'Object Type',
																		'name'			=>'IntObjectType',
																		'dependences'	=>['ObjectName'],
																		'extra'			=>['Facility','IntObjectType','ExtensionValueType']),
										'ObjectName'			=>	array('filterName'	=>'Object Name',
																		'name'			=>'ObjectName',
																		'id'			=>'ObjectName',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
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
																		'id'			=>'CodeAllocType'),
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
										],
		'tab'							=>$tab
		// etc
];
