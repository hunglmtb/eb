<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TmWorkflowTask,
	App\Models\AllocJob,
	App\Models\AllocRunner,
	App\Models\WellComp,
	App\Models\WellCompDataAlloc,
	App\Models\WellCompInterval,
	App\Models\WellCompIntervalDataAlloc,
	App\Models\WellCompIntervalPerf,
	App\Models\WellCompIntervalPerfDataAlloc,
	App\Models\FlowDataAlloc,
	App\Models\EnergyUnitDataAlloc,
	App\Models\TankDataAlloc,
	App\Models\StorageDataAlloc,
	App\Models\FlowCoEntDataAlloc,
	App\Models\EnergyUnitCoEntDataAlloc,
	App\Models\FlowCompDataAlloc,
	App\Models\EnergyUnitCompDataAlloc,
	App\Models\QltyProductElementType,
	App\Models\AllocRunnerObjects;
use  DB, Carbon\Carbon, Mail;
use App\Http\Controllers\WorkflowProcessController;

class runAllocation extends Job implements ShouldQueue, SelfHandling
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
		$runner_id = 0;
		$job_id = isset($this->param['job_id'])?$this->param['job_id']:0;
    	$this->alloc_act = $this->param['act'];
		if(isset($this->param['taskid'])){
			$task_id = $this->param['taskid'];
			$date_type = $this->param['type'];
			$from_date = $this->param['from_date'];
			$to_date = $this->param['to_date'];
			$email = $this->param['email'];
			if($date_type == "day"){
				$date = date('Y-m-d');
				$from_date = date('Y-m-d', strtotime($date .' -1 day'))."";
				$to_date = $from_date;
			}
		}
		else{
			$runner_id = isset($this->param['runner_id'])?$this->param['runner_id']:0;
			$from_date = Carbon::createFromFormat('m/d/Y',$this->param['from_date'])->format('Y-m-d');
			$to_date = Carbon::createFromFormat('m/d/Y',$this->param['to_date'])->format('Y-m-d');
		}

    	if(!($job_id>0) && !($runner_id>0)){
    		$this->_log("Unknown job or runner to run allocation",1);
    		if($task_id>0){
    			$this->finalizeTask($task_id,3,$this->log,$email);
    		}
    		exit();
    	}

    	\Log::info ("date: $from_date, $to_date");
    	if ($runner_id > 0) {
    		$tmp = DB::table ( 'alloc_runner AS b' )->join ( 'alloc_job AS a', 'a.id', '=', 'b.job_id' )->where ( [
    				'b.id' => $runner_id
    		] )->select ( 'a.DAY_BY_DAY' )->first ();

    		$daybyday = $tmp->DAY_BY_DAY;
    		if ($daybyday == 1) {
    			//$ds = explode ( "-", $from_date );
    			//$d1 = "$ds[2]-$ds[0]-$ds[1]";
    			//$ds = explode ( "-", $to_date );
    			//$d2 = "$ds[2]-$ds[0]-$ds[1]";
				$d1 = date ( "Y-m-d", strtotime ( $from_date ) );
				$d2 = strtotime ( $to_date );
    			while ( strtotime ( $d1 ) <= $d2 ) {
    				$this->exec_runner ( $runner_id, $d1, $d1 );
    				$d1 = date ( "Y-m-d", strtotime ( "+1 day", strtotime ( $d1 ) ) );
    			}
    		} else {
    			$this->exec_runner ( $runner_id, $from_date, $to_date );
    		}
    	} else if ($job_id > 0) {
    		$tmp = AllocJob::where ( [
    				'ID' => $job_id
    		] )->select ( 'DAY_BY_DAY' )->first ();
    		$daybyday = $tmp['DAY_BY_DAY'];
    		if ($daybyday == 1) {
				$d1 = date ( "Y-m-d", strtotime ( $from_date ) );
				$d2 = strtotime ( $to_date );
    			while ( strtotime ( $d1 ) <= $d2 ) {
    				$this->exec_job ( $job_id, $d1, $d1 );
    				$d1 = date ( "Y-m-d", strtotime ( "+1 day", strtotime ( $d1 ) ) );
    			}
    		} else {
    			$this->exec_job ( $job_id, $from_date, $to_date );
    		}
    	}

    	$this->_log("Finish.".($this->error_count>0?" <font color='red'>($this->error_count error)</font>":" (no error)"),2);

    	if($task_id > 0){
    		$this->finalizeTask($task_id,($this->error_count>0?3:1),$this->log,$email);
    		if($this->log){
    			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    				try
    				{
    					$mailFrom = env('MAIL_USERNAME');
		    			$data = ['content' => strip_tags($this->log)];
		    			$subjectName = ($this->error_count>0?"[ERROR] ":"")."Automatic allocation task's log";
		    			$ret = Mail::send('front.sendmail',$data, function ($message) use ($email, $subjectName, $mailFrom) {
		    				$message->from($mailFrom, 'Your Application');
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

    private function allocWellCompletion($eu_id,$date,$phase_type,$event_type,$alloc_attr,$value)
    {
    	$F="";
    	if($phase_type==1) $F="OIL_RATE";
    	else if($phase_type==2) $F="GAS_RATE";
    	else if($phase_type==3) $F="WATER_RATE";
    	else return;

    	$result = WellComp::where(['EU_ID'=>$eu_id])->whereDate('EFFECTIVE_DATE', '<=', $date)->get();

    	$total_fixed = 0;
    	foreach ($result as $row){
    		if($row->$F){
    			$v=$row->$F*$value;
    			$comp_id=$row->ID;

    			$ro = WellCompDataAlloc::where(['COMP_ID'=>$comp_id, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type])
    			->whereDate('OCCUR_DATE', '=', $date)
    			->select('ID')->first();

    			if($ro){
    				if($_REQUEST["act"]=="run"){
    					WellCompDataAlloc::where(['ID'=>$ro->ID])->update([$alloc_attr=>$v]);
    				}
    				$sSQL="update well_comp_data_alloc set $alloc_attr=$v where ID=$ro[ID]";

    			}else{
    				if($_REQUEST["act"]=="run"){
    					WellCompDataAlloc::insert(['COMP_ID'=>$comp_id, 'OCCUR_DATE'=>$date, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type, $alloc_attr=>$v]);
    				}
    				$sSQL="insert into well_comp_data_alloc(`COMP_ID`,`OCCUR_DATE`,FLOW_PHASE,EVENT_TYPE,$alloc_attr) values('$comp_id','$date',$phase_type,$event_type,$v)";
    			}
    			$this->_log($sSQL,2);

    			$this->allocWellInterval($comp_id,$date,$phase_type,$event_type,$alloc_attr,$v);
    		}
    	}
    }

    private function allocWellInterval($comp_id,$date,$phase_type,$event_type,$alloc_attr,$value)
    {
    	$F="";
    	if($phase_type==1) $F="OIL_RATE";
    	else if($phase_type==2) $F="GAS_RATE";
    	else if($phase_type==3) $F="WATER_RATE";
    	else return;

    	$result = WellCompInterval::where(['COMP_ID'=>$comp_id])->whereDate('EFFECTIVE_DATE', '<=', $date)->get();
    	$total_fixed = 0;
    	foreach ($result as $row){
    		if($row->$F){
    			$v=$row->$F*$value;
    			$interval_id=$row->ID;

    			$ro = WellCompIntervalDataAlloc::where(['COMP_INTERVAL_ID'=>$interval_id, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type])
    			->whereDate('OCCUR_DATE', '=', $date)
    			->select('ID')->first();

    			if($ro){
    				if($_REQUEST["act"]=="run"){
    					WellCompIntervalDataAlloc::where(['ID'=>$ro->ID])->update([$alloc_attr=>$v]);
    				}
    				$sSQL="update well_comp_interval_data_alloc set $alloc_attr=$v where ID=$ro[ID]";
    			}else{
    				if($_REQUEST["act"]=="run"){
    					WellCompIntervalDataAlloc::insert(['COMP_INTERVAL_ID'=>$interval_id, 'OCCUR_DATE'=>$date, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type, $alloc_attr=>$v]);
    				}
    				$sSQL="insert into well_comp_interval_data_alloc(`COMP_INTERVAL_ID`,`OCCUR_DATE`,FLOW_PHASE,EVENT_TYPE,$alloc_attr) values('$interval_id','$date',$phase_type,$event_type,$v)";
    			}
    			$this->_log($sSQL,2);

    			$this->allocWellPerforation($interval_id,$date,$phase_type,$event_type,$alloc_attr,$v);
    		}
    	}
    }

    private function allocWellPerforation($interval_id,$date,$phase_type,$event_type,$alloc_attr,$value)
    {
    	$F="";
    	if($phase_type==1) $F="OIL_RATE";
    	else if($phase_type==2) $F="GAS_RATE";
    	else if($phase_type==3) $F="WATER_RATE";
    	else return;

    	$result = WellCompIntervalPerf::where(['COMP_INTERVAL_ID'=>$interval_id])->whereDate('EFFECTIVE_DATE', '<=', $date)->get();
    	$total_fixed = 0;
    	foreach ($result as $row){
    		if($row->$F)
    		{
    			$v=$row->$F*$value;
    			$perf_id=$row->ID;

    			$ro = WellCompIntervalPerfDataAlloc::where(['COMP_INTERVAL_PERF_ID'=>$perf_id, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type])
    			->whereDate('OCCUR_DATE', '=', $date)
    			->select('ID')->first();

    			if($ro){
    				if($_REQUEST["act"]=="run"){
    					WellCompIntervalPerfDataAlloc::where(['ID'=>$ro->ID])->update([$alloc_attr=>$v]);
    				}
    				$sSQL="update well_comp_interval_perf_data_alloc set $alloc_attr=$v where ID=$ro[ID]";

    			}else{
    				if($_REQUEST["act"]=="run"){
    					WellCompIntervalPerfDataAlloc::insert(['COMP_INTERVAL_PERF_ID'=>$perf_id, 'OCCUR_DATE'=>$date, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type, $alloc_attr=>$v]);
    				}
    				$sSQL="insert into well_comp_interval_perf_data_alloc(`COMP_INTERVAL_PERF_ID`,`OCCUR_DATE`,FLOW_PHASE,EVENT_TYPE,$alloc_attr) values('$perf_id','$date',$phase_type,$event_type,$v)";
    			}
    			$this->_log($sSQL,2);
    		}
    	}
    }

    private function exec_runner($runner_id, $from_date, $to_date)
    {
    	//\DB::enableQueryLog ();
    	$tmps = DB::table ('alloc_runner AS a' )
    	->join ( 'alloc_job AS b', 'a.job_id', '=', 'b.ID' )
    	->join ( 'code_alloc_value_type AS c', 'c.id', '=', 'b.value_type' )
    	->leftjoin('code_alloc_value_type AS t', 'a.theor_value_type', '=', 't.id')
    	->where(['a.id'=>$runner_id])
    	->get(['a.ALLOC_TYPE', 'a.THEOR_PHASE', 'c.CODE AS ALLOC_ATTR_CODE', 't.CODE AS THEOR_ATTR_CODE', 'b.*']);
    	//\Log::info ( \DB::getQueryLog () );
		$row = $tmps[0];
    	if($row)
    	{
    		$alloc_attr=$row->ALLOC_ATTR_CODE;
    		$alloc_type=$row->ALLOC_TYPE;
    		$theor_attr=$row->THEOR_ATTR_CODE;
    		$theor_phase=$row->THEOR_PHASE;

    		$alloc_oil=($row->ALLOC_OIL == 1);
    		$alloc_gas=($row->ALLOC_GAS == 1);
    		$alloc_gaslift=($row->ALLOC_GASLIFT == 1);
    		$alloc_condensate=($row->ALLOC_CONDENSATE == 1);
    		$alloc_water=($row->ALLOC_WATER == 1);
    		$alloc_comp=($row->ALLOC_COMP == 1);

    		if($alloc_oil) $this->run_runner($runner_id, $from_date, $to_date, $alloc_attr, 1,$theor_phase,$alloc_comp,$alloc_type,$theor_attr);
    		if($alloc_gas) $this->run_runner($runner_id, $from_date, $to_date, $alloc_attr, 2,$theor_phase,$alloc_comp,$alloc_type,$theor_attr);
    		if($alloc_water) $this->run_runner($runner_id, $from_date, $to_date, $alloc_attr, 3,$theor_phase,$alloc_comp,$alloc_type,$theor_attr);
    		if($alloc_gaslift) $this->run_runner($runner_id, $from_date, $to_date, $alloc_attr, 21,$theor_phase,$alloc_comp,$alloc_type,$theor_attr);
    		if($alloc_condensate) $this->run_runner($runner_id, $from_date, $to_date, $alloc_attr, 5,$theor_phase,$alloc_comp,$alloc_type,$theor_attr);
    	}
    	else
    	{
    		$this->_log("No runner info found",1);
    		return false;
    	}
    }

    private function getQualityGas($object_id,$object_type_code,$occur_date,$F)
    {
    	// Find composition %Mol
    	$field = ($F == "MASS" ? "MASS_FRACTION" : "MOLE_FACTION");

    	//\DB::enableQueryLog ();
    	$row = DB::table ( 'qlty_data AS a' )->join ( 'code_qlty_src_type AS b', 'a.SRC_TYPE', '=', 'b.ID' )->where ( [
    			'a.SRC_ID' => $object_id,
    			'b.CODE' => $object_type_code
    	] )->whereDate ( 'a.EFFECTIVE_DATE', '<=', $occur_date )->orderBy ( 'a.EFFECTIVE_DATE', 'DESC' )->SELECT ( 'ID', 'a.ENGY_RATE' )->first ();
    	//\Log::info ( \DB::getQueryLog () );

    	if (count ( $row ) > 0) {
    		$data = [ ];
    		$data ['ENGY_RATE'] = $row->ENGY_RATE;

    		$ID_C1 = QltyProductElementType::where ( [
    				'CODE' => 'C1',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_C1'] = $ID_C1->ID;

    		$ID_C2 = QltyProductElementType::where ( [
    				'CODE' => 'C2',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_C2'] = $ID_C2->ID;

    		$ID_C3 = QltyProductElementType::where ( [
    				'CODE' => 'C3',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_C3'] = $ID_C3->ID;

    		$ID_C4I = QltyProductElementType::where ( [
    				'CODE' => 'IC4',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_C4I'] = $ID_C4I->ID;

    		$ID_C4N = QltyProductElementType::where ( [
    				'CODE' => 'NC4',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_C4N'] = $ID_C4N->ID;

    		$ID_C5I = QltyProductElementType::where ( [
    				'CODE' => 'IC5',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_C5I'] = $ID_C5I->ID;

    		$ID_C5N = QltyProductElementType::where ( [
    				'CODE' => 'NC5',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_C5N'] = $ID_C5N->ID;

    		$ID_C6 = QltyProductElementType::where ( [
    				'CODE' => 'C6',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_C6'] = $ID_C6->ID;

    		$ID_C7 = QltyProductElementType::where ( [
    				'CODE' => 'C7+',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_C7'] = $ID_C7->ID;

    		$ID_H2S = QltyProductElementType::where ( [
    				'CODE' => 'H2S',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_H2S'] = $ID_H2S->ID;

    		$ID_CO2 = QltyProductElementType::where ( [
    				'CODE' => 'CO2',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_CO2'] = $ID_CO2->ID;

    		$ID_N2 = QltyProductElementType::where ( [
    				'CODE' => 'N2',
    				'PRODUCT_TYPE' => 2
    		] )->SELECT ( 'ID' )->first ();
    		$data ['ID_N2'] = $ID_N2->ID;

    		$C1 = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'C1',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['C1'] = $C1;

    		$C2 = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'C2',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['C2'] = $C2;

    		$C3 = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'C3',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['C3'] = $C3;

    		$C4I = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'IC4',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['C4I'] = $C4I;

    		$C4N = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'NC4',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['C4N'] = $C4N;

    		$C5I = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'IC5',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['C5I'] = $C5I;

    		$C5N = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'NC5',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['C5N'] = $C5N;

    		$C6 = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'C6',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['C6'] = $C6;

    		$C7 = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'C7+',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['C7'] = $C7;

    		$H2S = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'H2S',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['H2S'] = $H2S;

    		$CO2 = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'CO2',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['CO2'] = $CO2;

    		$N2 = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'N2',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.' . $field )->get () )->max ( $field );
    		$data ['N2'] = $N2;

    		$M_C7 = collect ( QltyProductElementType::where ( [
    				'CODE' => 'C7+',
    				'PRODUCT_TYPE' => 2
    		] )->get ( [
    				'MOL_WEIGHT'
    		] ) )->max ( 'MOL_WEIGHT' );
    		$data ['M_C7'] = $M_C7;

    		$G_C7 = collect ( DB::table ( 'qlty_data_detail AS a' )->join ( 'qlty_product_element_type AS b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $row->ID,
    				'b.CODE' => 'C7+',
    				'b.PRODUCT_TYPE' => 2
    		] )->SELECT ( 'a.GAMMA_C7' )->get () )->max ( 'GAMMA_C7' );
    		$data ['G_C7'] = $G_C7;

    		return $data;
    	}
    	return null;
    }

    private function run_runner($runner_id, $from_date, $to_date, $alloc_attr, $alloc_phase, $theor_phase, $alloc_comp, $alloc_type, $theor_attr)
    {
    	$runner_name = AllocRunner::where ( [
    			'ID' => $runner_id
    	] )->select ( 'NAME' )->first ();

    	$this->_log ( "Begin runner $runner_name->NAME (ID: $runner_id), from date: $from_date, to date: $to_date, alloc_attr: $alloc_attr, alloc_phase: $alloc_phase", 2 );
    	$xdate = date_create ( "2016-01-01" );
    	//$from_date = date ( 'Y-m-d', strtotime ( $from_date ) );
    	//$to_date = date ( 'Y-m-d', strtotime ( $to_date ) );
    	if (date_create ( $from_date ) < $xdate || date_create ( $to_date ) < $xdate) {
    		$ret = $this->_log ( "Can not run allocation for the date earlier than 01/01/2016.", 1 );
    		return false;
    	}

    	$success = true;
    	$event_type = 0;
    	if ($alloc_type == 1 || $alloc_type == 2)
    		$event_type = $alloc_type;

		if ($alloc_phase != 2)
			$alloc_comp = false;

		if (! $theor_phase)
			$theor_phase = $alloc_phase;
		else if ($theor_phase != $alloc_phase)
			$this->_log ( "Theor. phase changed to: $theor_phase", 2 );

		$F = "VOL";
		if (strpos ( $alloc_attr, 'MASS' ) !== false) {
			$F = "MASS";
		}
		$alloc_attr_eu = $alloc_attr;
		//if ($alloc_attr == "NET_VOL")
		//	$alloc_attr_eu = "GRS_VOL";
		if (! $theor_attr)
			$theor_attr = $alloc_attr;
		else if ($theor_attr != $alloc_attr)
			$this->_log ( "Theor. value type changed to: $theor_attr", 2 );

		$theor_attr_eu = $theor_attr;
		if ($theor_attr == "NET_VOL")
			$theor_attr_eu = "GRS_VOL";

		$total_from = 0;
		$total_to = 0;
		$total_fixed = 0;

		$ids_from = "";
		$ids_to = "";
		$ids_fixed = "";
		$ids_minus = "-999";

		$obj_type_from = "";
		$obj_type_to = "";

		$OBJ_TYPE_FLOW = 1;
		$OBJ_TYPE_EU = 2;
		$OBJ_TYPE_TANK = 3;
		$OBJ_TYPE_STORAGE = 4;

		$rows = AllocRunnerObjects::where ( [
				'RUNNER_ID' => $runner_id
		] )->get ();

		foreach ( $rows as $row ) {
			if ($row->DIRECTION == 1) {
				if (! $obj_type_from)
					$obj_type_from = $row->OBJECT_TYPE;
					if ($row->OBJECT_TYPE == $obj_type_from) {
						$ids_from .= ($ids_from ? "," : "") . $row->OBJECT_ID;
					}
					if ($row->MINUS == 1)
						$ids_minus .= ($ids_minus ? "," : "") . $row->OBJECT_ID;
			} else {
				if (! $obj_type_to)
					$obj_type_to = $row->OBJECT_TYPE;
					if ($row->OBJECT_TYPE == $obj_type_to) {
						if ($row->FIXED == 1){
							$ids_fixed .= ($ids_fixed ? "," : "") . $row->OBJECT_ID;
						}else{
							$ids_to .= ($ids_to ? "," : "") . $row->OBJECT_ID;
						}
					}
			}
		}

		if ($ids_from) {
			$total_from = 0;
			$sum = [ ];
			$sSQL_alloc_from = [ ];
			$arrfrom = explode ( ',', $ids_from );
			if ($obj_type_from == $OBJ_TYPE_FLOW) {
				// //\DB::enableQueryLog ();
				$sum = DB::table ( 'FLOW_DATA_ALLOC AS a' )->leftjoin ( 'FLOW_DATA_VALUE AS v', function ($join) {
					$join->on ( 'v.FLOW_ID', '=', 'a.FLOW_ID' );
					$join->on ( 'v.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
				} )->leftjoin ( 'FLOW_DATA_THEOR AS t', function ($join) {
					$join->on ( 'v.FLOW_ID', '=', 'a.FLOW_ID' );
					$join->on ( 'v.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
				} )->join ( 'FLOW AS b', 'a.FLOW_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.FLOW_ID', $arrfrom )->SELECT ( DB::raw ( 'sum((case when a.FLOW_ID in (' . $ids_minus . ') then -1 else 1 end))*IF(IFNULL(a.FL_DATA_' . $alloc_attr . ',0)>0,a.FL_DATA_' . $alloc_attr . ',IF(IFNULL(v.FL_DATA_' . $alloc_attr . ',0)>0,v.FL_DATA_' . $alloc_attr . ',t.FL_DATA_' . $alloc_attr . ')) AS total_from' ) )->get ();
				// //\Log::info ( \DB::getQueryLog () );

				//\DB::enableQueryLog ();
				$sSQL_alloc_from = DB::table ( 'FLOW_DATA_ALLOC AS a' )->leftjoin ( 'FLOW_DATA_VALUE AS v', function ($join) {
					$join->on ( 'v.FLOW_ID', '=', 'a.FLOW_ID' );
					$join->on ( 'v.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
				} )->leftjoin ( 'FLOW_DATA_THEOR AS t', function ($join) {
					$join->on ( 'v.FLOW_ID', '=', 'a.FLOW_ID' );
					$join->on ( 'v.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
				} )->join ( 'FLOW AS b', 'a.FLOW_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.FLOW_ID', $arrfrom )->SELECT ( DB::raw ( 'IF(IFNULL(a.FL_DATA_' . $alloc_attr . ',0)>0,a.FL_DATA_' . $alloc_attr . ',IF(IFNULL(v.FL_DATA_' . $alloc_attr . ',0)>0,v.FL_DATA_' . $alloc_attr . ',t.FL_DATA_' . $alloc_attr . ')) AS ALLOC_VALUE', 'a.FLOW_ID AS OBJECT_ID', 'b.NAME AS OBJECT_NAME', 'a.ACTIVE_HRS', 'b.NAME AS ALLOC_VALUE', 'a.OCCUR_DATE', 'a.OCCUR_DATE AS OCCUR_DATE_STR' ) )->get ();
				//\Log::info ( \DB::getQueryLog () );
			} else if ($obj_type_from == $OBJ_TYPE_EU) {
				// //\DB::enableQueryLog ();
				$sum = DB::table ( 'ENERGY_UNIT_DATA_ALLOC AS a' )->leftjoin ( 'ENERGY_UNIT_DATA_VALUE AS v', function ($join) {
					$join->on ( 'v.EU_ID', '=', 'a.EU_ID' );
					$join->on ( 'v.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
					$join->on ( 'v.FLOW_PHASE', '=', 'a.FLOW_PHASE' );
					$join->on ( 'v.EVENT_TYPE', '=', 'a.EVENT_TYPE' );
				} )->leftjoin ( 'ENERGY_UNIT_DATA_THEOR AS t', function ($join) {
					$join->on ( 't.EU_ID', '=', 'a.EU_ID' );
					$join->on ( 't.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
					$join->on ( 't.FLOW_PHASE', '=', 'a.FLOW_PHASE' );
					$join->on ( 't.EVENT_TYPE', '=', 'a.EVENT_TYPE' );
				} )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.EU_ID', $arrfrom )->where ( [
						'a.FLOW_PHASE' => $alloc_phase
				] )->where ( [
						'a.EVENT_TYPE' => $event_type
				] )->SELECT ( DB::raw ( 'sum((case when a.EU_ID in (' . $ids_minus . ') then -1 else 1 end))*IF(IFNULL(a.EU_DATA_' . $alloc_attr_eu . ',0)>0,a.EU_DATA_' . $alloc_attr_eu . ',IF(IFNULL(v.EU_DATA_' . $alloc_attr_eu . ',0)>0,v.EU_DATA_' . $alloc_attr_eu . ',t.EU_DATA_' . $alloc_attr_eu . ')) AS total_from' ) )->get ();
				// //\Log::info ( \DB::getQueryLog () );

				//\DB::enableQueryLog ();
				$sSQL_alloc_from = DB::table ( 'ENERGY_UNIT_DATA_ALLOC AS a' )->leftjoin ( 'ENERGY_UNIT_DATA_VALUE AS v', function ($join) {
					$join->on ( 'v.EU_ID', '=', 'a.EU_ID' );
					$join->on ( 'v.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
					$join->on ( 'v.FLOW_PHASE', '=', 'a.FLOW_PHASE' );
					$join->on ( 'v.EVENT_TYPE', '=', 'a.EVENT_TYPE' );
				} )->leftjoin ( 'ENERGY_UNIT_DATA_THEOR AS t', function ($join) {
					$join->on ( 't.EU_ID', '=', 'a.EU_ID' );
					$join->on ( 't.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
					$join->on ( 't.FLOW_PHASE', '=', 'a.FLOW_PHASE' );
					$join->on ( 't.EVENT_TYPE', '=', 'a.EVENT_TYPE' );
				} )->join ( 'ENERGY_UNIT AS b', 'a.EU_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.EU_ID', $arrfrom )->where ( [
						'a.FLOW_PHASE' => $alloc_phase
				] )->where ( [
						'a.EVENT_TYPE' => $event_type
				] )->SELECT ( DB::raw ( 'IF(IFNULL(a.EU_DATA_' . $alloc_attr_eu . ',0)>0,a.EU_DATA_' . $alloc_attr_eu . ',IF(IFNULL(v.EU_DATA_' . $alloc_attr_eu . ',0)>0,v.EU_DATA_' . $alloc_attr_eu . ',t.EU_DATA_' . $alloc_attr_eu . ')) AS ALLOC_VALUE', 'a.EU_ID AS OBJECT_ID', 'b.NAME AS OBJECT_NAME', 'a.ACTIVE_HRS', 'a.OCCUR_DATE', 'a.OCCUR_DATE AS OCCUR_DATE_STR', 'a.FLOW_PHASE' ) )->get ();
				//\Log::info ( \DB::getQueryLog () );
			} else if ($obj_type_from == $OBJ_TYPE_TANK) {
				// //\DB::enableQueryLog ();
				$sum = DB::table ( 'TANK_DATA_ALLOC AS a' )->leftjoin ( 'TANK_DATA_VALUE AS v', function ($join) {
					$join->on ( 'v.TANK_ID', '=', 'a.TANK_ID' );
					$join->on ( 'v.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
				} )->join ( 'TANK AS b', 'a.TANK_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.TANK_ID', $arrfrom )->select ( DB::raw ( 'sum((case when a.TANK_ID in (' . $ids_minus . ') then -1 else 1 end)*IF(IFNULL(a.TANK_' . $alloc_attr . ',0)>0,a.TANK_' . $alloc_attr . ',v.TANK_' . $alloc_attr . ')) AS total_from' ) )->get ();
				// //\Log::info ( \DB::getQueryLog () );

				// //\DB::enableQueryLog ();
				$sSQL_alloc_from = DB::table ( 'TANK_DATA_ALLOC AS a' )->leftjoin ( 'TANK_DATA_VALUE AS v', function ($join) {
					$join->on ( 'v.TANK_ID', '=', 'a.TANK_ID' );
					$join->on ( 'v.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
				} )->join ( 'TANK AS b', 'a.TANK_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.TANK_ID', $arrfrom )->select ( DB::raw ( 'IF(IFNULL(a.TANK_' . $alloc_attr . ',0)>0,a.TANK_' . $alloc_attr . ',v.TANK_' . $alloc_attr . ') AS total_from', 'a.TANK_ID AS OBJECT_ID', 'b.NAME AS OBJECT_NAME', 'a.ACTIVE_HRS', 'a.OCCUR_DATE', 'a.OCCUR_DATE AS OCCUR_DATE_STR' ) )->get ();
				// //\Log::info ( \DB::getQueryLog () );
			} else if ($obj_type_from == $OBJ_TYPE_STORAGE) {

				// //\DB::enableQueryLog ();
				$sum = DB::table ( 'STORAGE_DATA_ALLOC AS a' )->leftjoin ( 'STORAGE_DATA_VALUE AS v', function ($join) {
					$join->on ( 'v.STORAGE_ID', '=', 'a.STORAGE_ID' );
					$join->on ( 'v.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
				} )->join ( 'STORAGE AS b', 'a.STORAGE_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.STORAGE_ID', $arrfrom )->select ( DB::raw ( 'sum((case when a.STORAGE_ID in (' . $ids_minus . ') then -1 else 1 end)*IF(IFNULL(a.STORAGE_' . $alloc_attr . ',0)>0,a.STORAGE_' . $alloc_attr . ',a.STORAGE_' . $alloc_attr . ')) AS total_from' ) )->get ();
				// //\Log::info ( \DB::getQueryLog () );

				//\DB::enableQueryLog ();
				$sSQL_alloc_from = DB::table ( 'STORAGE_DATA_ALLOC AS a' )->leftjoin ( 'STORAGE_DATA_VALUE AS v', function ($join) {
					$join->on ( 'v.STORAGE_ID', '=', 'a.STORAGE_ID' );
					$join->on ( 'v.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
				} )->join ( 'STORAGE AS b', 'a.STORAGE_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.STORAGE_ID', $arrfrom )->select ( DB::raw ( 'IF(IFNULL(a.STORAGE_' . $alloc_attr . ',0)>0,a.STORAGE_' . $alloc_attr . ',a.STORAGE_' . $alloc_attr . ') AS total_from', 'a.STORAGE_ID AS OBJECT_ID', 'b.NAME as OBJECT_NAME', 'a.ACTIVE_HRS', 'a.OCCUR_DATE', 'a.OCCUR_DATE AS OCCUR_DATE_STR' ) )->get ();
				//\Log::info ( \DB::getQueryLog () );
			}
			$total_from = $sum [0]->total_from;

			// _log("command: $sSQL");
			// _log("sSQL_alloc_from: $sSQL_alloc_from");
			$this->_log ( "total_from (allocated ~ std value ~ theor): $total_from", 2 );

			if ($total_from <= 0){ // does not have value at "ALLOCATION", use data from STD VALUE
				$subSum = [ ];
				$sum = [ ];
				switch ($obj_type_from) {
					case $OBJ_TYPE_FLOW :
						$sum = DB::table ( 'FLOW_DATA_VALUE AS a' )->join ( 'FLOW AS b', 'a.FLOW_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.FLOW_ID', $arrfrom )->select ( DB::raw ( 'sum(FL_DATA_' . $alloc_attr . ') AS total_from' ) )->get ();
						break;

					case $OBJ_TYPE_EU :
						$sum = DB::table ( 'ENERGY_UNIT_DATA_VALUE AS a' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.EU_ID', $arrfrom )->where ( [
						'a.FLOW_PHASE' => $alloc_phase
						] )->select ( DB::raw ( 'sum(FL_DATA_' . $alloc_attr . ') AS total_from' ) )->get ();
						break;

					case $OBJ_TYPE_TANK :
						$sum = DB::table ( 'TANK_DATA_VALUE AS a' )->join ( 'TANK AS b', 'a.TANK_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.TANK_ID', $arrfrom )->select ( DB::raw ( 'sum(TANK_' . $alloc_attr . ') AS total_from' ) )->get ();
						break;

					case $OBJ_TYPE_STORAGE :
						$sum = DB::table ( 'STORAGE_DATA_VALUE AS a' )->join ( 'STORAGE AS b', 'a.STORAGE_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.STORAGE_ID', $arrfrom )->select ( DB::raw ( 'sum(STORAGE_' . $alloc_attr . ') AS total_from' ) )->get ();
						break;
				}

				$total_from = $sum [0]->total_from;
				$this->_log ( "total_from (std value): $total_from", 2 );
			}
		} else {
			$ret = $this->_log ( "From objects not found", 1 );
			if ($ret === false)
				return false;
		}
		if ($ids_to) {
			$arrto = explode ( ',', $ids_to );
			$sum = [ ];
			$sSQL_alloc = [ ];
			if ($obj_type_to == $OBJ_TYPE_FLOW) {
				$sum = DB::table ( 'FLOW_DATA_THEOR AS a' )->join ( 'FLOW AS b', 'a.FLOW_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.FLOW_ID', $arrto )->where ( [
						'b.PHASE_ID' => $theor_phase
				] )->select ( DB::raw ( 'sum(FL_DATA_' . $theor_attr . ') AS total_to' ) )->get ();

				$sSQL_alloc = DB::table ( 'FLOW_DATA_THEOR AS a' )->join ( 'FLOW AS b', 'a.FLOW_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.FLOW_ID', $arrto )->where ( [
						'b.PHASE_ID' => $theor_phase
				] )->get ( [
						'a.FLOW_ID AS OBJECT_ID',
						'b.NAME AS OBJECT_NAME',
						'a.ACTIVE_HRS',
						'a.OCCUR_DATE',
						'a.OCCUR_DATE AS OCCUR_DATE_STR',
						'a.FL_DATA_' . $theor_attr . ' AS ALLOC_THEOR'
				] );

				$sSQL_alloc_to = DB::table ( 'FLOW_DATA_ALLOC AS a' )->join ( 'FLOW AS b', 'a.FLOW_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.FLOW_ID', $arrto )->where ( [
						'b.PHASE_ID' => $theor_phase
				] )->get ( [
						'a.FLOW_ID AS OBJECT_ID',
						'b.NAME AS OBJECT_NAME',
						'a.ACTIVE_HRS',
						'a.OCCUR_DATE',
						'a.OCCUR_DATE AS OCCUR_DATE_STR',
						'a.FL_DATA_' . $theor_attr . ' AS ALLOC_VALUE'
				] );
			} else if ($obj_type_to == $OBJ_TYPE_EU) {
				$sum = DB::table ( 'ENERGY_UNIT_DATA_THEOR AS a' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.EU_ID', $arrto )->where ( [
						'a.FLOW_PHASE' => $theor_phase,
						'a.FLOW_PHASE' => $theor_phase,
						'a.EVENT_TYPE' => $event_type
				] )->select ( DB::raw ( 'sum(EU_DATA_' . $theor_attr_eu . ') AS total_to' ) )->get ();
//\DB::enableQueryLog ();
				$sSQL_alloc = DB::table ( 'ENERGY_UNIT_DATA_THEOR AS a' )->join ( 'ENERGY_UNIT AS b', 'a.EU_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.EU_ID', $arrto )->where ( [
						'a.FLOW_PHASE' => $theor_phase,
						'a.EVENT_TYPE' => $event_type
				] )->get ( [
						'a.EU_ID AS OBJECT_ID',
						'b.NAME AS OBJECT_NAME',
						'a.ACTIVE_HRS',
						'a.OCCUR_DATE',
						'a.OCCUR_DATE AS OCCUR_DATE_STR',
						'a.FLOW_PHASE',
						'EU_DATA_' . $theor_attr_eu . ' AS ALLOC_THEOR'
				] );
//\Log::info ( \DB::getQueryLog () );
				$sSQL_alloc_to = DB::table ( 'ENERGY_UNIT_DATA_ALLOC AS a' )->join ( 'ENERGY_UNIT AS b', 'a.EU_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.EU_ID', $arrto )->where ( [
						'a.FLOW_PHASE' => $theor_phase,
						'a.EVENT_TYPE' => $event_type
				] )->get ( [
						'a.EU_ID AS OBJECT_ID',
						'b.NAME AS OBJECT_NAME',
						'a.ACTIVE_HRS',
						'a.OCCUR_DATE',
						'a.OCCUR_DATE AS OCCUR_DATE_STR',
						'a.FLOW_PHASE',
						'EU_DATA_' . $theor_attr_eu . ' AS ALLOC_VALUE'
				] );
			} else if ($obj_type_to == $OBJ_TYPE_TANK) {
				$sum = DB::table ( 'TANK_DATA_VALUE AS a' )->join ( 'TANK AS b', 'a.TANK_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.TANK_ID', $arrto )->select ( DB::raw ( 'sum(TANK_' . $theor_attr . ') AS total_to' ) )->get ();

				$sSQL_alloc = DB::table ( 'TANK_DATA_VALUE AS a' )->join ( 'TANK AS b', 'a.TANK_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.TANK_ID', $arrto )->get ( [
						'a.TANK_ID AS OBJECT_ID',
						'b.NAME AS OBJECT_NAME',
						'a.OCCUR_DATE',
						'a.OCCUR_DATE AS OCCUR_DATE_STR',
						'a.TANK_DATA_' . $theor_attr . ' AS ALLOC_THEOR'
				] );

				$sSQL_alloc_to = DB::table ( 'TANK_DATA_VALUE AS a' )->join ( 'TANK AS b', 'a.TANK_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.TANK_ID', $arrto )->get ( [
						'a.TANK_ID AS OBJECT_ID',
						'b.NAME AS OBJECT_NAME',
						'a.OCCUR_DATE',
						'a.OCCUR_DATE AS OCCUR_DATE_STR',
						'a.TANK_' . $theor_attr . ' AS ALLOC_VALUE'
				] );
			} else if ($obj_type_to == $OBJ_TYPE_STORAGE) {
				$sum = DB::table ( 'STORAGE_DATA_VALUE AS a' )->join ( 'STORAGE AS b', 'a.STORAGE_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.STORAGE_ID', $arrto )->select ( DB::raw ( 'sum(STORAGE_' . $theor_attr . ') AS total_to' ) )->get ();

				$sSQL_alloc = DB::table ( 'STORAGE_DATA_VALUE AS a' )->join ( 'STORAGE AS b', 'a.STORAGE_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.STORAGE_ID', $arrto )->get ( [
						'a.STORAGE_ID AS OBJECT_ID',
						'b.NAME AS OBJECT_NAME',
						'a.OCCUR_DATE',
						'a.OCCUR_DATE AS OCCUR_DATE_STR',
						'a.STORAGE_' . $theor_attr . ' AS ALLOC_THEOR'
				] );

				$sSQL_alloc_to = DB::table ( 'STORAGE_DATA_ALLOC AS a' )->join ( 'STORAGE AS b', 'a.STORAGE_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.STORAGE_ID', $arrto )->get ( [
						'a.STORAGE_ID AS OBJECT_ID',
						'b.NAME AS OBJECT_NAME',
						'a.OCCUR_DATE',
						'a.OCCUR_DATE AS OCCUR_DATE_STR',
						'a.STORAGE_' . $theor_attr . ' AS ALLOC_VALUE'
				] );
			}
			$total_to = $sum [0]->total_to;
			// _log("command: $sSQL");
			$this->_log ( "total_to (theor): $total_to", 2 );
			if($total_to)
				$this->_log ( "Allocation factor: ".round($total_from/$total_to,4), 2 );
		} else {
			$ret = $this->_log ( "TO object not found", 1 );
			if ($ret === false)
				return false;
		}

		if ($ids_fixed) {
			$rows = [ ];
			$arrfixed = explode ( ',', $ids_fixed );
			if ($obj_type_to == $OBJ_TYPE_FLOW) {
				//\DB::enableQueryLog ();
				$rows = DB::table ( 'FLOW_DATA_VALUE AS a' )->leftjoin ( 'FLOW_DATA_THEOR AS t', function ($join) {
					$join->on ( 't.FLOW_ID', '=', 'a.FLOW_ID' );
					$join->on ( 't.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
				} )->join ( 'FLOW AS b', 'a.FLOW_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->where ( [
						'b.PHASE_ID' => $alloc_phase
				] )->whereIn ( 'a.FLOW_ID', $arrfixed )->SELECT ( DB::raw ( 'IF(IFNULL(a.FL_DATA_' . $alloc_attr . ',0)>0,a.FL_DATA_' . $alloc_attr . ',t.FL_DATA_' . $alloc_attr . ') AS FIXED_VALUE', 'a.FLOW_ID AS OBJECT_ID', 'a.ACTIVE_HRS', 'a.OCCUR_DATE', 'a.OCCUR_DATE AS OCCUR_DATE_STR' ) )->get ();
				//\Log::info ( \DB::getQueryLog () );
			} else if ($obj_type_to == $OBJ_TYPE_EU) {
				//\DB::enableQueryLog ();
				$rows = DB::table ( 'ENERGY_UNIT_DATA_VALUE AS a' )->leftjoin ( 'ENERGY_UNIT_DATA_THEOR AS t', function ($join) {
					$join->on ( 't.EU_ID', '=', 'a.EU_ID' );
					$join->on ( 't.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
					$join->on ( 't.FLOW_PHASE', '=', 'a.FLOW_PHASE' );
					$join->on ( 't.EVENT_TYPE', '=', 'a.EVENT_TYPE' );
				} )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.EU_ID', $arrfixed )->where ( [
						'a.FLOW_PHASE' => $alloc_phase,
						'a.EVENT_TYPE' => $event_type
				] )->

				SELECT ( DB::raw ( 'IF(IFNULL(a.EU_DATA_' . $alloc_attr . ',0)>0,a.EU_DATA_' . $alloc_attr . ',t.EU_DATA_' . $alloc_attr . ') AS FIXED_VALUE', 'a.EU_ID AS OBJECT_ID', 'a.ACTIVE_HRS', 'a.OCCUR_DATE', 'a.OCCUR_DATE AS OCCUR_DATE_STR' ) )->get ();
				//\Log::info ( \DB::getQueryLog () );
			} else if ($obj_type_to == $OBJ_TYPE_TANK) {
				//\DB::enableQueryLog ();
				$rows = DB::table ( 'TANK_DATA_VALUE AS a' )->join ( 'TANK AS b', 'a.TANK_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.TANK_ID', $arrfixed )->SELECT ( 'a.TANK_ID AS OBJECT_ID', 'a.OCCUR_DATE', 'a.OCCUR_DATE AS OCCUR_DATE_STR', 'TANK_' . $alloc_attr . ' AS FIXED_VALUE' )->get ();
				//\Log::info ( \DB::getQueryLog () );
			} else if ($obj_type_to == $OBJ_TYPE_STORAGE) {
				//\DB::enableQueryLog ();
				$rows = DB::table ( 'STORAGE_DATA_VALUE AS a' )->join ( 'STORAGE AS b', 'a.STORAGE_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.STORAGE_ID', $arrfixed )->SELECT ( 'a.STORAGE_ID AS OBJECT_ID', 'a.OCCUR_DATE', 'a.OCCUR_DATE AS OCCUR_DATE_STR', 'STORAGE_' . $alloc_attr . ' AS FIXED_VALUE' )->get ();
				//\Log::info ( \DB::getQueryLog () );
			}
			$this->_log ( "Create allocation data from fixed objects (id: $ids_fixed):", 2 );
			$total_fixed = 0;

			foreach ( $rows as $row ) {
				$v_to = $row->FIXED_VALUE;
				$total_fixed += $v_to;
				if ($obj_type_to == $OBJ_TYPE_FLOW) {
					$ro = FlowDataAlloc::where ( [
							'FLOW_ID' => $row->OBJECT_ID
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

					if (count ( $ro ) > 0) {
						if ($this->alloc_act == "run") {
							FlowDataAlloc::where ( [
									'ID' => $ro->ID
							] )->update ( [
									'FL_DATA_' . $alloc_attr => $v_to
							] );
						}
						$sSQL = "update FLOW_DATA_ALLOC set FL_DATA_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
					} else {
						if ($this->alloc_act == "run") {
							FlowDataAlloc::insert ( [
									'FLOW_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FL_DATA_' . $alloc_attr => $v_to
							] );
						}
						$sSQL = "insert into FLOW_DATA_ALLOC(`FLOW_ID`,`OCCUR_DATE`,FL_DATA_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
					}

					$this->_log ( $sSQL, 2 );
				}
				else if ($obj_type_to == $OBJ_TYPE_EU) {
					$ro = EnergyUnitDataAlloc::where ( [
							'EU_ID' => $row->OBJECT_ID,
							'FLOW_PHASE' => $alloc_phase,
							'EVENT_TYPE' => $event_type,
							'ALLOC_TYPE' => $alloc_type
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();
					if (count ( $ro ) > 0) {
						if ($this->alloc_act == "run") {
							EnergyUnitDataAlloc::where ( [
									'ID' => $ro->ID
							] )->update ( [
									'EU_DATA_' . $alloc_attr_eu => $v_to
							] );
						}
						$sSQL = "update ENERGY_UNIT_DATA_ALLOC set EU_DATA_" . $alloc_attr_eu . "='" . $v_to . "' where ID=" . $ro->ID;
					} else {
						if ($this->alloc_act == "run") {
							$EnergyUnitDataAlloc = EnergyUnitDataAlloc::insert ( [
									'EU_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => $alloc_phase,
									'EVENT_TYPE' => $event_type,
									'ALLOC_TYPE' => $alloc_type,
									'EU_DATA_' . $alloc_attr_eu => $v_to
							] );
						}
						$sSQL = "insert into ENERGY_UNIT_DATA_ALLOC(`EU_ID`,`OCCUR_DATE`,FLOW_PHASE,EVENT_TYPE,ALLOC_TYPE,EU_DATA_" . $alloc_attr_eu . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $alloc_phase . "," . $event_type . "," . $alloc_type . "," . $v_to . ")";
					}

					$this->_log ( $sSQL, 2 );
				} else if ($obj_type_to == $OBJ_TYPE_TANK) {
					$ro = TankDataAlloc::where ( [
							'TANK_ID' => $row->OBJECT_ID
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

					if (count ( $ro ) > 0) {
						if ($this->alloc_act == "run") {
							TankDataAlloc::where ( [
									'ID' => $ro->ID
							] )->update ( [
									'TANK_' . $alloc_attr => $v_to
							] );
						}
						$sSQL = "update TANK_DATA_ALLOC set TANK_DATA_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
					} else {
						if ($this->alloc_act == "run") {
							TankDataAlloc::insert ( [
									'TANK_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'TANK_' . $alloc_attr =>$v_to
							] );
						}
						$sSQL = "insert into TANK_DATA_ALLOC(`TANK_ID`,`OCCUR_DATE`,TANK_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
					}

					$this->_log ( $sSQL, 2 );
				} else if ($obj_type_to == $OBJ_TYPE_STORAGE) {
					$ro = StorageDataAlloc::where ( [
							'STORAGE_ID' => $row->OBJECT_ID
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

					if (count ( $ro ) > 0) {
						if ($this->alloc_act == "run") {
							StorageDataAlloc::where ( [
									'ID' => $ro->ID
							] )->update ( [
									'STORAGE_' . $alloc_attr => $v_to
							] );
						}
						$sSQL = "update STORAGE_DATA_ALLOC set STORAGE_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
					} else {
						if ($this->alloc_act == "run") {
							StorageDataAlloc::insert ( [
									'STORAGE_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'STORAGE_' . $alloc_attr=>$v_to
							] );
						}
						$sSQL = "insert into STORAGE_DATA_ALLOC(`STORAGE_ID`,`OCCUR_DATE`,STORAGE_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
					}

					$this->_log ( $sSQL, 2 );
				}
			}

			$this->_log ( "total_fixed (std value ~ theor): $total_fixed", 2 );
			$total_from -= $total_fixed;
			$this->_log ( "total_from (minus total_fixed): $total_from", 2 );
		}

		// Alloc
		if ($total_to == 0) {
			$ret = $this->_log ( "total_to is zero, can not calculate", 1 );
			if ($ret === false)
				return false;
		}

		foreach ( $sSQL_alloc as $row ) {
			if ($row->ALLOC_THEOR === '' || $row->ALLOC_THEOR == null) {
				$row->ALLOC_THEOR = 0;
			}
			$v_to = $total_from * $row->ALLOC_THEOR / $total_to;
			//$this->_log ("v_to = $total_from * {$row->ALLOC_THEOR} / $total_to = ".$v_to, 2 );

			if ($obj_type_to == $OBJ_TYPE_FLOW) {
				$ro = FlowDataAlloc::where ( [
						'FLOW_ID' => $row->OBJECT_ID
				] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

				if (count ( $ro ) > 0) {
					if ($this->alloc_act == "run") {
						FlowDataAlloc::where ( [
								'ID' => $ro->ID
						] )->update ( [
								'FL_DATA_' . $alloc_attr => $v_to
						] );
					}
					$sSQL = "update FLOW_DATA_ALLOC set FL_DATA_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
				} else {
					if ($this->alloc_act == "run") {
						FlowDataAlloc::insert ( [
								'FLOW_ID' => $row->OBJECT_ID,
								'OCCUR_DATE' => $row->OCCUR_DATE,
								'FL_DATA_' . $alloc_attr => $v_to
						] );
					}
					$sSQL = "insert into FLOW_DATA_ALLOC(`FLOW_ID`,`OCCUR_DATE`,FL_DATA_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
				}

				$this->_log ( $sSQL, 2 );

				// ////// Flow COST_INT_CTR allocation
/*
				if ($this->alloc_act == "run") {
					FlowCoEntDataAlloc::where ( [
							'FLOW_ID' => $row->OBJECT_ID
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
					$sSQL = "delete from FLOW_CO_ENT_DATA_ALLOC where FLOW_ID=" . $row->OBJECT_ID . " and OCCUR_DATE='".$row->OCCUR_DATE."'";
					$this->_log ( $sSQL, 2 );
				}
*/
				$re_co = DB::table ( 'FLOW AS a' )->join ( 'COST_INT_CTR_DETAIL AS b', 'a.COST_INT_CTR_ID', '=', 'b.COST_INT_CTR_ID' )->where ( [
						'a.ID' => $row->OBJECT_ID,
						'b.FLOW_PHASE' => $alloc_phase
				] )->get ( [
						'a.COST_INT_CTR_ID',
						'b.BA_ID',
						'b.INTEREST_PCT AS ALLOC_PERCENT'
				] );

				foreach ( $re_co as $ro_co ) {
					$v_co = $v_to * $ro_co->ALLOC_PERCENT / 100;
					$ro = FlowCoEntDataAlloc::where ( [
							'FLOW_ID' => $row->OBJECT_ID,
							'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
							'BA_ID' => $ro_co->BA_ID
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();
					if (count ( $ro ) > 0) {
						FlowCoEntDataAlloc::where ( [
								'ID' => $ro->ID
						] )->update ( [
								'FL_DATA_' . $alloc_attr => $v_co
						] );
						$sSQL = "update FLOW_CO_ENT_DATA_ALLOC set FL_DATA_" . $alloc_attr . "=" . $v_co . " where ID=".$ro->ID;
					} else {
						if ($this->alloc_act == "run") {
							FlowCoEntDataAlloc::insert ( [
									'FLOW_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
									'BA_ID' => $ro_co->BA_ID,
									'FL_DATA_' . $alloc_attr => $v_co
							] );
						}
						$sSQL = "insert into FLOW_CO_ENT_DATA_ALLOC(`FLOW_ID`,`OCCUR_DATE`,COST_INT_CTR_ID,BA_ID,FL_DATA_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "','" . $ro_co->COST_INT_CTR_ID . "','" . $ro_co->BA_ID . "'," . $v_co . ")";
					}
					$this->_log ( $sSQL, 2 );
				}
				// /////// END of Flow COST_INT_CTR allocation
			} else if ($obj_type_to == $OBJ_TYPE_EU) {
				$ro = EnergyUnitDataAlloc::where ( [
						'EU_ID' => $row->OBJECT_ID,
						'FLOW_PHASE' => $alloc_phase,
						'EVENT_TYPE' => $event_type,
						'ALLOC_TYPE' => $alloc_type
				] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();
				if (count ( $ro ) > 0) {
					if ($this->alloc_act == "run") {
						EnergyUnitDataAlloc::where ( [
								'ID' => $ro->ID
						] )->update ( [
								'EU_DATA_' . $alloc_attr_eu => $v_to
						] );
					}
					$sSQL = "update ENERGY_UNIT_DATA_ALLOC set EU_DATA_" . $alloc_attr_eu . "='" . $v_to . "' where ID=" . $ro->ID;
				} else {
					if ($this->alloc_act == "run") {
						EnergyUnitDataAlloc::insert ( [
								'EU_ID' => $row->OBJECT_ID,
								'OCCUR_DATE' => $row->OCCUR_DATE,
								'FLOW_PHASE' => $alloc_phase,
								'EVENT_TYPE' => $event_type,
								'ALLOC_TYPE' => $alloc_type,
								'EU_DATA_' . $alloc_attr_eu => $v_to
						] );
					}
					$sSQL = "insert into ENERGY_UNIT_DATA_ALLOC(`EU_ID`,`OCCUR_DATE`,FLOW_PHASE,EVENT_TYPE,ALLOC_TYPE,EU_DATA_" . $alloc_attr_eu . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $alloc_phase . "," . $event_type . ",'" . $alloc_type . "'," . $v_to . ")";
				}

				$this->_log ( $sSQL, 2 );
/*
				if ($this->alloc_act == "run") {
					EnergyUnitCoEntDataAlloc::where ( [
							'EU_ID' => $row->OBJECT_ID,
							'FLOW_PHASE' => $alloc_phase,
							'EVENT_TYPE' => $event_type,
							'ALLOC_TYPE' => $alloc_type
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
					$sSQL = "delete from ENERGY_UNIT_CO_ENT_DATA_ALLOC where EU_ID=" . $row->OBJECT_ID." and OCCUR_DATE='".$row->OCCUR_DATE."' and FLOW_PHASE=$alloc_phase and EVENT_TYPE=$event_type and ALLOC_TYPE=$alloc_type";
					$this->_log ( $sSQL, 2 );
				}
*/
				// ////// Well COST_INT_CTR allocation
				$re_co = DB::table ( 'ENERGY_UNIT AS a' )->join ( 'COST_INT_CTR_DETAIL AS b', 'a.COST_INT_CTR_ID', '=', 'b.COST_INT_CTR_ID' )->where ( [
						'a.ID' => $row->OBJECT_ID,
						'b.FLOW_PHASE' => $alloc_phase
				] )->get ( [
						'a.COST_INT_CTR_ID',
						'b.BA_ID',
						'b.INTEREST_PCT AS ALLOC_PERCENT'
				] );
				foreach ( $re_co as $ro_co ) {
					$v_co = $v_to * $ro_co->ALLOC_PERCENT / 100;
					$ro = EnergyUnitCoEntDataAlloc::where ( [
									'EU_ID' => $row->OBJECT_ID,
									'FLOW_PHASE' => $alloc_phase,
									'EVENT_TYPE' => $event_type,
									'ALLOC_TYPE' => $alloc_type,
									'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
									'BA_ID' => $ro_co->BA_ID
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();
					if (count ( $ro ) > 0) {
						if ($this->alloc_act == "run") {
							EnergyUnitCoEntDataAlloc::where ( [
									'ID' => $ro->ID
							] )->update ( [
									'EU_DATA_' . $alloc_attr_eu => $v_co
							] );
						}
						$sSQL = "update ENERGY_UNIT_CO_ENT_DATA_ALLOC set EU_DATA_" . $alloc_attr_eu . "='" . $v_co . "' where ID=" . $ro->ID;
					} else {
						if ($this->alloc_act == "run") {
							EnergyUnitCoEntDataAlloc::insert ( [
									'EU_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => $alloc_phase,
									'EVENT_TYPE' => $event_type,
									'ALLOC_TYPE' => $alloc_type,
									'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
									'BA_ID' => $ro_co->BA_ID,
									'EU_DATA_' . $alloc_attr_eu => $v_co
							] );
						}
						$sSQL = "insert into ENERGY_UNIT_CO_ENT_DATA_ALLOC(`EU_ID`,`OCCUR_DATE`,FLOW_PHASE,EVENT_TYPE,ALLOC_TYPE,COST_INT_CTR_ID,BA_ID,EU_DATA_" . $alloc_attr_eu . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $alloc_phase . "," . $event_type . ",'" . $alloc_type . "','" . $ro_co->COST_INT_CTR_ID . "','" . $ro_co->BA_ID . "'," . $v_co . ")";
					}
					$this->_log ( $sSQL, 2 );
				}
				// /////// END of Well COST_INT_CTR allocation

				// completion, interval
				if ($alloc_attr == "GRS_VOL" || $alloc_attr == "GRS_MASS") {
					$this->allocWellCompletion ( $row->OBJECT_ID, $row->OCCUR_DATE, $alloc_phase, $event_type, $alloc_attr, $v_to );
				}
			} else if ($obj_type_to == $OBJ_TYPE_TANK) {
				$ro = TankDataAlloc::where ( [
						'TANK_ID' => $row->OBJECT_ID
				] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

				if (count ( $ro ) > 0) {
					if ($this->alloc_act == "run") {
						TankDataAlloc::where ( [
								'ID' => $ro->ID
						] )->update ( [
								'TANK_' . $alloc_attr => $v_to
						] );
					}
					$sSQL = "update TANK_DATA_ALLOC set TANK_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
				} else {
					if ($this->alloc_act == "run") {
						TankDataAlloc::insert ( [
								'TANK_ID' => $row->OBJECT_ID,
								'OCCUR_DATE' => $row->OCCUR_DATE,
								'TANK_' . $alloc_attr => $v_to
						] );
					}
					$sSQL = "insert into TANK_DATA_ALLOC(`TANK_ID`,`OCCUR_DATE`,TANK_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
				}

				$this->_log ( $sSQL, 2 );
			} else if ($obj_type_to == $OBJ_TYPE_STORAGE) {
				$ro = StorageDataAlloc::where ( [
						'STORAGE_ID' => $row->OBJECT_ID
				] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

				if (count ( $ro ) > 0) {
					if ($this->alloc_act == "run") {
						StorageDataAlloc::where ( [
								'ID' => $ro->ID
						] )->update ( [
								'STORAGE_' . $alloc_attr => $v_to
						] );
					}
					$sSQL = "update STORAGE_DATA_ALLOC set STORAGE_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
				} else {
					if ($this->alloc_act == "run") {
						StorageDataAlloc::insert ( [
								'STORAGE_ID' => $row->OBJECT_ID,
								'OCCUR_DATE' => $row->OCCUR_DATE,
								'STORAGE_' . $alloc_attr => $v_to
						] );
					}
					$sSQL = "insert into STORAGE_DATA_ALLOC(`STORAGE_ID`,`OCCUR_DATE`,STORAGE_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
				}

				$this->_log ( $sSQL, 2 );
			}
		}

		// Composition
		if ($alloc_comp) {
			$this->_log ( "Begin composition allocation", 2 );
			$comp_sqls = array ();
			$obj_type_code = ($obj_type_from == 1 ? "FLOW" : "WELL");
			$comp_total_from = array (
					"C1" => 0,
					"C2" => 0,
					"C3" => 0,
					"C4I" => 0,
					"C4N" => 0,
					"C5I" => 0,
					"C5N" => 0,
					"C6" => 0,
					"C7" => 0,
					"H2S" => 0,
					"CO2" => 0,
					"N2" => 0
			);
			$comp_total_to = array (
					"C1" => 0,
					"C2" => 0,
					"C3" => 0,
					"C4I" => 0,
					"C4N" => 0,
					"C5I" => 0,
					"C5N" => 0,
					"C6" => 0,
					"C7" => 0,
					"H2S" => 0,
					"CO2" => 0,
					"N2" => 0
			);
			$comp_total_rate = array (
					"C1" => 0,
					"C2" => 0,
					"C3" => 0,
					"C4I" => 0,
					"C4N" => 0,
					"C5I" => 0,
					"C5N" => 0,
					"C6" => 0,
					"C7" => 0,
					"H2S" => 0,
					"CO2" => 0,
					"N2" => 0
			);

			// step 1: calculate composition for all "from" object
			foreach ( $sSQL_alloc_from as $row ) {
				$this->_log ( "Calculate composition _FROM, object_name: " . $row->OBJECT_NAME . ",date" . $row->OCCUR_DATE_STR . ",2" );
				$object_id = $row->OBJECT_ID;
				$occur_date = $row->OCCUR_DATE_STR;
				$quality_from = $this->getQualityGas ( $object_id, $obj_type_code, $occur_date, $F );
				if ($quality_from) {
					foreach ( $comp_total_from as $x => $x_value ) {
						$comp_total_from [$x] += $row->ALLOC_VALUE * $quality_from [$x];
					}
					if ($obj_type_from == $OBJ_TYPE_FLOW) {
						if ($success && $this->alloc_act == "run") {
							FlowCompDataAlloc::where ( [
									'FLOW_ID' => $object_id
							] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
							$sSQL = "delete from FLOW_COMP_DATA_ALLOC where FLOW_ID=$object_id and OCCUR_DATE='$row[OCCUR_DATE]'";
							$this->_log ( $sSQL, 2 );

							$param = [ ];
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_C1,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->C1
							] );
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_C2,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->C2
							] );
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_C3,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->C3
							] );
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_C4I,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->C4I
							] );
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_C4N,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->C4N
							] );
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_C5I,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->C5I
							] );
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_C5N,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->C5N
							] );
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_C6,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->C6
							] );
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_C7,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->C7
							] );
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_H2S,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->H2S
							] );
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_CO2,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->CO2
							] );
							array_push ( $param, [
									'FLOW_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'COMPOSITION' => $quality_from->ID_N2,
									'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $quality_from->N2
							] );

							$sSQL = "insert into FLOW_COMP_DATA_ALLOC(FLOW_ID,OCCUR_DATE,COMPOSITION,FL_DATA_$alloc_attr) VALUES ";
							foreach ( $param as $pa ) {
								FlowCompDataAlloc::insert ( $pa );
								$sSQL .= $pa ['FLOW_ID'] . "," . $pa ['OCCUR_DATE'] . "," . $pa ['COMPOSITION'] . "," . $pa ['FL_DATA_' . $alloc_attr] . "\n";
							}
							$sSQL .= ")";

							$this->_log ( $sSQL, 2 );
						}
					} else {
						if ($success && $this->alloc_act == "run") {
							EnergyUnitCompDataAlloc::where ( [
									'EU_ID' => $object_id,
									'FLOW_PHASE' => 2
							] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
							$sSQL = "delete from ENERGY_UNIT_COMP_DATA_ALLOC where EU_ID=$object_id and FLOW_PHASE=2 and OCCUR_DATE='".$row->OCCUR_DATE."'";
							$this->_log ( $sSQL, 2 );

							$param = [ ];
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_C1,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->C1
							] );
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_C2,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->C2
							] );
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_C3,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->C3
							] );
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_C4I,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->C4I
							] );
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_C4N,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->C4N
							] );
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_C5I,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->C5I
							] );
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_C5N,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->C5N
							] );
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_C6,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->C6
							] );
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_C7,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->C7
							] );
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_H2S,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->H2S
							] );
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_CO2,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->CO2
							] );
							array_push ( $param, [
									'EU_ID' => $object_id,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => 2,
									'ALLOC_TYPE' => $alloc_type,
									'COMPOSITION' => $quality_from->ID_N2,
									'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $quality_from->N2
							] );

							$sSQL = "insert into ENERGY_UNIT_COMP_DATA_ALLOC(EU_ID,OCCUR_DATE,FLOW_PHASE,ALLOC_TYPE,COMPOSITION,EU_DATA_$alloc_attr_eu) VALUES ";
							foreach ( $param as $pa ) {
								EnergyUnitCompDataAlloc::insert ( $pa );
								$sSQL .= $pa ['EU_ID'] . "," . $pa ['OCCUR_DATE'] . "," . $pa ['FLOW_PHASE'] . "," . $pa ['ALLOC_TYPE'] . "," . $pa ['COMPOSITION'] . "," . $pa ['EU_DATA_' . $alloc_attr_eu] . "\n";
							}
							$sSQL .= ")";

							$this->_log ( $sSQL, 2 );
						}
					}
				} else {
					$ret = $this->_log ( "Quality data not found (_FROM object_id: $object_id, object_name: $row->OBJECT_NAME, date $row->OCCUR_DATE_STR)", 1 );
					if ($ret === false)
						return false;
				}
			}

			// step2:
			$this->_log ( "Calculate composition allocation rates", 2 );
			$obj_type_code = ($obj_type_to == 1 ? "FLOW" : "WELL");

			foreach ( $sSQL_alloc_to as $row ) {
				$object_id = $row->OBJECT_ID;
				$occur_date = $row->OCCUR_DATE_STR;
				$quality_to = getQualityGas ( $object_id, $obj_type_code, $occur_date, $F );
				if ($quality_to) {
					foreach ( $comp_total_to as $x => $x_value ) {
						$comp_total_to [$x] += $row->ALLOC_VALUE * $quality_to [$x];
					}
				} else {
					$ret = $this->_log ( "Quality data not found (_TO object_id: $object_id, object_name: $row->OBJECT_NAME, date $row->OCCUR_DATE_STR)", 1 );
					if ($ret === false)
						return false;
				}
			}
			if ($success) {
				foreach ( $comp_total_to as $x => $x_value ) {
					if ($comp_total_to [$x] == 0) {
						$comp_total_rate [$x] = - 1;
					} else {
						$comp_total_rate [$x] = $comp_total_from [$x] / $comp_total_to [$x];
					}
					$this->_log ( "[$x] comp_total_from = $comp_total_from[$x], comp_total_to = $comp_total_to[$x], rate = $comp_total_rate[$x]", 2 );
				}

				$result = AllocRunnerObjects::where ( [
						'RUNNER_ID' => $runner_id
				] )->get ();
				foreach ( $result as $row ) {
					$this->_log ( "Calculate composition allocation _TO, object_name: $row->OBJECT_NAME, date $row->OCCUR_DATE_STR", 2 );
					$object_id = $row->OBJECT_ID;
					$occur_date = $row->OCCUR_DATE_STR;
					$quality_from = $this->getQualityGas ( $object_id, $obj_type_code, $occur_date, $F );

					if ($quality_from && $this->alloc_act == "run") {
						if ($obj_type_to == $OBJ_TYPE_FLOW) {
							FlowCompDataAlloc::where ( [
									'FLOW_ID' => $object_id
							] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
							$sSQL = "delete from FLOW_COMP_DATA_ALLOC where FLOW_ID=$object_id and OCCUR_DATE='$row[OCCUR_DATE]'";
							$this->_log ( $sSQL, 2 );

							$sSQL = "insert into FLOW_COMP_DATA_ALLOC(FLOW_ID,OCCUR_DATE,COMPOSITION,FL_DATA_$alloc_attr) VALUES (";
							foreach ( $comp_total_rate as $x => $x_value ) {
								if ($x_value > 0 && $row->ALLOC_VALUE > 0 && $quality_from [$x] > 0) {
									$_v = $x_value * $row->ALLOC_VALUE * $quality_from [$x];
								} else {
									$_v = 0;
								}
								FlowCompDataAlloc::insert ( [
										'FLOW_ID' => $object_id,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'COMPOSITION' => $quality_from ["ID_$x"],
										'FL_DATA_' . $alloc_attr => $_v
								] );
								$sSQL .= $object_id . "," . $row->OCCUR_DATE . "," . $quality_from ["ID_$x"] . "," . $_v . "\n";
							}
							$sSQL .= ")";

							$this->_log ( $sSQL, 2 );
						} else {
							EnergyUnitCompDataAlloc::where ( [
									'EU_ID' => $object_id,
									'FLOW_PHASE' => 2
							] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
							$sSQL = "delete from ENERGY_UNIT_COMP_DATA_ALLOC where EU_ID=$object_id and FLOW_PHASE=2 and OCCUR_DATE='$row[OCCUR_DATE]'";
							$this->_log ( $sSQL, 2 );

							$sSQL = "insert into ENERGY_UNIT_COMP_DATA_ALLOC(EU_ID,OCCUR_DATE,FLOW_PHASE,ALLOC_TYPE,COMPOSITION,EU_DATA_$alloc_attr_eu) VALUES (";
							foreach ( $comp_total_rate as $x => $x_value ) {
								if ($x_value > 0 && $row->ALLOC_VALUE > 0 && $quality_from [$x] > 0) {
									$_v = $x_value * $row->ALLOC_VALUE * $quality_from [$x];
								} else {
									$_v = 0;
								}

								EnergyUnitCompDataAlloc::insert ( [
										'EU_ID' => $object_id,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'FLOW_PHASE' => $alloc_phase,
										'ALLOC_TYPE' => $alloc_type,
										'COMPOSITION' => $quality_from ["ID_$x"],
										'EU_DATA_' . $alloc_attr_eu => $_v
								] );
								$sSQL .= $object_id . "," . $row->OCCUR_DATE . "," . $alloc_phase . "," . "," . $alloc_type . "," . $quality_from ["ID_$x"] . "," . $_v . "\n";
							}
							$sSQL .= ")";

							$this->_log ( $sSQL, 2 );
						}
					} else {
						$ret = $this->_log ( "Quality data not found (_TO object_id: $object_id, object_name: $row[OBJECT_NAME], date $row[OCCUR_DATE_STR])", 1 );
						if ($ret === false)
							return false;
					}
				}
				if ($success) {
					$this->_log ( "Execute SQL composition allocation commands", 2 );
					/*
					 * foreach($comp_sqls as $sSQL)
						* {
						* if($_REQUEST["act"]=="run") mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
						* _log($sSQL);
						* }
					 */
				}
			}
		}
		$this->_log ( "End runner ID: $runner_id -------------------------------------------------------", 2 );
		return $success;
    }

    private function exec_job($job_id, $from_date, $to_date)
    {
    	$tmp = AllocJob::where(['ID'=>$job_id])->select('NAME')->first();
    	$job_name = $tmp['NAME'];

    	$this->_log("Begin job $job_name (id:$job_id) from date: $from_date, to date: $to_date at ".date("Y-m-d H:i:s"),2);

    	//\DB::enableQueryLog ();
    	$tmps = DB::table ('alloc_runner AS a' )
    	->join ( 'alloc_job AS b', 'a.job_id', '=', 'b.ID' )
    	->join ( 'code_alloc_value_type AS c', 'c.id', '=', 'b.value_type' )
    	->leftJoin ( 'code_alloc_value_type AS c2', 'c2.id', '=', 'a.THEOR_VALUE_TYPE' )
    	->where(['b.id'=>$job_id])
    	->orderBy('a.ORDER')
    	->get(['a.ID AS RUNNER_ID', 'a.ALLOC_TYPE', 'a.THEOR_PHASE', 'c.CODE AS ALLOC_ATTR_CODE', 'c2.CODE AS THEOR_ATTR_CODE', 'b.*']);
    	//\Log::info ( \DB::getQueryLog () );

    	$count=0;
    	foreach ($tmps as $row){
    		$alloc_attr=$row->ALLOC_ATTR_CODE;
    		$alloc_type=$row->ALLOC_TYPE;
    		$theor_attr=$row->THEOR_ATTR_CODE;
    		$theor_phase=$row->THEOR_PHASE;

    		$alloc_oil=($row->ALLOC_OIL == 1);
    		$alloc_gas=($row->ALLOC_GAS == 1);
    		$alloc_water=($row->ALLOC_WATER == 1);
    		$alloc_gaslift=($row->ALLOC_GASLIFT == 1);
    		$alloc_condensate=($row->ALLOC_CONDENSATE == 1);
    		$alloc_comp=($row->ALLOC_COMP == 1);
    		$runner_id=$row->RUNNER_ID;
    		if($alloc_oil) $this->run_runner($runner_id, $from_date, $to_date, $alloc_attr, 1,$theor_phase,$alloc_comp,$alloc_type,$theor_attr);
    		if($alloc_gas) $this->run_runner($runner_id, $from_date, $to_date, $alloc_attr, 2,$theor_phase,$alloc_comp,$alloc_type,$theor_attr);
    		if($alloc_water) $this->run_runner($runner_id, $from_date, $to_date, $alloc_attr, 3,$theor_phase,$alloc_comp,$alloc_type,$theor_attr);
    		if($alloc_gaslift) $this->run_runner($runner_id, $from_date, $to_date, $alloc_attr, 21,$theor_phase,$alloc_comp,$alloc_type,$theor_attr);
    		if($alloc_condensate) $this->run_runner($runner_id, $from_date, $to_date, $alloc_attr, 5,$theor_phase,$alloc_comp,$alloc_type,$theor_attr);
    		$count++;
    	}
    	if($count==0){
    		$this->_log("No runner to run!",2);
    		$this->_log("End job ID: $job_id =====================================================================",2);
    	}
    }

    private function fff()
    {
    	//echo "<b>Allocation request: ".$_REQUEST["act"]."</b><br>";
    }

    public function finalizeTask($task_id,$status,$log,$email){
    	if($task_id>0){

    		$now = Carbon::now('Europe/London');
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
