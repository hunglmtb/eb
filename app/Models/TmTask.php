<?php 
namespace App\Models; 
use Carbon\Carbon;

 class TmTask extends EbBussinessModel 
{ 
	const STOPPED			= 0;
	const STARTING 			= 1;
	const QUEUED 			= 2;
	const RUNNING			= 3;
	const CANCELLING		= 4;
	const DONE				= 5;
	
	const RUN_BY_SYSTEM 	= 1;
	const RUN_BY_USER 		= 2;
	
	protected $table = 'TM_TASK';
	protected $dates = ["expire_date",'slast_run','last_check','next_run',"cdate"];
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
	
	
	public function validateTaskCondition(){
 		$validated	= 	$this->runby==self::RUN_BY_SYSTEM&&
 						($this->status==self::STARTING||$this->status==self::QUEUED)
 						&&(!$this->expire_date||$this->expire_date&&$this->expire_date->gt(Carbon::now()));
 		
		$validated	= $validated&&$this->shouldRunByTimeConfig();
 		return $validated;
	}
	
	public function shouldRunByTimeConfig(){
		$should		= false;
		$now		= Carbon::now();
		$timeConfig	= $this->time_config;
		if ($timeConfig) {
			$frequenceMode	= array_key_exists('FREQUENCEMODE'	, $timeConfig)?$timeConfig["FREQUENCEMODE"]				:"ONCETIME";
			$intervalDay	= array_key_exists('INTERVALDAY'	, $timeConfig)?$timeConfig["INTERVALDAY"]				:0;
			$startTime		= array_key_exists('STARTTIME'		, $timeConfig)?Carbon::parse($timeConfig["STARTTIME"])	:null;
			$endTime		= array_key_exists('ENDTIME'		, $timeConfig)?Carbon::parse($timeConfig["ENDTIME"])	:null;
			$weekDays		= array_key_exists('WEEKDAY'		, $timeConfig)?$timeConfig["WEEKDAY"]					:0;
			$days			= array_key_exists('MONTHDAY'		, $timeConfig)?$timeConfig["MONTHDAY"]					:0;
			$months			= array_key_exists('MONTH'			, $timeConfig)?$timeConfig["MONTH"]						:0;
			
			switch ($frequenceMode) {
				case "ONCETIME":
					$should		= true;
					if ($startTime)			$should	=	$now->gte($startTime);
					if ($should&&$endTime) 	$should	=	$now->lte($endTime);
					break;
			}
		}
		return $should;
	}
	
	public function preRunTask($scheduleJob){
		$this->last_run		= Carbon::now();
		if ($this->count_run)$this->count_run = 0;
		$this->count_run	= $this->count_run+1;
		$this->status		= self::RUNNING;
		$this->save();
	}
	
	public function afterRunTask($scheduleJob,$result){
		$this->last_run	= Carbon::now();
		//TODO check expire date
		if ($this->expire_date&&$this->expire_date->lte(Carbon::now())){
			$this->status	= self::DONE;
		}
		else
			$this->status	= $this->status==self::CANCELLING?self::STOPPED:self::QUEUED;
		$this->save();
	}
 } 
