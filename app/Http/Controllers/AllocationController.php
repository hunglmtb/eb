<?php

namespace App\Http\Controllers;
use App\Models\AllocJob;
use App\Models\Network;
use App\Models\CodeAllocValueType;
use App\Jobs\runAllocation;

use DB;
use Illuminate\Http\Request;

class AllocationController extends Controller {
	
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	
	public function _index() {
		$network = Network::where(['NETWORK_TYPE'=>1])->get(['ID', 'NAME']);
		$result = [];
		foreach ($network as $n){
			$tmp = [];
			$count = AllocJob::where(['NETWORK_ID'=>$n->ID])->count();
			if($count > 0){
				$tmp['NAME'] = $n->NAME.'('.$count.')';
			}else{
				$tmp['NAME'] = $n->NAME;
			}
				
			$tmp['ID'] = $n->ID;
				
			array_push($result, $tmp);
		}
		
		return view ( 'front.runallocation', ['result'=>$result]);
	}
	
	public function getJobsRunAlloc(Request $request) {
		$data = $request->all ();
		
		$result = $this->getAllocJob($data['NETWORK_ID']);
						
		return response ()->json ( $result );
	}
	
	private function getAllocJob($network_id){
		
		$allocjob = AllocJob::getTableName ();
		$code_alloc_value_type = CodeAllocValueType::getTableName();
		
		$result = DB::table ( $allocjob . ' AS a' )
		->join ( $code_alloc_value_type . ' AS b', 'a.VALUE_TYPE', '=', 'b.ID' )
		->where ( ['a.NETWORK_ID' => $network_id])
		->orderBy('a.ID')->select('a.*', 'b.name AS value_type_name')->get();
		
		return $result;
	}
	
	public function run_runner(Request $request) {
		$data = $request->all ();
		
		$objRun = new runAllocation($data);
		return response ()->json ($objRun->handle());
	}
}