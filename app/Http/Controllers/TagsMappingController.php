<?php

namespace App\Http\Controllers;
use App\Models\IntMapTable;
use App\Models\IntObjectType;
use Illuminate\Http\Request;

class TagsMappingController extends CodeController {
    
	protected $extraDataSetColumns;
	
	public function __construct() {
		parent::__construct();
		$this->extraDataSetColumns = [	
										'TABLE_NAME'		=>	[	'column'	=>'COLUMN_NAME',
																	'model'		=>'CodeDeferCode2'],
										/* 'TABLE_NAME'		=>	[	'column'	=>'TABLE_NAME',
																	'model'		=>'CodeDeferCode2'], */
									];
	}
	
	public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
		$object_type = $postData['IntObjectType'];
    	$object_id = $postData['ObjectName'];
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
		$objectType=IntObjectType::find($object_type);
		$xtable=$objectType->CODE;
    	 
    	
//     	$objects = "select ID,`NAME` from $xtable where FACILITY_ID=$facility_id order by `NAME`";
    	$objects = \DB::table($xtable)->where("FACILITY_ID",'=',$facility_id)
    									->orderBy('NAME')
    									->get(['ID','NAME',"ID as value","NAME as text"]);
    	 
    	$extraDataSet = [
    			'OBJECT_ID'			=>	$objects,
//     			'TABLE_NAME'		=>	$c_table
    	];
	   
	    $where = [
	    		"$xtable.FACILITY_ID" 	=> $facility_id,
 	    		"$dcTable.OBJECT_TYPE" 	=> $object_type,
	    		];
	    
	    if ($object_id>0) $where["$dcTable.OBJECT_ID"]= $object_id;
	     
	    $dataSet = $mdl::join($xtable,"$dcTable.OBJECT_ID",'=',"$xtable.ID")
		 				->where($where)
	    				->select(
								"$dcTable.ID as DT_RowId",
								"$dcTable.*"
								)
		 				->get();
	    
		 				
    	if ($dataSet&&$dataSet->count()>0) {
    		$bunde = ['OBJECT_TYPE' => $object_type];
    		foreach($this->extraDataSetColumns as $column => $extraDataSetColumn){
    			$extraDataSet[$column] = $this->getExtraEntriesBy($column,$extraDataSetColumn,$dataSet,$bunde);
    		}
    	}
    	
    	$data = IntMapTable::where("OBJECT_TYPE",'=',$object_type)
    	->orderBy('NAME')
    	->get([
    			'TABLE_NAME as NAME',
    			"TABLE_NAME as value",
    			"TABLE_NAME as text"]);
    	
     	$extraDataSet['TABLE_NAME']['TABLE_NAME'] = $data;
    	 
    	return ['dataSet'=>$dataSet,
     			'extraDataSet'=>$extraDataSet
    	];
    }
    
    
    public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde){
    	$data = null;
    	switch ($sourceColumn) {
    		case 'TABLE_NAME':
    			//note for multi db
				$db_schema="energy_builder";
//        			\DB::enableQueryLog();
				$data = \DB::table('INFORMATION_SCHEMA.COLUMNS')
		    				->where('TABLE_SCHEMA','=',$db_schema)
		    				->where('TABLE_NAME','=',$sourceColumnValue)
		    				->whereIn('DATA_TYPE',['decimal'])
		    				->select(
							    			"COLUMN_NAME as ID",
							    			"COLUMN_NAME as NAME",
		    								"COLUMN_NAME as value",
							    			"COLUMN_NAME as text"
							    			)
		    				->get();
//  		     	\Log::info(\DB::getQueryLog());
    			break;
    	}
    	return $data;
    }
    
    
    public function loadsrc(Request $request){
    	$postData = $request->all();
    	$sourceColumn = $postData['name'];
    	$sourceColumnValue = $postData['value'];
    	$bunde = [];
    	$extraDataSetColumn = $this->extraDataSetColumns[$sourceColumn];
    	$targetColumn = $extraDataSetColumn['column'];
    	$data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde);
    	$dataSet = [
    				$targetColumn	=>[	'data'			=>	$data,
								    	'ofId'			=>	$sourceColumnValue,
								    	'sourceColumn'	=>	$sourceColumn]
    				];
    	 
    	return response()->json(['dataSet'=>$dataSet,
    							'postData'=>$postData]);
    }
}
