<?php

namespace App\Models;
use App\Models\DynamicModel;

class FlowDataFdcValue extends DynamicModel
{
	protected $table = 'FLOW_DATA_FDC_VALUE';
	protected $primaryKey = 'ID';
	
	protected $fillable  = ['FLOW_ID',
							'OCCUR_DATE',
							'ACTIVE_HRS',
							'LAST_DATA_READ',
							'COMP_LAB_ID',
							'DISP',
							'OBS_API',
							'OBS_TEMP',
							'OBS_PRESS',
							'RECORD_FREQUENCY',
							'BEGIN_READING_VALUE',
							'END_READING_VALUE',
							'FL_DATA_GRS_VOL',
							'FL_DATA_NET_VOL',
							'FL_DATA_SW_PCT',
							'FL_DATA_GRS_MASS',
							'FL_DATA_NET_MASS',
							'FL_DATA_GRS_WTR_VOL',
							'FL_DATA_GRS_WTR_MASS',
							'FL_DATA_GRS_ENGY',
							'FL_DATA_GRS_PWR',
							'FL_DATA_DENS',
							'FL_VOL_UOM',
							'FL_MASS_UOM',
							'FL_ENGY_UOM',
							'FL_POWR_UOM',
							'TEMP_UOM',
							'PRESS_UOM',
							'DENS_UOM',
							'CTV',
							'STATUS_BY',
							'STATUS_DATE',
							'RECORD_STATUS'];
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	/* public function ProductionUnit()
	{
		return $this->belongsTo('App\Models\LoProductionUnit',
'PRODUCTION_UNIT_ID',
'ID');
	} */
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	/* public function Facility($fields=null)
	{
		if ($fields!=null&&is_array($fields)) {
			return $this->hasMany('App\Models\Facility',
'AREA_ID',
'ID')->select($fields);
		}
		return $this->hasMany('App\Models\Facility',
'AREA_ID',
'ID');
	}  */
	
}
