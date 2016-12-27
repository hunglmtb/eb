<?php

namespace App\Models;
use App\Models\UomModel;
use App\Trail\ObjectNameLoad;

class Facility extends UomModel
{
 	use ObjectNameLoad;
	
	protected $table = 'FACILITY';
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function Area()
	{
		return $this->belongsTo('App\Models\LoArea', 'AREA_ID', 'ID');
	}
	
	public function Tank($fields=null){
		return $this->hasMany('App\Models\Tank', 'FACILITY_ID', 'ID');
	}
	
	public function EnergyUnitGroup($fields=null){
		return $this->hasMany('App\Models\EnergyUnitGroup', 'FACILITY_ID', 'ID');
	}
	
	public function EnergyUnit($fields=null){
		return $this->hasMany('App\Models\EnergyUnit', 'FACILITY_ID', 'ID');
	}
	
	public function Storage($fields=null){
		return $this->hasMany('App\Models\Storage', 'FACILITY_ID', 'ID');
	}
	
	
	public static function getEntries($facility_id=null,$product_type = 0){
		if ($facility_id&&$facility_id>0)$wheres = ['ID'=>$facility_id];
		else $wheres = [];
	
		if ($product_type>0) {
// 			$wheres['PHASE_ID'] = $product_type;
		}
		$entries = static ::where($wheres)->select('ID','NAME')->orderBy('NAME')->get();
		return $entries;
	}
	
	public function PlotViewConfig(){
		$result	= PlotViewConfig::where("CONFIG",'like',"%#$this->ID:%")->get();
		return $result;
	}
	
	
}
