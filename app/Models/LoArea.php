<?php

namespace App\Models;
use App\Models\DynamicModel;

class LoArea extends DynamicModel
{
	protected $table = 'LO_AREA';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function ProductionUnit()
	{
		return $this->belongsTo('App\Models\LoProductionUnit', 'PRODUCTION_UNIT_ID', 'ID');
	}
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function Facility($option=null){
		if ($option) {
			$userDataScope			= UserDataScope::where("USER_ID",auth()->user()->ID)->first();
			$DATA_SCOPE_FACILITY	= $userDataScope?$userDataScope->FACILITY_ID:null;
			if($DATA_SCOPE_FACILITY&&$DATA_SCOPE_FACILITY!=""&&$DATA_SCOPE_FACILITY!="0"&&$DATA_SCOPE_FACILITY!=0){
				$facilityIds		= explode(",", $DATA_SCOPE_FACILITY);
				return Facility::whereIn('ID',$facilityIds)->where("AREA_ID","=",$this->ID)->get();
			}
		}
		return $this->hasMany('App\Models\Facility', 'AREA_ID', 'ID');
	}
	
}
