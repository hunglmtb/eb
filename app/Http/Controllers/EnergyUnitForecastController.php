<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

class EnergyUnitForecastController extends CodeController {
    
	public function getWorkingTable($postData){
		$data_source 	= 	$postData['ExtensionDataSource'];
		$src			=	'ENERGY_UNIT';
		$table			=	$src."_DATA_".$data_source;
		$mdl 			= 	\Helper::getModelName($table);
		return $mdl::getTableName();
	}
	
	public function getProperties($dcTable,$facility_id,$occur_date){
		$properties = $this->getOriginProperties($dcTable);
		$results = ['properties'	=>$properties,
		];
		return $results;
	}
	
	public function getOriginProperties($dcTable){
		$properties = collect([
					(object)['data' =>	'OCCUR_DATE'	,'title' => 'Occur time'    	,	'width'=>40,'INPUT_TYPE'=>3,],
					(object)['data' =>	'T'				,'title' => 'Time'    			,	'width'=>30,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>2],
					(object)['data' =>	'V'				,'title' => 'Value'    			,	'width'=>30,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>3],
		]);
		return $properties;
	}
	
	public function getFirstProperty($dcTable){
    	return  null;
    }
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$date_end 		= 	$postData['date_end'];
    	$date_end		= 	Carbon::parse($date_end);
		$id 			= 	$postData['EnergyUnit'];
		
		$phase_type 	= 	$postData['ExtensionPhaseType'];
		$value_type 	= 	$postData['ExtensionValueType'];
		$data_source 	= 	$postData['ExtensionDataSource'];
		$table			=	$dcTable;
		$mdl 			= 	\Helper::getModelName($table);
	   
	    $where = [	"EU_ID" 			=> $id,
	    			"FLOW_PHASE" 		=> $phase_type,];
	    
// 		\DB::enableQueryLog();
	    $dataSet = $mdl::where($where)
					    ->whereDate("OCCUR_DATE", '>=', $occur_date)
					    ->whereDate("OCCUR_DATE", '<=', $date_end)
					    ->select(
					    		"OCCUR_DATE",
					    		\DB::raw("'$occur_date' as T "),
					    		"EU_DATA_$value_type as V"
					    		)
					   	->orderBy('OCCUR_DATE')
					    ->get();
// 		\Log::info(\DB::getQueryLog());
					    					
    	return ['dataSet'=>$dataSet];
    }
    
    
    public function run(Request $request){
    	$postData = $request->all();
    	$date_end 		= 	$postData['date_end'];
    	$date_end		= 	Carbon::parse($date_end);
    	$id 			= 	$postData['EnergyUnit'];
    	 
    	$phase_type 	= 	$postData['ExtensionPhaseType'];
    	$value_type 	= 	$postData['ExtensionValueType'];
    	$data_source 	= 	$postData['ExtensionDataSource'];
    	$table			=	$dcTable;
    	$mdl 			= 	\Helper::getModelName($table);
    	
    	$cb_update_db	=	$postData['cb_update_db'];
    	$a				=	$postData['a'];
    	$b				=	$postData['b'];
    	$u				=	$postData['u'];
    	$l				=	$postData['l'];
    	$m				=	$postData['m'];
    	$c1				=	$postData['c1'];
    	$c2				=	$postData['c2'];
    	
		$mkey="_".date("Ymdhis_").rand(100,1000);
		
    	if (!array_key_exists('editedData', $postData)&&!array_key_exists('deleteData', $postData)) {
    		return response()->json('no data 2 update!');
    	}
    
    	$results = ['updatedData'=>$updatedData,
    			'postData'=>$postData];
    	if (array_key_exists('lockeds', $resultTransaction)) {
    		$results['lockeds'] = $resultTransaction['lockeds'];
    	}
    	return response()->json($results);
    }
    
}
