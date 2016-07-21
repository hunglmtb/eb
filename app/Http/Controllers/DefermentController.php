<?php

namespace App\Http\Controllers;
use App\Models\CodeDeferGroupType;
use App\Models\Deferment;
use App\Models\DefermentDetail;
use App\Models\DefermentGroup;
use App\Models\DefermentGroupEu;
use App\Models\EnergyUnit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DefermentController extends CodeController {
    
	protected $extraDataSetColumns;
	
	public function __construct() {
		parent::__construct();
		$this->extraDataSetColumns = [	'DEFER_GROUP_TYPE'	=>	[	'column'	=>'DEFER_TARGET',
																	'model'		=>'DefermentGroup'],
										'CODE1'				=>	[	'column'	=>'CODE2',
																	'model'		=>'CodeDeferCode2'],
										'CODE2'				=>	[	'column'	=>'CODE3',
																	'model'		=>'CodeDeferCode3']
									];
		
		$this->keyColumns = [$this->idColumn,$this->phaseColumn];
	}
	
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	$date_end = $postData['date_end'];
//     	$date_end = Carbon::parse($date_end);
    	$date_end = \Helper::parseDate($date_end);
    	
    	$extraDataSet = [];
    	$dataSet = null;
    	$codeDeferGroupType = CodeDeferGroupType::getTableName();
	    
	    $where = ['FACILITY_ID' => $facility_id];
    	$dataSet = $mdl::leftJoin($codeDeferGroupType,"$dcTable.DEFER_GROUP_TYPE",'=',"$codeDeferGroupType.ID")
							    	->where($where)
							    	->whereDate("$dcTable.BEGIN_TIME", '>=', $occur_date)
							    	->whereDate("$dcTable.END_TIME", '<=', $date_end)
							    	->select(
 							    			"$dcTable.ID as $dcTable",
							    			"$codeDeferGroupType.CODE as DEFER_GROUP_CODE",
							    			"$dcTable.ID as DT_RowId",
							    			"$dcTable.ID",
							    			"$dcTable.*"
							    			)
// 					    			->orderBy($dcTable)
 									->get();
	    
    	if ($dataSet&&$dataSet->count()>0) {
    		$bunde = ['FACILITY_ID' => $facility_id];
    		foreach($this->extraDataSetColumns as $column => $extraDataSetColumn){
    			$extraDataSet[$column] = $this->getExtraEntriesBy($column,$extraDataSetColumn,$dataSet,$bunde);
    		}
    	}
    	
    	return ['dataSet'=>$dataSet,
     			'extraDataSet'=>$extraDataSet
    	];
    }
    
    public function putExtraBundle(&$bunde,$sourceColumn,$entry){
    	if ($sourceColumn=='DEFER_GROUP_TYPE') {
    		$bunde['DEFER_GROUP_CODE'] = $entry->DEFER_GROUP_CODE;
    	}
    }
    
    public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde){
    	$data = null;
    	switch ($sourceColumn) {
    		case 'CODE1':
    		case 'CODE2':
		    	$targetModel = $extraDataSetColumn['model'];
		    	$targetEloquent = "App\Models\\$targetModel";
		    	$data = $targetEloquent::where('PARENT_ID','=',$sourceColumnValue)->select(
									    			"*",
		 							    			"ID as value",
									    			"NAME as text"
									    			)->get();
    			break;
    			
    		case 'DEFER_GROUP_TYPE':
    			if ($bunde&&array_key_exists('DEFER_GROUP_CODE', $bunde)) {
	    			$defer_group_code = $bunde['DEFER_GROUP_CODE'];
	    			if ($defer_group_code=='WELL') {
	    				$facility_id = $bunde['FACILITY_ID'];
	    				$data = EnergyUnit::where(['FACILITY_ID'=>$facility_id,
	    									'FDC_DISPLAY'=>1
	    							])
	    							->select("ID","NAME","ID as value","NAME as text")
	    							->orderBy('text')
	    							->get();
	    			}
	    			else{
	    				$data = DefermentGroup::where(['DEFER_GROUP_TYPE'=>$sourceColumnValue])
							    				->select("ID","NAME","ID as value","NAME as text")
							    				->orderBy('text')
							    				->get();
	    			}
    			}
    			break;
    	}
    	return $data;
    }
    
    
    public function loadsrc(Request $request){
    	//     	sleep(2);
    	$postData = $request->all();
    	$sourceColumn = $postData['name'];
    	$sourceColumnValue = $postData['value'];
    	$bunde = [];
    	$dataSet = [];
    	if ($sourceColumn=='DEFER_GROUP_TYPE') {
    		$codeColumn = 'DEFER_GROUP_CODE';
    		$entry = CodeDeferGroupType::select("CODE as $codeColumn")->find($sourceColumnValue);
	    	$bunde[$codeColumn] = $entry->$codeColumn;
	    	
	    	$facility_id = $postData['Facility'];
	    	$bunde['FACILITY_ID'] = $facility_id;
    	}
    	$targetExists = true;
    	$loopIndex = 0;
    	while($loopIndex<5){
    		$loopIndex++;
    		if (!array_key_exists($sourceColumn, $this->extraDataSetColumns)) break;
	    	$extraDataSetColumn = $this->extraDataSetColumns[$sourceColumn];
	    	$targetColumn = $extraDataSetColumn['column'];
	    	$data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde);
	    	$dataSet[$targetColumn] = [	'data'			=>	$data,
								    	'ofId'			=>	$sourceColumnValue,
								    	'sourceColumn'	=>	$sourceColumn
								    	];
	    	
	    	$sourceColumn = $targetColumn;
	    	$sourceColumnValue = $data&&$data->count()>0?$data[0]->ID:null;
	    	if (!$sourceColumnValue) break;
    	}
    	
    	return response()->json(['dataSet'=>$dataSet,
    							'postData'=>$postData]);
    }
    
    public function edit(Request $request){
    	$postData = $request->all();
    	$id = $postData['id'];
    	$defermentDetail =DefermentDetail::getTableName();
    	$properties = $this->getOriginProperties($defermentDetail);
    	
    	$dataSet = $this->getDefermentDetails($id);
	    $results = [];
    	$results['deferment'] = ['properties'	=>$properties,
	    							'dataSet'		=>$dataSet];
	    
    	return response()->json($results);
    }
    
    public function getDefermentDetails($id){
    	$defermentDetail =DefermentDetail::getTableName();
    	//     	$defermentGroupEu =DefermentGroupEu::getTableName();
    	$energyUnit =EnergyUnit::getTableName();
    	$deferment =Deferment::getTableName();
    	$dataSet = Deferment::join($energyUnit, "$deferment.DEFER_TARGET", '=', "$energyUnit.ID")
					    	->leftJoin($defermentDetail, function($join) use ($defermentDetail,$deferment,$energyUnit){
					    		$join->on("$defermentDetail.DEFERMENT_ID", '=', "$deferment.ID")
					    		->on("$defermentDetail.EU_ID",'=',"$energyUnit.ID");
					    	})
					    	->where("$deferment.ID",'=',$id)
					    	//well
					    	->where("$deferment.DEFER_GROUP_TYPE",'=',3)
					    	->select(
					    			"$energyUnit.ID as ID",
					    			"$energyUnit.NAME as NAME",
					    			"$energyUnit.ID as DT_RowId",
					    			"$deferment.DEFER_GROUP_TYPE",
					    			"$defermentDetail.*"
					    			)
					    			->get();
    	return $dataSet;
    }
    
    
    
    public function editSaving(Request $request){
    	$postData = $request->all();
    	$id = $postData['id'];
	    $mdlData = $postData['deferment'];
	    $columns = ['DEFERMENT_ID'=>$id];
	    foreach($mdlData as $key => $newData ){
	    	$columns['EU_ID'] = $newData['DT_RowId'];
	    	$newData['EU_ID'] = $newData['DT_RowId'];
	    	$newData['DEFERMENT_ID'] = $id;
	    	$returnRecord = DefermentDetail::updateOrCreate($columns, $newData);
	    }
	    
    	$dataSet = $this->getDefermentDetails($id);
    	$totalOfOvrDeferOilVol = $dataSet->sum('OVR_DEFER_OIL_VOL');
    	$totalOfOvrDeferGasVol = $dataSet->sum('OVR_DEFER_GAS_VOL');
    	$totalOfOvrDeferWaterVol = $dataSet->sum('OVR_DEFER_WATER_VOL');
    	
    	Deferment::where('ID', $id)
    			->update(array('OVR_DEFER_OIL_VOL' => $totalOfOvrDeferOilVol,
    					'OVR_DEFER_GAS_VOL' => $totalOfOvrDeferGasVol,
    					'OVR_DEFER_WATER_VOL' => $totalOfOvrDeferWaterVol
    			));
    	return response()->json('Edit Successfullly');
    }
    
    public function getHistoryConditions($dcTable,$rowData,$row_id){
    	return ['DEFER_TARGET'			=>	$rowData["DEFER_TARGET"],
    			'DEFER_GROUP_TYPE'		=>	$rowData["DEFER_GROUP_TYPE"],
    	];
    }
    
    public function getHistoryData($mdl, $field,$rowData,$where, $limit){
		$row_id			= $rowData['ID'];
    	if ($row_id<=0) return [];
    	
    	$occur_date		= $rowData['BEGIN_TIME'];
    	$history 		= $mdl::where($where)
						    	->whereDate('BEGIN_TIME', '<', $occur_date)
						    	->whereNotNull($field)
						    	->orderBy('BEGIN_TIME','desc')
						    	->skip(0)->take($limit)
						    	->select('BEGIN_TIME as OCCUR_DATE',
						    			"$field as VALUE"
						    			)
				    			->get();
    	return $history;
    }
    
    public function getFieldTitle($dcTable,$field,$rowData){
    	$row = EnergyUnit::where(['ID'=>$rowData['DEFER_TARGET']])
				    	 ->select('NAME')
				    	 ->first();
    	$obj_name		= $row?$row->NAME:"";
    	return $obj_name;
    }
}
