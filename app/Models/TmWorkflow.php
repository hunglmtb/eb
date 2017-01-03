<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class TmWorkflow extends DynamicModel 
{ 
	protected $table = 'TM_WORKFLOW'; 
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = ['ID', 'NAME', 'INTRO', 'CDATE', 'AUTHOR', 'RUN_TASK', 'ISRUN', 'DATA', 'STATUS'];
} 
