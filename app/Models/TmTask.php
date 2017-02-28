<?php 
namespace App\Models; 
use Carbon\Carbon;

 class TmTask extends EbBussinessModel 
{ 
	const RUNNING			= 1;
	const STARTING 			= 7;
	const RUNNING_UPDATE	= 15;
	const STOP				= 0;
	
	const RUN_BY_SYSTEM 	= 1;
	const RUN_BY_USER 		= 2;
	
	protected $primaryKey = 'id';
	protected $table = 'TM_TASK';
	protected $dates = ['cdate','last_run','last_check','next_run'];
	protected $fillable  = ['name',
							'runby',
							'user',
							'expire_date',
							'intro',
							'time_config',
							'task_group',
							'task_code',
							'task_config',
							'author',
							'count_run',
							'result',
							'cdate',
							'status',
							'last_run',
							'last_check',
							'next_run'];
	
	public function setTaskConfigAttribute($value){
		$this->attributes['task_config'] = json_encode($value);
	}
	
	public function setTimeConfigAttribute($value){
		$this->attributes['time_config'] = json_encode($value);
	}
	
 	public function getTimeConfigAttribute($value){
        return json_decode ( $value , true );
    }
    
    public function getTaskConfigAttribute($value){
    	return json_decode ( $value , true );
    }
	
	public function handleScheduleJob(){
		$validated	= $this->validateTaskCondition();
		if (!$validated) return;
		//TODO
		$this->last_run	= Carbon::now();
		$this->status	= self::RUNNING;
		$this->save();
	}
	
	public function validateTaskCondition(){
 		$validated	= 	$this->runby==self::RUN_BY_SYSTEM&&
 						($this->status==self::STARTING||$this->status==self::RUNNING||$this->status==self::RUNNING_UPDATE)
 						&&($this->expire_date&&$this->expire_date->gt(Carbon::now()));
 		
		$validated	= $validated&&$this->shouldRunByTimeConfig();
 		return $validated;
	}
	
	public function shouldRunByTimeConfig(){
		$should		= false;
		$lastRun	= $this->last_run;
		return $should;
	}
 } 
