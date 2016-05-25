<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class TmWorkflowTask extends DynamicModel 
{ 
	protected $table = 'tm_workflow_task'; 
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = [
			'ID', 'WF_ID', 'NAME', 'TYPE', 'TASK_GROUP', 'TASK_CODE', 'NODE_CONFIG', 'TASK_CONFIG', 
			'NEXT_TASK_CONFIG', 'PREV_TASK_CONFIG', 'RUNBY', 'USER', 'ISBEGIN', 'ISRUN', 'RESULT', 
			'CDATE', 'STATUS', 'LOG', 'START_TIME', 'FINISH_TIME'
	];	
} 