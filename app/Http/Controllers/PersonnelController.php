<?php

namespace App\Http\Controllers;
use App\Models\Personnel;
use App\Models\CodePersonnelTitle;
use App\Models\CodeBaType;
use Illuminate\Http\Request;

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
	
    public function getDataSet($postData,$safetyTable,$facility_id,$occur_date,$properties){

    	$personnel = Personnel::getTableName();
    	$codePersonnelTitle = CodePersonnelTitle::getTableName();
    	$extraDataSet = [];
    	//      	\DB::enableQueryLog();
    	$dataSet = Personnel::join($codePersonnelTitle,"$codePersonnelTitle.ID", '=', "$personnel.TITLE")
    					->where("FACILITY_ID","=",$facility_id)
				    	->where("OCCUR_DATE","=",$occur_date)
				    	->select(
				    			"$personnel.ID as DT_RowId",
				    			"$personnel.*",
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
    	
    	/* $sSQL="select concat(a.`TYPE`,'_',a.TITLE) ID, 
    	a.*,s.NOTE,
    	(select count(1) 
	    	from PERSONNEL x 
	    	where x.`TYPE`=a.`TYPE` 
	    	and x.TITLE=a.TITLE 
	    	and x.FACILITY_ID=$facility_id 
	    	and x.OCCUR_DATE=STR_TO_DATE('$occur_date','%m/%d/%Y')
    	) X_NUMBER,
    	b.NAME TYPE_NAME,
    	c.NAME TITLE_NAME 
    	from (select t1.ID `TITLE`,
    			t2.ID `TYPE` 
    			from code_personnel_title t1, 
    			code_personnel_type t2
    	) a 
    	left join PERSONNEL_SUM_DAY s 
    	on s.TYPE=a.TYPE 
    	and s.TITLE=a.TITLE 
    	and s.FACILITY_ID=$facility_id 
    	and s.OCCUR_DATE=STR_TO_DATE('$occur_date','%m/%d/%Y'), 
    	CODE_PERSONNEL_TYPE b, 
    	CODE_PERSONNEL_TITLE c 
    	where a.TYPE=b.ID 
    	and a.TITLE=c.ID 
    	order by a.`TYPE`, 
    	a.`TITLE`"; */
    	 
    	return ['dataSet'=>$dataSet,
    			'extraDataSet'=>$extraDataSet
    	];
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
