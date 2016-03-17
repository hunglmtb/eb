<?php

namespace App\Models;
use App\Models\DynamicModel;

class FlowDataTheor extends DynamicModel
{
	protected $table = 'FLOW_DATA_THEOR';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	/* public function ProductionUnit()
	{
		return $this->belongsTo('App\Models\LoProductionUnit', 'PRODUCTION_UNIT_ID', 'ID');
	} */
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	/* public function Facility($fields=null)
	{
		if ($fields!=null&&is_array($fields)) {
			return $this->hasMany('App\Models\Facility', 'AREA_ID', 'ID')->select($fields);
		}
		return $this->hasMany('App\Models\Facility', 'AREA_ID', 'ID');
	}  */
	
}
