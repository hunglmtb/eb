<?php 
namespace App\Models; 
use App\Models\FeatureTicketModel; 
use App\Models\Tank; 
use Carbon\Carbon;

 class RunTicketValue extends FeatureTicketModel 
{ 
	protected $table = 'RUN_TICKET_VALUE';
	protected $primaryKey = 'ID';
	protected $dates = ['OCCUR_DATE'];
	protected $fillable  = ['OCCUR_DATE', 
							'TICKET_NO', 
							'TANK_ID', 
							'CARRIER_ID', 
							'BEGIN_LEVEL', 
							'END_LEVEL', 
							'BEGIN_VOL', 
							'END_VOL', 
							'SW', 
							'TICKET_GRS_VOL', 
							'TICKET_NET_VOL', 
							'TICKET_DENSITY', 
							'TICKET_GRS_MASS', 
							'TICKET_NET_MASS', 
							'TICKET_WTR_VOL', 
							'LOADING_TIME'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if ( array_key_exists ( 'auto', $newData )) {
			return $newData;
		}
		else {
			$attributes = parent:: getKeyColumns($newData,$occur_date,$postData);
			if ( array_key_exists ( 'FLOW_PHASE', $newData )) {
				$attributes['FLOW_PHASE'] = $newData['FLOW_PHASE'];
			}
			if ( array_key_exists ( 'TANK_ID', $newData )) {
				$attributes['TANK_ID'] = $newData['TANK_ID'];
			}
		}
		return $attributes;
	}
	
	public static function  getFdcValues($attributes){
		/* if (strpos($attributes['ID'], 'NEW_RECORD') !== false) {
			unset($attributes['ID']);
		} */
		if (strpos($attributes['ID'], 'NEW_RECORD') !== false) {
			/* if ( array_key_exists ( 'auto', $attributes )) unset($attributes['auto']);
// 			if ( array_key_exists ( 'isAdding', $attributes )) unset($attributes['isAdding']);
			if ( array_key_exists ( 'ID', $attributes )) unset($attributes['ID']);
			if ( array_key_exists ( 'FLOW_PHASE', $attributes )) unset($attributes['FLOW_PHASE']); */
			$newAttributes = [	'OCCUR_DATE'=>$attributes['OCCUR_DATE'],
								'TICKET_NO'=>$attributes['TICKET_NO'],
								'TANK_ID'=>$attributes['TANK_ID'],
			];
		}
		else{
			$newAttributes = [
								'ID'=>$attributes['ID'],
			];
		}
		$fdcValues = RunTicketFdcValue::where($newAttributes)->first();
		return $fdcValues;
	}
	
	public static function calculateBeforeUpdateOrCreate(array &$attributes, array $values = []){
		if(array_key_exists('auto', $attributes)&&$attributes['auto']){
			if (strpos($attributes['ID'], 'NEW_RECORD') !== false&&
					(!array_key_exists ( 'OCCUR_DATE', $attributes )||
							!array_key_exists ( 'TICKET_NO', $attributes )||
							!array_key_exists ( 'TANK_ID', $attributes ))) break;
					
			if(array_key_exists('OCCUR_DATE', $attributes)){
				$occur_date = $attributes['OCCUR_DATE'];
				$occur_date = Carbon::parse($occur_date);
				$occur_date->hour = 0;
				$occur_date->minute = 0;
				$occur_date->second = 0;
				$attributes['OCCUR_DATE'] = $occur_date;
			}
									
			$fields = [	"BEGIN_VOL",
						"END_VOL",
						"TICKET_GRS_VOL",
						"TICKET_NET_VOL",
						config("constants.keyField") 	=>	'TANK_ID'];
			if(!array_key_exists('FLOW_PHASE', $attributes)) {
				$tank = Tank::where('ID','=',$attributes['TANK_ID'])->select('PRODUCT')->first();
				$attributes['FLOW_PHASE'] = $tank?$tank->PRODUCT:null;
			}
			static::updateValues($attributes,$values,'TANK',$fields);
			
			$fdcValues = static::getFdcValues ( $attributes );
			if ($fdcValues) {
				$newValues = $fdcValues->toArray();
				foreach ( $newValues as $column => $vl ) {
					if (!$vl) {
						unset($newValues[$column]);
					}
				}
				$values = array_merge($newValues, $values);
				
			}
			$attributes = ['OCCUR_DATE'=>$values['OCCUR_DATE'],
					'TICKET_NO'=>$values['TICKET_NO'],
					'TANK_ID'=>$values['TANK_ID'],
			];
		}
		
		if(array_key_exists('FLOW_PHASE', $attributes)) unset($attributes['FLOW_PHASE']);
		return $values;
	}
} 
