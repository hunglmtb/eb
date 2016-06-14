<?php

namespace App\Http\Controllers;
use App\Models\EbFunctions;
use App\Models\TmWorkflow;
use App\Models\TmWorkflowTask;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
		
		$user_name = '';
		if((auth()->user() != null)){
			$user_name = auth()->user()->username;
		}
		
		$tmworkflowtask = collect(TmWorkflowTask::whereIn('ISRUN', [2,3])
			 ->where ( function ($q) use ($user_name) {
				$q->where('USER', 'like', '%,'.$user_name.',%');
				$q->orWhere('USER', 'like', $user_name.',%');
			} ) 
			->get(['WF_ID']))->toArray();
		
		$tmworkflow = TmWorkflow::where(['ISRUN'=>'YES'])
		->whereIn('ID', $tmworkflowtask)
		->get(['ID', 'NAME']);
		
		$tmworkflow = TmWorkflow::where(['ISRUN'=>'YES'])
		->whereIn('ID', $tmworkflowtask)
		->get(['ID', 'NAME']);
		
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
	
		$objRun = new WorkflowProcessController(null, null);
		$objRun->processNextTask($data['ID']);
		
		return response ()->json ( ['ok'=>'OK'] );
	}
	
	public function openTask(Request $request){
		$data = $request->all ();
	
		if($data['act'] == "opentask"){
			$taskcode = $data['taskcode'];
			$ebfunctions = EbFunctions::where(['CODE'=>$taskcode])->select('PATH')->first();
			$url = $ebfunctions['PATH'];
			
			return response ()->json ( ['url'=>$url] );
		}
	}
	
	public function countWorkflowTask(){		
		$user_name = '';
		if((auth()->user() != null)){
			$user_name = auth()->user()->username;
		}
					
		$tmworkflow = collect(TmWorkflow::where(['ISRUN'=>'yes'])
		->get(['ID']))->toArray();
			
		$tmworkflow = collect(TmWorkflow::where(['ISRUN'=>'yes'])
		->get(['ID']))->toArray();

		$tmworkflowtask = TmWorkflowTask::whereIn('ISRUN', [2,3])
			->whereIn('WF_ID', $tmworkflow)
			->where ( function ($q) use ($user_name) {
				$q->where('USER', 'like', '%,'.$user_name.',%');
				$q->orWhere('USER', 'like', $user_name.',%');
				$q->orWhere('USER', 'like', '%,'.$user_name);
				$q->orWhere('USER', '=', $user_name);
			} )
			->get(['WF_ID']);

		
		return response ()->json ( count($tmworkflowtask) );
	}
}