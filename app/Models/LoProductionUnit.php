<?php

namespace App\Models;
use App\Models\DynamicModel;

class LoProductionUnit extends DynamicModel
{
	protected $table = 'LO_PRODUCTION_UNIT';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function LoArea($option=null){
		if ($option) {
			$userDataScope			= UserDataScope::where("USER_ID",auth()->user()->ID)->first();
			$DATA_SCOPE_FACILITY	= $userDataScope?$userDataScope->FACILITY_ID:null;
			if($DATA_SCOPE_FACILITY&&$DATA_SCOPE_FACILITY!=""&&$DATA_SCOPE_FACILITY!="0"&&$DATA_SCOPE_FACILITY!=0){
				$facilityIds		= explode(",", $DATA_SCOPE_FACILITY);
				$areas 				= LoArea::whereHas('Facility', function ($query) use($facilityIds) {
											$query->whereIn('ID',  $facilityIds);
										})
										->where("PRODUCTION_UNIT_ID",$this->ID)
										->get();
				return $areas;
			}
		}
		return $this->hasMany('App\Models\LoArea', 'PRODUCTION_UNIT_ID', 'ID');
	}
}
