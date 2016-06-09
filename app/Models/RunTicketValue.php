<?php 
namespace App\Models; 
use App\Models\FeatureTicketModel; 

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
			/* if ( array_key_exists ( 'TANK_ID', $newData )) {
				$attributes['TANK_ID'] = $newData['TANK_ID'];
			} */
		}
		return $attributes;
	}
	
	public static function  getFdcValues($attributes){
		$newAttributes = [
							'ID'=>$attributes['ID'],
		];
		$fdcValues = RunTicketFdcValue::where($newAttributes)->first();
		return $fdcValues;
	}
	
	public static function calculateBeforeUpdateOrCreate(array &$attributes, array $values = []){
		if(array_key_exists('auto', $attributes)&&$attributes['auto']){
			$fields = [	"BEGIN_VOL",
						"END_VOL",
						"TICKET_GRS_VOL",
						"TICKET_NET_VOL",
						config("constants.keyField") 	=>	'TANK_ID'];
// 			$values[config ( "constants.flowPhase" )] = $values[config ( "constants.flowPhase" )];
			static::updateValues($attributes,$values,'TANK',$fields);
			$fdcValues = static::getFdcValues ( $attributes );
			if ($fdcValues) {
				$attributes = ['OCCUR_DATE'=>$fdcValues->OCCUR_DATE,
						'TICKET_NO'=>$fdcValues->TICKET_NO,
						'TANK_ID'=>$fdcValues->TANK_ID,
				];
				$values['OCCUR_DATE']= $fdcValues->OCCUR_DATE;
				$values['TANK_ID']= $fdcValues->TANK_ID;
// 				$values['LOADING_TIME']= $fdcValues->LOADING_TIME;
				$values['TICKET_NO']= $fdcValues->TICKET_NO;
			}
		}
		
		if(array_key_exists('FLOW_PHASE', $attributes)) unset($attributes['FLOW_PHASE']);
		return $values;
	}
} 
