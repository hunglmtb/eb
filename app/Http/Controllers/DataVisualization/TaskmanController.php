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
				'title' => 'Command',
				'width' => 150 
		];
	}
	
	public function getDataSet($postData, $dcTable, $facility_id, $occur_date, $properties) {
		$mdlName 	= $postData[config("constants.tabTable")];
    	$mdl 		= "App\Models\\$mdlName";
    	$date_end 	= $postData['date_end'];
    	$date_end	= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
//      	$wheres 	= [];
     	$wheres 	= ['runby' => 1];
    	$dataSet 	= $mdl::where($wheres)
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
				$group		= EbFunctions::findByCode($sourceColumnValue);
				if ($group) $data = $group->ExtensionEbFunctions();
				break;
		}
		return $data;
	}
	
	public function update($command,$id){
		$result		= ["CODE"	=>"ATTEMP_FAILT"];
		$task		= TmTask::find($id);
		if ($task) {
			switch ($command) {
				case "start":
					$task->command		= TmTask::STARTING;
					if ($task->status	!= TmTask::RUNNING) {
						$task->status	= TmTask::READY;
						$task->command	= TmTask::NONE;
					}
					$task->save();
				break;
				case "stop":
					$task->command		= TmTask::CANCELLING;
					if ($task->status	!= TmTask::RUNNING) {
						$task->status	= TmTask::STOPPED;
						$task->command	= TmTask::NONE;
					}
					$task->save();
					break;
				case "refresh":
					break;
				default:
				break;
			}
			$result["CODE"]		= "ATTEMP_SUCCESS";
			$result["task"]		= $task;
		}
		return response()->json($result);
	}
}