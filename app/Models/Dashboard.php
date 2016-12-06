<?php

namespace App\Models;

class Dashboard extends EbBussinessModel {
	protected $table = 'DASHBOARD';
	
	protected $fillable = [ 
							'NAME',
							'TYPE',
							'BACKGROUND',
							'USER_NAME' 
					];
} 
