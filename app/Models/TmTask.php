<?php 
namespace App\Models; 
use Carbon\Carbon;

 class TmTask extends EbBussinessModel 
{ 
	const STOPPED			= 0;
	const STARTING 			= 1;
	const READY 			= 2;
	const RUNNING			= 3;
	const CANCELLING		= 4;
	const DONE				= 5;
	const NONE				= 15;
	
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
	
    public static function loadStatus($option=null){
	    return collect([
			    		(object)['ID' =>	self::STOPPED		,'NAME' => 'STOPPED'   	],
			    		(object)['ID' =>	self::STARTING 		,'NAME' => 'STARTING'   ],
			    		(object)['ID' =>	self::READY 		,'NAME' => 'READY'   	],
			    		(object)['ID' =>    self::RUNNING	 	,'NAME' => 'RUNNING'   	],      
			    		(object)['ID' =>    self::CANCELLING  	,'NAME' => 'CANCELLING' ],  
			    		(object)['ID' =>    self::DONE			,'NAME' => 'DONE'   	],
	    				(object)['ID' =>    self::NONE			,'NAME' => ''   		],      
	    		
	    ]);
    }
	
	public function validateTaskCondition(){
 		$validated	= 	$this->runby==self::RUN_BY_SYSTEM&&
 						($this->status==self::STARTING||$this->status==self::READY)
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
					if ($startTime)			{
						$should	=	$now->gte($startTime);
// 	 					\Log::info("startTime $startTime now $now should $should gte {$now->gte($startTime)}");
					}
					if ($should&&$endTime) 	{
						$should	=	$now->lte($endTime);
// 	 					\Log::info("endTime $endTime now $now should $should lte {$now->lte($endTime)}");
					}
					break;
			}
		}
		return $should;
	}
	
	public function preRunTask($scheduleJob){
		$this->last_run		= Carbon::now();
		if (!$this->count_run)$this->count_run = 0;
		$this->count_run	= $this->count_run+1;
		$this->status		= self::RUNNING;
		$this->save();
	}
	
	public function afterRunTask($scheduleJob,$result){
		$this->last_check	= Carbon::now();
		$result	= $result?$result:"no return";
		//TODO check expire date
		if ($result instanceof \Exception) {
			$this->result	= "ERROR : ".$result->getMessage();
		}
		else if(is_string($result)){
			$this->result	= "RETURN : ".$result;
		}
		else{
			$this->result	= "RETURN object ";
		}
		
		if ($this->expire_date&&$this->expire_date->lte(Carbon::now())){
			$this->status	= self::DONE;
		}
		else
			$this->status	= $this->command==self::CANCELLING?self::STOPPED:self::READY;
		$this->command	= 0;
		$this->save();
	}
 } 
