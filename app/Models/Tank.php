<?php

namespace App\Models;
use App\Models\FeatureTankModel;

class Tank extends FeatureTankModel
{
	protected $table = 'TANK';
	public  static  $idField = 'ID';
	public  static  $dateField = null;
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function Facility()
	{
		return $this->belongsTo('App\Models\Facility', 'FACILITY_ID', 'ID');
	}
	
}
