<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\CodePersonnelTitle;
use App\Models\CodeBaType;
use App\Models\PersonnelSumDay;
use App\Models\CodePersonnelType;

class PersonnelController extends CodeController {
	
	protected $extraDataSetColumns;
	
	public function __construct() {
		parent::__construct();
		$this->extraDataSetColumns = [	'TITLE'				=>	[	'column'	=>'BA_ID',
																	'model'		=>'BaAddress'],
		];
	}
	
	public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$table,$facility_id,$occur_date,$properties){
    	if($table!=PersonnelSumDay::getTableName()){
	//     	$personnel = Personnel::getTableName();
	    	$codePersonnelTitle = CodePersonnelTitle::getTableName();
	    	$extraDataSet = [];
	    	//      	\DB::enableQueryLog();
	    	$dataSet = Personnel::join($codePersonnelTitle,"$codePersonnelTitle.ID", '=', "$table.TITLE")
	    					->where("FACILITY_ID","=",$facility_id)
					    	->where("OCCUR_DATE","=",$occur_date)
					    	->select(
					    			"$table.ID as DT_RowId",
					    			"$table.*",
					    			"$codePersonnelTitle.CODE"
					    			)
	 		    			->orderBy("ID")
			    			->get();
	    	//  		\Log::info(\DB::getQueryLog());
			    			
	    	if ($dataSet&&$dataSet->count()>0) {
	    		foreach($this->extraDataSetColumns as $column => $extraDataSetColumn){
	    			$extraDataSet[$column] = $this->getExtraEntriesBy($column,$extraDataSetColumn,$dataSet);
	    		}
	    	}
	    	
	    	return ['dataSet'=>$dataSet,
	    			'extraDataSet'=>$extraDataSet,
	    	];
    	}
    	return null;
    }
    
    public function getSecondaryData($postData,$dcTable,$facility_id,$occur_date,$results){
    	$personnelSumDay = PersonnelSumDay::getTableName();
    	$personnelSumDayProperties = $this->getOriginProperties($personnelSumDay);
    	$secondaryEntries = $this->getSecondaryDataSet($postData,$personnelSumDay,$facility_id,$occur_date,$personnelSumDayProperties);
    	$uoms = $this->getUoms($personnelSumDayProperties,$facility_id,$personnelSumDay);
    	
    	$secondaryData = [	'dataSet'		=>$secondaryEntries,
			    			'properties'	=>$personnelSumDayProperties,
			    			'uoms'			=>$uoms,
			    			'postData'		=>['tabTable'=>'PersonnelSumDay'],
    	];
    	return $secondaryData;
    }
    
    public function getSecondaryDataSet($postData,$table,$facility_id,$occur_date,$properties){
    	
    	$personnel = Personnel::getTableName();
    	$codePersonnelTitle = CodePersonnelTitle::getTableName();
    	$codePersonnelType = CodePersonnelType::getTableName();
    	$personnelSumDay = PersonnelSumDay::getTableName();
//     	\DB::enableQueryLog();
    	$query = Personnel::where("$personnel.FACILITY_ID", '=', $facility_id)
    						->where("$personnel.OCCUR_DATE", '=', $occur_date)
    						->whereRaw(\DB::raw("$personnel.TYPE = $codePersonnelType.ID"))
    						->whereRaw(\DB::raw("$personnel.TITLE = $codePersonnelTitle.ID"))
    						->select(\DB::raw('count(*)'));
    	
    	$entryCount = $query->toSql();
    	$binding = $query->getBindings();
    	 
    	$dataSet = CodePersonnelTitle::join($codePersonnelType,"$codePersonnelType.ID",'=',"$codePersonnelType.ID")
							    	->select(
							    			\DB::raw("CONCAT($codePersonnelType.ID,$codePersonnelTitle.ID) as DT_RowId"),
							    			"$codePersonnelType.ID as TYPE",
							    			"$codePersonnelTitle.ID as TITLE",
							    			"$codePersonnelType.NAME as TYPE_NAME",
							    			"$codePersonnelTitle.NAME as TITLE_NAME",
							    			"$personnelSumDay.NOTE",
							    			\DB::raw("($entryCount) as NUMBER")
							    			)
					    			->addBinding($binding)
					    			->leftJoin($personnelSumDay, function($join) use ($personnelSumDay,$codePersonnelType,$codePersonnelTitle,$facility_id,$occur_date){
					    				$join->on("$personnelSumDay.TYPE", '=', "$codePersonnelType.ID");
					    				$join->on("$personnelSumDay.TITLE", '=', "$codePersonnelTitle.ID");
					    				$join->where('FACILITY_ID','=',$facility_id);
					    				$join->where('OCCUR_DATE','=',$occur_date);
					    			})
					    			->orderBy("$codePersonnelType.ID")
					    			->orderBy("$codePersonnelTitle.ID")
					    			->get();
// 		\Log::info(\DB::getQueryLog());
					    			
    	return $dataSet;
    }
    

	public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde = null){
    	$data = null;
    	switch ($sourceColumn) {
    		case 'TITLE':
		    	$targetModel = $extraDataSetColumn['model'];
		    	$targetEloquent = "App\Models\\$targetModel";
		    	$codePersonnelTitle = CodePersonnelTitle::getTableName();
		    	$codeBaType = CodeBaType::getTableName();
		    	$targetTable = $targetEloquent::getTableName();
		    	$data = $targetEloquent::join($codeBaType,"$targetTable.SOURCE",'=',"$codeBaType.ID")
							    		->join($codePersonnelTitle,function ($query) use ($codePersonnelTitle,$codeBaType,$sourceColumnValue) {
											    			$query->on("$codePersonnelTitle.CODE",'=',"$codeBaType.CODE")
											    					->where("$codePersonnelTitle.ID",'=',$sourceColumnValue) ;
							    		})
							    		->select(
									    			"$targetTable.*",
		 							    			"$targetTable.ID as value",
									    			"$targetTable.NAME as text"
									    			)->get();
    			break;
    	}
    	return $data;
    }
    
    public function loadsrc(Request $request){
    	//     	sleep(2);
    	$postData = $request->all();
    	$sourceColumn = $postData['name'];
    	$sourceColumnValue = $postData['value'];
    	$dataSet = [];
    		
    	if (array_key_exists($sourceColumn, $this->extraDataSetColumns)) {
		   	$extraDataSetColumn = $this->extraDataSetColumns[$sourceColumn];
		   	$targetColumn = $extraDataSetColumn['column'];
		   	$data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,null);
		   	$dataSet[$targetColumn] = [	'data'			=>	$data,
		   			'ofId'			=>	$sourceColumnValue,
		   			'sourceColumn'	=>	$sourceColumn
		   	];
    	}
    	 
    	return response()->json(['dataSet'=>$dataSet,
    							'postData'=>$postData]);
    }
}
