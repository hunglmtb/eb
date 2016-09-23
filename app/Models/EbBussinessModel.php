<?php

namespace App\Models;

use App\Exceptions\DataInputException;
use Carbon\Carbon;
use App\Models\DynamicModel;
use App\Models\AuditTrail;
use App\Models\EuTestDataValue;

class EbBussinessModel extends DynamicModel {
	
	protected $objectModel = null;
	protected static $enableCheckCondition = false;
	protected $disableUpdateAudit = true;
	public  static  $idField = 'ID';
	
	protected $excludeColumns = [];
	public  static $ignorePostData = false;
	protected $oldValues = null;
	protected $isAuto = false;
	
	public static function getKeyColumns(&$newData,$occur_date,$postData)
	{
		return [static::$idField => $newData[static::$idField]];
	}
	
	public static function findManyWithConfig($updatedIds) {
		return parent::findMany ( $updatedIds );
	}
	
	public static function deleteWithConfig($mdlData) {
		$valuesIds = array_values($mdlData);
		static::whereIn('ID', $valuesIds)->delete();
	}
	
	public static function updateValues(array $attributes, array &$values = [], $type, $fields) {
		$unnecessary = true;
		foreach ( $fields as $field ) {
			$unnecessary = $unnecessary && array_key_exists ( $field, $values ) && $values [$field] != null && $values [$field] != '';
		}
		
		if ($unnecessary) return;
		if( !array_key_exists ( config ( "constants.flowPhase" ), $attributes )) return ;
		$flow_phase = $attributes [config ( "constants.flowPhase" )];
		// OIL or GAS
		if (($flow_phase == 1 || $flow_phase == 2 || $flow_phase == 21)) {
			$fdcValues = static::getFdcValues ( $attributes );
			if (!$fdcValues) return ;
			
			$object_id = $attributes [$fields [config ( "constants.keyField" )]];
			$occur_date = $fdcValues ["OCCUR_DATE"];
			
			$T_obs = $fdcValues ["OBS_TEMP"];
			$P_obs = $fdcValues ["OBS_PRESS"];
			$API_obs = $fdcValues ["OBS_API"];
			
			$_Bg = \FormulaHelpers::calculateBg ( $flow_phase, $T_obs, $P_obs, $API_obs, $occur_date, $object_id, $type );
			
			foreach ( $fields as $field ) {
				if (config ( "constants.keyField" ) == $field) {
					continue;
				}
				// if($ctv==1){
				if (array_key_exists ( $field, $values )) {
					break;
				}
				$_vFDC = $fdcValues->$field;
				if (static::$enableCheckCondition && $_Bg == null && $_vFDC != '') {
					throw new DataInputException ( "Can not calculate conversion for $type ID: $object_id (check API, Temprature, Pressure value)" );
					return;
				}
				$values [$field] = $_vFDC;
				switch ($flow_phase) {
					case 1 :
						$_v = null;
						if ($_vFDC && $_Bg != null)
							$_v = $_vFDC * $_Bg;
						$values [$field] = $_v;
						break;
					case 2 :
					case 21 :
						if ($_Bg == null) {
							$values [$field] = null;
						} else {
							if ($_Bg == 0) {
								if ((($values [$field] != null && $values [$field] != ''))) {
									throw new DataInputException ( "Wrong gas conversion number (zero) for $type ID: $object_id" );
								}
							} else {
								$values [$field] = $values [$field] / $_Bg;
							}
						}
						break;
					default :
						break;
				}
			}
		}
	}
	
	public static function updateOrCreateWithCalculating(array $attributes, array $values = []) {
		$auto = array_key_exists("auto", $values)&&$values["auto"];
		if (array_key_exists("isAdding", $values)&&$values["isAdding"]&&!$auto) {
			$instance = new static((array) $values);
	        $instance->exists = false;
	        foreach ( $values as $column => $value ) {
	        	if (!$instance->isFillable($column)) {
	        		unset($values[$column]);
	        	}
	        }
			$instance = $instance->checkAndSave($values);
// 			$instance->fill($values)->save();
// 	        $values = static::calculateBeforeUpdateOrCreate ( $attributes, $values );
	        return $instance;
		}
		
		$values = static::calculateBeforeUpdateOrCreate ( $attributes, $values );
		$instance = null;
		if ($values&&is_array($values)&&count($values)>0) {
			$instance = static::firstOrNew($attributes);
			if($instance->isNotAvailable($attributes)) return null;
					
			$instance->isAuto = $auto;
			$oldValues = [];
			foreach ( $values as $column => $value ) {
				$oldValues[$column]= $instance->$column;
				if (!$instance->isFillable($column)) {
					unset($values[$column]);
				}
			}
			$instance->fill($values)->save();
			$instance->oldValues = $oldValues;
		}
		return $instance; 
	}
	
	public static function calculateBeforeUpdateOrCreate(array &$attributes, array $values = []){
		return $values;
	}
	
	public function isNotAvailable($attributes){
		return false;
	}
	
	public static function updateWithFormularedValues($values,$object_id,$occur_date,$flow_phase) {
		$newData = [static::$idField=>$object_id];
		$attributes = static::getKeyColumns($newData,$occur_date,null);
		$values = array_merge($values,$newData);
		return parent::updateOrCreate($attributes,$values);
	}
	
	public static function updateWithQuality($record,$occur_date) {
		return false;
	}
	
	public function getObjectDesc($rowID) {
		$mdl = 'App\Models\\'.$this->objectModel;
		return $mdl::find($rowID);
	}
	
	public function afterSaving($postData) {
	}
	
	public function checkAndSave(&$values) {
		$this->fill($values)->save();
		return $this;
	}
	
	public function updateDependRecords($occur_date,$values,$postData) {
		return null;
	}
	
	public function updateAudit($attributes,$values,$postData) {
		if ($this->disableUpdateAudit)  return;
		$current = Carbon::now();
		$current_username =auth()->user()->username;
		$rowID = $attributes[static::$idField];
		$facility_id = $postData['Facility'];
		$objectDesc = $this->getObjectDesc($rowID);
		$oldValue = null;
		$newValue = null;
		$records = array();
		$shouldInsertAudit = true;
		
		if ($this->wasRecentlyCreated) {
			$action = "New record";
			$columns = ['New'];
		}
		else{
			$action = "Update value";
			$columns = $values;
		}
		
		
		foreach ( $columns as $column => $columnValue ) {
			if (!$this->wasRecentlyCreated) {
				$shouldInsertAudit = false;
				if (!in_array($column, $this->excludeColumns)) {
					if(isset($this->oldValues)) {
						$original = $this->oldValues;
						if (array_key_exists($column, $original)){
							$oldValue = $original[$column];
							$newValue = $this->$column;
							$shouldInsertAudit = $oldValue!=$newValue;
						}
					}
				}
			}
					
			if ($shouldInsertAudit) {
				$records[] = array('ACTION'=>$action,
								'FACILITY_ID'=>$facility_id,
								'WHO'=>$current_username,
								'WHEN'=>$current, 
								'TABLE_NAME'=>$this->table,
								'COLUMN_NAME'=>$column,
								'RECORD_ID'=>$rowID,
								'OBJECT_DESC'=>$objectDesc->NAME,
								'REASON'=>1,
								'OLD_VALUE'=>$oldValue,
								'NEW_VALUE'=>$newValue);
			}
		}
		
		if (count($records)>0) {
			AuditTrail::insert($records);
		}
	}
	
	
	public static function getEUTest($object_id,$occur_date) {
		$rowTest=EuTestDataValue::where([['EU_ID',$object_id],
				['TEST_USAGE',1]])
				->whereDate('EFFECTIVE_DATE', '<=', $occur_date)
				->orderBy('EFFECTIVE_DATE','desc')
				->first();
		return $rowTest;
	}
	
	
	public static function getCalculateFields() {
		return null;
	}
}
