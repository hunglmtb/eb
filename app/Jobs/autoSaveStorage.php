<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TmWorkflowTask;
use App\Models\Tank;
use App\Models\Storage;
use  DB, Carbon\Carbon, Mail;
use App\Http\Controllers\WorkflowProcessController;

class autoSaveStorage extends Job implements ShouldQueue, SelfHandling
{
	use InteractsWithQueue, SerializesModels;
    protected $param=[], $log, $error_count = 0, $alloc_act = "";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		//All dates in 'Y-m-d' format
		//\Log::info ($this->param);
		$task_id = 0;
		if(isset($this->param['taskid'])){
			$task_id = $this->param['taskid'];
			$date_type = $this->param['type'];			
			$facility_id = $this->param['facility'];
			$product_type = $this->param['product_type'];
			$from_date = $this->param['from_date'];
			$to_date = $this->param['to_date'];
			$email = $this->param['email'];
			
			if($date_type == "day"){
				$date = date('Y-m-d');
				$from_date = date('Y-m-d', strtotime($date .' -1 day'))."";
				$to_date = $from_date;
			}
			else if($date_type == "month"){
				$date = date('Y-m-d');
				$from_date = date('Y-m-01', strtotime($date .' -1 month'))."";
				$to_date = $from_date;
			}
			$this->_log("from_date: $from_date, to_date: $to_date",2);	
		}
		if(!$task_id){
    		$this->_log("Unknown task to perform",1);
    		exit();
		}

    	\Log::info ("date: $from_date, $to_date");
		$occur_date=$from_date;

		//Get object Ids
		$occur_date = $from_date;
//     	$obj = Tank::getTableName();
    	$where = ['FACILITY_ID' => $facility_id, 'FDC_DISPLAY' => 1];
    	if ($product_type>0) $where["PRODUCT"]= $product_type;

//      	\DB::enableQueryLog();
    	$dataSet = Tank::where($where)
				    	->select(
				    			"ID as OBJECT_ID"
				    			) 
  		    			->get();
		$objectIdsTank = [];
		foreach($dataSet as $row){
			$objectIdsTank[] = $row->OBJECT_ID;
		}
		
// 		$obj = Storage::getTableName();
    	$dataSet = Storage::where($where)
				    	->select(
				    			"ID as OBJECT_ID"
				    			) 
  		    			->get();
		$objectIdsStorage = [];
		foreach($dataSet as $row){
			$objectIdsStorage[] = $row->OBJECT_ID;
		}
		
		//Save data
		if($from_date && $to_date){
			$d1 = $from_date;
			$d2 = $to_date;
			while (strtotime($d1) <= strtotime($d2)) {
				$occur_date=$d1;
				saveDataTank($occur_date,$facility_id,$objectIdsTank);
				saveDataStorage($occur_date,$facility_id,$objectIdsStorage);
				$d1 = date ("Y-m-d", strtotime("+1 day", strtotime($d1)));
			}
		}

		$this->_log("Finish at ".date('m/d/Y h:i:s a', time()).($this->error_count>0?" <font color='red'>({$this->error_count} error)</font>":" (no error)"),2);
		if($task_id>0){
			$this->finalizeTask($task_id,($this->error_count>0?3:1),$this->log,$email);
			if($this->log){
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					try
					{
						$mailFrom = env('MAIL_USERNAME');
						$data = ['content' => strip_tags($this->log)];
						$subjectName = ($this->error_count>0?"[ERROR] ":"")."Automatic save Storage/Tank task's log";
						$ret = Mail::send('front.sendmail',$data, function ($message) use ($email, $subjectName, $mailFrom) {
							$message->from($mailFrom, 'Energy Builder');
							$message->to($email)->subject($subjectName);
						});
						if($ret == 1){
							return "Email sent successfully";
						}else{
							return $ret;
						}
					}catch (\Exception $e)
					{
						\Log::info($e->getMessage());
					}
				}
			}
		}else{
			//\Log::info($this->log);
			return $this->log;
		}
	}

	function saveDataTank($occur_date,$facility_id,$objectIds){
		//check date
		$ds=explode("-",$occur_date);
		$day=$ds[2];
		$month=$ds[1];
		$year=$ds[0];
		if(!($day>=1 && $day<=31 && $month>=1 && $month<=12 && $year>=1900 && $year<=3000)){
			$this->_log("Wrong occur date ($occur_date)",1);
			return;
		}
		//CHECK DATA LOCKED
		$islocked = [];
		$tables = ["TANK_DATA_FDC_VALUE","TANK_DATA_VALUE"];
		foreach($tables as $table){
			$islocked[$table] = \Helper::checkLockedTable($table,$occur_date,$facility_id);
			if($islocked[$table]){
				echo "Table locked ($table, date: $occur_date, facility_id: $facility_id)";
				$this->_log("Table locked ($table, date: $occur_date, facility_id: $facility_id)",2);
			}
		}
		/*
		foreach($tables as $table){
			if(!$islocked[$table])
				doFormula($table,"id",getRowIDs($table));
		}
		*/
		
		foreach($tables as $table)
		if($islocked[$table] == false){
			$fo_mdlName = \Helper::camelize(strtolower ($table),'_');
			\FormulaHelpers::applyFormula($fo_mdlName,$objectIds,$occur_date);
		}
		$this->_log("saveData $occur_date",2);
	}

	function saveDataStorage($occur_date,$facility_id,$objectIds){
		//check date
		$ds=explode("-",$occur_date);
		$day=$ds[2];
		$month=$ds[1];
		$year=$ds[0];
		if(!($day>=1 && $day<=31 && $month>=1 && $month<=12 && $year>=1900 && $year<=3000)){
			$this->_log("Wrong occur date ($occur_date)",1);
			return;
		}
		//CHECK DATA LOCKED
		$islocked = [];
		$tables = ["STORAGE_DATA_VALUE"];
		foreach($tables as $table){
			$islocked[$table] = \Helper::checkLockedTable($table,$occur_date,$facility_id);
			if($islocked[$table]){
				echo "Table locked ($table, date: $occur_date, facility_id: $facility_id)";
				$this->_log("Table locked ($table, date: $occur_date, facility_id: $facility_id)",2);
			}
		}
		/*
		foreach($tables as $table){
			if(!$islocked[$table])
				doFormula($table,"id",getRowIDs($table));
		}
		*/
		
		foreach($tables as $table)
		if($islocked[$table] == false){
			$fo_mdlName = \Helper::camelize(strtolower ($table),'_');
			\FormulaHelpers::applyFormula($fo_mdlName,$objectIds,$occur_date);
		}
		$this->_log("saveData $occur_date",2);
	}

    public function finalizeTask($task_id,$status,$log,$email){
    	if($task_id>0){

    		$now = Carbon::now();
    		$time = date('Y-m-d H:i:s', strtotime($now));

    		TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>$status, 'FINISH_TIME'=>$time, 'LOG'=>addslashes($log)]);

    		if($status==1){
    			//task finish, check next task
    			$objAll = new WorkflowProcessController(null, null);
    			$objAll->processNextTask($task_id);
    		}
    	}
    }

    private function _log($s,$type)
    {
    	$ret=true;
    	$h=$s;
    	if($type==1){
    		$h="<font color='red'>$s</font><br>";
    		$this->error_count++;
    		$ret=false;
    	}
    	else if($type==2)
    		$h="<font color='blue'>$s</font><br>";
    		else
    			$h="$s<br>";
    			$this->log.=$h;
    			return $ret;
    }
}
