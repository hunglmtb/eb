<?php

namespace App\Models;
use App\Models\DynamicModel;

class LogUser extends DynamicModel
{
	protected $table = 'LOG_USER';
	protected $autoFillableColumns = false;
	
	protected $fillable  = ['USERNAME', 
							'LOGIN_TIME', 
							'LOGOUT_TIME', 
							'SESSION_ID', 
							'IP'];
	
}
