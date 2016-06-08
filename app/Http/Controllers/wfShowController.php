<?php

namespace App\Http\Controllers;
use App\Models\TmWorkflow;
use App\Models\TmWorkflowTask;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Jobs\runAllocation;
use App\Models\EbFunctions;

class wfShowController extends Controller {
	
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	
	public function loadData() {
		$tmworkflow = $this->getTmworkflow();
		return view ( 'front.wfshow',  ['tmworkflow'=>$tmworkflow]);
	}
	
	public function getTmworkflow(){
		$user_name = 'tung';
		
		\DB::enableQueryLog ();
		$tmworkflowtask = collect(TmWorkflowTask::whereIn('ISRUN', [2,3])
			->where('USER', 'like', '%,'.$user_name.',%')
			->orWhere('USER', 'like', $user_name.'%')
			->get(['WF_ID']))->toArray();
			\Log::info ( \DB::getQueryLog () );
			
		\DB::enableQueryLog ();			
		$tmworkflow = TmWorkflow::where(['ISRUN'=>'YES'])
		->whereIn('ID', $tmworkflowtask)
		->get(['ID', 'NAME']);
		\Log::info ( \DB::getQueryLog () );
		
		return $tmworkflow;
	}
	
	public function reLoadtTmworkflow(){
		$tmworkflow = $this->getTmworkflow();
		return response ()->json ( $tmworkflow );
	}
	
	public function finish_workflowtask(Request $request){
		$data = $request->all ();
	
		$now = Carbon::now('Europe/London');
		$time = date('Y-m-d H:i:s', strtotime($now));
	
		TmWorkflowTask::where(['ID'=>$data['ID']])->update(['ISRUN'=>1, 'FINISH_TIME'=>$time]);
	
		$objRun = new runAllocation(null, null);
		$objRun->processNextTask($data['ID']);
		
		return response ()->json ( 'OK' );
	}
	
	public function openTask(Request $request){
		$data = $request->all ();
	
		if($data['act'] == "opentask"){
			$taskcode = $data['taskcode'];
			$ebfunctions = EbFunctions::where(['CODE'=>$taskcode])->select('PATH')->first();
			$url = $ebfunctions['PATH'];
			
			return response ()->json ( ['url'=>$url] );
		}
		
		return response ()->json ( 'OK' );
	}
}