<?php

namespace App\Http\Controllers\DataVisualization;

use App\Http\Controllers\CodeController;
use App\Models\EbFunctions;
use App\Models\TmTask;

class TaskmanController extends CodeController {
	
	public function __construct() {
		parent::__construct();
		$this->extraDataSetColumns = [ 
				'task_group' => [ 
						'column' 	=> 'task_code',
						'model' 	=> 'EbFunctions' 
				] 
		];
	}
	
	public function getFirstProperty($dcTable) {
		return [ 
				'data' => $dcTable,
				'title' => '',
				'width' => 100 
		];
	}
	
	public function getDataSet($postData, $dcTable, $facility_id, $occur_date, $properties) {
		$mdlName 	= $postData[config("constants.tabTable")];
    	$mdl 		= "App\Models\\$mdlName";
    	$date_end 	= $postData['date_end'];
    	$date_end	= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
//     	$status		= 
//     	$wheres 	= ['STATUS' => 1];
    	$dataSet 	= $mdl::where('STATUS' ,'>', 0)
				    	->whereBetween('CDATE', [$occur_date,$date_end])
				    	->select(
				    			"$dcTable.*",
								"$dcTable.ID as $dcTable",
								"$dcTable.id as ID",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.CDATE as CDATE"
				    			) 
  		    			->orderBy("$dcTable.CDATE")
  		    			->get();
  		/* if ($dataSet&&$dataSet instanceof Collection && $dataSet->count()>0) {
  			$dataSet->each(function ($item, $key){
  				if ($item&&$item instanceof Model) {
  					$item->time_config	= $item->time_config;
  				}
  			});
  		} */
  		$extraDataSet 	= $this->getExtraDataSet($dataSet, null);
  		
		return [ 
				'dataSet' 		=> $dataSet,
     			'extraDataSet'	=>$extraDataSet
		]
		;
	}
	
	public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde){
		$data = null;
		switch ($sourceColumn) {
			case 'task_group':
				$group		= EbFunctions::find($sourceColumnValue);
				if ($group) $data = $group->ExtensionEbFunctions();
				break;
		}
		return $data;
	}
	
	public function start($id){
		$result		= ["CODE"	=>"ATTEMP_FAILT"];
		$task		= TmTask::find($id);
		if ($task) {
			$task->status		= 7;
			$task->save();
			$result["CODE"]		= "ATTEMP_SUCCESS";
			$result["status"]	= $task->status;
		}
		return response()->json($result);
	}
}