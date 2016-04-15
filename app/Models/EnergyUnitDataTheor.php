<?php

namespace App\Models;
use App\Models\DynamicModel;
use App\Models\CfgFieldProps;

class EnergyUnitDataTheor extends DynamicModel
{
	protected $table = 'ENERGY_UNIT_DATA_THEOR';
	
	protected $primaryKey = 'ID';
	protected $fillable  = ['EU_ID',
							 'OCCUR_DATE',
							 'ACTIVE_HRS',
							 'RECORD_FREQUENCY',
							 'DISP',
							 'FL_DATA_GRS_VOL',
							 'FL_DATA_NET_VOL',
							 'FL_DATA_SW_PCT',
							 'FL_DATA_GRS_WTR_VOL',
							 'FL_DATA_GRS_MASS',
							 'FL_DATA_NET_MASS',
							 'FL_DATA_GRS_WTR_MASS',
							 'FL_DATA_GRS_ENGY',
							 'FL_DATA_GRS_PWR',
							 'FL_DATA_DENS',
							 'STATUS_BY',
							 'STATUS_DATE',
							 'RECORD_STATUS'];
	
	public static function calculateBeforeUpdateOrCreate(array $attributes, array $values = [],$options=null){
	
		if($options
				&&array_key_exists("FLOW_ID",$attributes)
				&&array_key_exists("OCCUR_DATE",$attributes)){
			
			$object_id = $attributes["FLOW_ID"];
			$occur_date = $attributes["OCCUR_DATE"];
			$fields = CfgFieldProps::where('TABLE_NAME', '=', FlowDataFdcValue::getTableName())
									->where('USE_FDC', '=', 1)
									->where('COLUMN_NAME', '!=','CTV')
									->orderBy('FIELD_ORDER')
									->select('COLUMN_NAME')
									->get();
			$theoFields = CfgFieldProps::where('TABLE_NAME', '=', FlowDataTheor::getTableName())
									->where('USE_FDC', '=', 1)
									->orderBy('FIELD_ORDER')
									->select('COLUMN_NAME')
									->get();
			
			$fieldArray =array_column($fields->toArray(), 'COLUMN_NAME');
			$theoFieldArray =array_column($theoFields->toArray(), 'COLUMN_NAME');
			$fdcValues = FlowDataFdcValue::where(array(['FLOW_ID',$object_id],['OCCUR_DATE',$occur_date]))
											->select($fieldArray)
											->first();
			
			foreach ($theoFieldArray as $field){
				if (!array_key_exists($field, $values)) {
					$values[$field]= $fdcValues->$field;
				}
			}
		}
		return $values;
	}
}
