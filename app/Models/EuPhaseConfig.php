<?php

namespace App\Models;
use App\Models\DynamicModel;

class EuPhaseConfig extends DynamicModel
{
	protected $table = 'EU_PHASE_CONFIG';
	protected $primaryKey = 'ID';
	
	public function CodeFlowPhase()
	{
		return $this->belongsTo('App\Models\CodeFlowPhase', 'PHASE_ID', $this->primaryKey);
	}
}
