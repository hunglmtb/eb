<?php

namespace App\Http\Controllers;

use App\Models\CfgFieldProps;
use App\Models\CodeSafetySeverity;
use App\Models\Safety;
use DB;

use Illuminate\Http\Request;
 
class FOController extends Controller {
	 
	public function __construct() {
		$this->middleware ( 'auth' );
	}
	
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function safety(){
		
		return view('front.safety');
	}
	
	public function loadSafety(Request $request){

			$facility_id = $request->input('_facility_id');
			$created_date = $request->input('_created_date');
			
			// get severity
			$severity = CodeSafetySeverity::where('active', '=', 1)->get();	
			
			$obj = new CommonController();		
			$cfgFieldProps = $obj->getField('SAFETY')[0];
			
			// search data
			$listData = DB::table('code_safety_category AS a')		
			->join('facility_safety_category AS b', function ($join) use($facility_id){
				$join->on('a.id', '=', 'b.safety_category_id')
				->where('b.facility_id', '=', [$facility_id]);
			})
			->leftjoin('safety AS c', function ($ljoin) use ($facility_id, $created_date){
				$ljoin->on('a.id', '=', 'c.category_id')
				->where('c.facility_id', '=', [$facility_id])
				->where('c.created_date','=', [$created_date]);
			})
			->select($cfgFieldProps['listColumn'])
			->where('a.active',1)
			->get();
			
			// return 
			$result = array(
				[
					'severity' => $severity, 
					'search' => $listData, 
					'thead' => $cfgFieldProps['listLabel'],		
					'totalWidth' => $cfgFieldProps['totalWidth'],
				]
			);
			
			return response()->json($result);
	}	
	
	public function saveSafety(Request $request){
		$data = $request->input('_sData');
		$success = 0;
		
		DB::beginTransaction();
		
		foreach ($data as $obj){
			$safety = Safety::where('CATEGORY_ID', '=', [$obj['XID']])
			->where('FACILITY_ID', '=', $obj['FACILITY_ID'])
			->select('ID')
			->first();
			
			$obj['CATEGORY_ID'] = $obj['XID'];
			unset($obj['XID']);
			
			$objSafety = new Safety();
			
			try {
				//\DB::enableQueryLog();
				if(!empty($safety)){				
					$objSafety->where('FACILITY_ID', '=', $obj['FACILITY_ID'])
					->where('CATEGORY_ID', '=', [$obj['CATEGORY_ID']])
					->update($obj);
				}else{				
					$objSafety->insert($obj);
				}
				$success = 1;
				//\Log::info(\DB::getQueryLog());
			} catch(\Exception $e)
			{
				DB::rollback();
				$success = 0;
				throw $e;
			}
		}
		
		DB::commit();
		
		return $success;
	}
}
