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
}
