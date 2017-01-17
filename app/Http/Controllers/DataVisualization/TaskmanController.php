<?php

namespace App\Http\Controllers\DataVisualization;

use App\Http\Controllers\CodeController;
use App\Models\EbFunctions;

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
    	
    	$wheres 	= ['STATUS' => 1];
    	$dataSet 	= $mdl::where($wheres)
				    	->whereBetween('CDATE', [$occur_date,$date_end])
				    	->select(
				    			"$dcTable.*",
								"$dcTable.ID as $dcTable",
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
				$group		= EbFunctions::find($sourceColumnValue);
				if ($group) $data = $group->ExtensionEbFunctions();
				break;
		}
		return $data;
	}
}