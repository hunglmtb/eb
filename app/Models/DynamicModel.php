<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicModel extends Model {
	
	protected $isOracleModel = false;
	protected $isReservedName = false;
	
	public function __construct() {
		parent::__construct();
		$this->isOracleModel = config('database.default')==='oracle';
		if ($this->isReservedName){
			$this->table = $this->table.'_';
		}
		
		if ($this->isOracleModel){
			$this->primaryKey = strtolower($this->primaryKey);
		}
	}
	
	public function __get($key)
	{
		if ($this->isOracleModel) {
			if (is_null($this->getAttribute($key))) {
				return $this->getAttribute(strtolower($key));
			} 
		} 
		return $this->getAttribute($key);
	}
	
	public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
	{
		return parent::belongsTo($related,$foreignKey,$this->isOracleModel?strtolower($otherKey):$otherKey,$relation);
	}
	
	public function hasMany($related, $foreignKey = null, $localKey = null)
	{
		return parent::hasMany($related,$foreignKey,$this->isOracleModel?strtolower($localKey):$localKey);
	}
	
	public function hasOne($related, $foreignKey = null, $localKey = null)
	{
		return parent::hasOne($related,$foreignKey,$this->isOracleModel?strtolower($localKey):$localKey);
	}
	
	public static function getTableName()
	{
		return with(new static)->getTable();
	}
}
