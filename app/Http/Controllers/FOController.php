<?php

namespace App\Http\Controllers;

use App\Models\CfgFieldProps;
use App\Models\CodeSafetySeverity;
use App\Models\Safety;
use DB;

use Illuminate\Http\Request;
 
class FOController extends Controller {
	
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function safety(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date'],
				)
		);
		return view ( 'front.safety',['filters'=>$filterGroups]);
	}
	
	public function loadSafety(Request $request){

			$facility_id = $request->input('Facility');
			$created_date = $request->input('date_begin');
			
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
				->where('c.created_date','=', [date('Y-m-d',strtotime($created_date))]);
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
		$success = 1;
		
		 DB::beginTransaction();
		
 		try {  
			foreach ($data as $obj){
				
				$obj['CATEGORY_ID'] = $obj['XID'];
				unset($obj['XID']);
				$obj['CREATED_DATE'] = date('Y-m-d',strtotime($obj['CREATED_DATE']));
				if(!isset($obj['SEVERITY_ID']) && empty($obj['SEVERITY_ID'])){
					$obj['SEVERITY_ID'] = null;
				}
				
				$condition = array(
						'CATEGORY_ID'=>$obj['CATEGORY_ID'],
						'FACILITY_ID'=>$obj['FACILITY_ID']
				);
				\DB::enableQueryLog();
				Safety::updateOrCreate($condition,$obj);
				\Log::info(\DB::getQueryLog());
			}
			
		} catch(\Exception $e)
		{
			DB::rollback();
			$success = 0;
			return response()->json($success);
		} 
		
		DB::commit(); 
		
		return response()->json($success);
	}
}
