<?php

namespace App\Models;
use App\Models\DynamicModel;

class Flow extends DynamicModel
{
	protected $table = 'FLOW';
	protected $primaryKey = 'ID';
	
	
	public function CodeFlowPhase()
	{
		return $this->belongsTo('App\Models\CodeFlowPhase', 'PHASE_ID', $this->primaryKey);
	}
}
