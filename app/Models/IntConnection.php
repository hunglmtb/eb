<?php

namespace App\Models;
use App\Models\DynamicModel;

class IntConnection extends DynamicModel
{
	protected $table = 'INT_CONNECTION';
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = [
			'ID',
			'NAME',
			'SERVER',
			'USER_NAME',
			'PASSWORD',
			'TYPE'
	];
	
}
