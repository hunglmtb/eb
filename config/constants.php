<?php

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
										'EnergyUnitGroup'		=>	array('filterName'	=>'Energy Unit Group',
																		'name'			=>'energyUnitGroup',
																		'default'		=>['ID'=>'0','NAME'=>'No Group']),
										'CodeReadingFrequency'	=>	array('filterName'	=>'Record Frequency',
																		'name'			=>'CodeReadingFrequency',
																		'id'			=>'CodeReadingFrequency',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
										'CodeFlowPhase'			=>	array('filterName'	=>'Phase Type',
																		'name'			=>'CodeFlowPhase',
																		'id'			=>'CodeFlowPhase',
																		'default'		=>['ID'=>0,'NAME'=>'All']),
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
				
										],
		// etc
];
