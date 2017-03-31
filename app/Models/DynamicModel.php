<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicModel extends Model {
	
	protected $primaryKey = 'ID';
	protected $isOracleModel = false;
	protected $isReservedName = false;
	public 		$timestamps = false;
	protected $autoFillableColumns = true;
	protected static $isAddAllAsDefault	= false;
	
	public function __construct() {
		parent::__construct();
		$this->isOracleModel = config('database.default')==='oracle';
		if ($this->isReservedName){
			$this->table = $this->table.'_';
		}
		
		if ($this->isOracleModel){
			$this->primaryKey = strtolower($this->primaryKey);
		}
		
		if ($this->autoFillableColumns) {
			$fillable = $this->getTableColumns();
			if(($key = array_search($this->primaryKey, $fillable)) !== false) {
				unset($fillable[$key]);
			}
			$this->fillable = $fillable;
		}
	}
	
	public function setTable($tableName){
		$this->table = $tableName;
	}
	
	public function getTableColumns() {
		return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
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
	
	public static function getDateFields(){
		return with(new static)->getDates();
	}
	
	public static function getAll(){
		/* $instance = new static;  
		if (method_exists($instance, "loadActive")) 
			$entries = $instance->loadActive();
		else */
		$entries = static ::all();
		return $entries;
	}
	
	public static function getOptionDefault($modelName,$unit){
		$aOption 		= ["modelName"	=> $modelName];
		if (static::$isAddAllAsDefault) $aOption['default']	= ['ID'=>0,'NAME'=>'All'];
		return $aOption;
	}
	
	public static function loadActive(){
		return static :: where("ACTIVE","=",1)->orderBy("ORDER")->get();
	}
}
