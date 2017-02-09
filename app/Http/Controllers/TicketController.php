<?php

namespace App\Http\Controllers;
use App\Models\Tank;

class TicketController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "RunTicketFdcValue";
		$this->idColumn = 'ID';
		$this->phaseColumn = 'FLOW_PHASE';
		
 		$this->valueModel = "RunTicketValue";
		$this->keyColumns = [$this->idColumn,$this->phaseColumn,'TANK_ID','OCCUR_DATE','TICKET_NO'];
		
		$this->extraDataSetColumns = [	
										'PHASE_TYPE'		=>	'TARGET_TANK',
		];
	}
	
	public function enableBatchRun($dataSet,$mdlName,$postData){
		return true;
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>50];
	}
	
	public function getObjectIds($dataSet,$postData){
		$objectIds = $dataSet->map(function ($item, $key) {
			return ["DT_RowId"			=> $item->DT_RowId,
					"FLOW_PHASE"		=> $item->FLOW_PHASE,
					"TANK_ID"			=> $item->TANK_ID,
    				"ID"				=> $item->ID,
			];
		});
		return $objectIds;
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
    	$object_id 		= $postData['Tank'];
    	$date_end 		= $postData['date_end'];
    	$date_end		= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	 
    	$tank = Tank::getTableName();
    	
    	$wheres = ['TANK_ID' => $object_id];
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::join($tank,"$dcTable.TANK_ID", '=', "$tank.ID")
    					->where($wheres)
				    	->whereBetween('OCCUR_DATE', [$occur_date,$date_end])
				    	->select(
				    			"$dcTable.*",
								"$dcTable.ID as $dcTable",
				    			"$dcTable.TANK_ID as OBJ_ID",
				    			"$tank.PRODUCT as FLOW_PHASE",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.OCCUR_DATE as T_OCCUR_DATE"
				    			) 
  		    			->orderBy("$dcTable.OCCUR_DATE")
  		    			->orderBy("$dcTable.LOADING_TIME")
  		    			->orderBy("$dcTable.TICKET_NO")
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
  		$extraDataSet 	= $this->getExtraDataSet($dataSet, null);
    	return ['dataSet'		=>$dataSet,
     			'extraDataSet'	=>$extraDataSet
    	];
    }
    
    public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$postData){
    	$data = null;
    	switch ($sourceColumn) {
    		case 'PHASE_TYPE':
    			switch ($extraDataSetColumn) {
    				case 'TARGET_TANK':
    					$targetEloquent = "App\Models\Tank";
    					$data = $targetEloquent::where('PRODUCT','=',$sourceColumnValue)
    											->select(
    												"*",
					    							"ID as value",
					    							"NAME as text"
					    						)
    											->get();
    					break;
    			}
    			break;
    	}
    	return $data;
    }
    
    public function checkExistPostEntry($editedData,$model,$element,$idColumn){
    	$tankID = 'TANK_ID';
    	$occurDate = 'OCCUR_DATE';
    	$ticketNo = 'TICKET_NO';
    	$tankIds = array_column($editedData[$model],$tankID);
    	$occurDates = array_column($editedData[$model],$occurDate);
    	$ticketNos = array_column($editedData[$model],$ticketNo);
    	
    	$notExist = (array_search($element[$tankID],$tankIds)===FALSE)||
		    	(array_search($element[$occurDate],$occurDates)===FALSE)||
		    	(array_search($element[$ticketNo],$ticketNos)===FALSE);
    	return $notExist;
    }
    
    public function getHistoryConditions($dcTable,$rowData,$row_id){
    	return ['TANK_ID'			=>	$rowData["TANK_ID"],
    	];
    }
    
    public function getHistoryData($mdl, $field,$rowData,$where, $limit){
    	$row_id			= $rowData['ID'];
    	if ($row_id<=0) return [];
    
    	$occur_date		= $rowData['OCCUR_DATE'];
    	$history 		= $mdl::where($where)
					    	->whereDate('OCCUR_DATE', '<', $occur_date)
					    	->whereNotNull($field)
					    	->orderBy('OCCUR_DATE','desc')
					    	->skip(0)->take($limit)
					    	->select(\DB::raw("concat(concat(OCCUR_DATE,' '), LOADING_TIME) as OCCUR_DATE"),
					    			"$field as VALUE"
					    			)
					    	->get();
    	return $history;
    }
    
    public function getFieldTitle($dcTable,$field,$rowData){
    	$row = Tank::where(['ID'=>$rowData['TANK_ID']])
    	->select('NAME')
    	->first();
    	$obj_name		= $row?$row->NAME:"";
    	return $obj_name;
    }
}
