<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class AllocRunner extends DynamicModel 
{ 
	protected $table = 'alloc_runner'; 
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = [
			'ID',
			'CODE',
			'NAME',
			'JOB_ID',
			'ORDER',
			'ALLOC_TYPE',
			'THEOR_PHASE',
			'THEOR_VALUE_TYPE',
			'LAST_RUN'
	];
} 
