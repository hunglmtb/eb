<?php

return [
        'tabTable'					=> 'tabTable',
		'flowPhase' 				=> 'flowPhase',
		'flowId' 					=> 'FLOW_ID',
		'flFlowPhase' 				=> 'FL_FLOW_PHASE',
		'euId' 						=> 'EU_ID',
		'euFlowPhase' 				=> 'EU_FLOW_PHASE',
		'euPhaseConfigId' 			=> 'EU_PHASE_CONFIG_ID',
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
										],
		// etc
];
