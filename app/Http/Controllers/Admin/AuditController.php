<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CodeController;
use App\Models\AuditTrail;
use App\Models\CodeAuditReason;	
use App\Models\IntObjectType;	

class AuditController extends CodeController {
    
	
	public function getFirstProperty($dcTable){
		return  null;
	}
	/* public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		$properties = collect([
 				(object)['data' =>	'ACTION',		'title' => 'Action',	'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>1],
				(object)['data' =>	"WHO",			'title' => 'By',		'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>2],
				(object)['data' =>	"WHEN",			'title' => 'Time',		'width'	=>	0,'INPUT_TYPE'=>4,	'DATA_METHOD'=>5,'FIELD_ORDER'=>3],
				(object)['data' =>	"REASON",		'title' => 'Reason',	'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>4],
				(object)['data' =>	"OBJECT_DESC",	'title' => 'Object',	'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>5],
				(object)['data' =>	"RECORD_ID",	'title' => 'Record ID',	'width'	=>	0,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>6],
				(object)['data' =>	"TABLE_NAME",	'title' => 'Table',		'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>7],
				(object)['data' =>	"COLUMN_NAME",	'title' => 'Column',	'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>8],
				(object)['data' =>	"OLD_VALUE",	'title' => 'Old Value',	'width'	=>	0,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>9],
				(object)['data' =>	"NEW_VALUE",	'title' => 'New Value',	'width'	=>	0,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>10],
				(object)['data' =>	"AUDIT_NOTE",	'title' => 'MEMO',		'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>11],
		]);
		$results 	= ['properties'		=> $properties,
	    				'locked'		=> true,
		];
		return $results;
	} */
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$date_end 			= $postData['date_end'];
    	$date_end			= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	$auditTrail 		= AuditTrail::getTableName();
    	$codeAuditReason 	= CodeAuditReason::getTableName();
    	$beginDate 			= $occur_date;
    	$tableName 			= $postData['ObjectDataSource'];
    	 
    	if($postData['IntObjectType'] >0){
    		$objectName = IntObjectType::where("ID",$postData['IntObjectType'])->select("CODE")->first();
    		$objectName = $objectName?$objectName->CODE:"";
    		$objectType = strtoupper(str_replace(' ','_',$objectName)).'_%';
    	}else{
    		$objectType = '%';
    	}
    			
    	// 		\DB::enableQueryLog();
    	$dataSet = AuditTrail::join($codeAuditReason, "$auditTrail.REASON", '=', "$codeAuditReason.ID")
						    	->where(["$auditTrail.FACILITY_ID" => $facility_id])
//  						    	->where('TABLE_NAME', 'like', $objectType)
 						    	->where('TABLE_NAME', '=', $tableName)
 						    	->whereDate("$auditTrail.WHEN", '>=', $occur_date)
						    	->whereDate("$auditTrail.WHEN", '<=', $date_end)
						    	->select(['ACTION',
						    			'WHO', 
						    			'WHEN', 
						    			'TABLE_NAME', 
						    			'COLUMN_NAME', 
						    			'RECORD_ID',
						    			'OBJECT_DESC',
						    			'OLD_VALUE',
						    			'NEW_VALUE', 
						    			'AUDIT_NOTE', 
						    			'OCCUR_DATE', 
						    			"$codeAuditReason.NAME AS REASON"])
						    	->get();
    	// 		\Log::info(\DB::getQueryLog());
    	return ['dataSet'=>$dataSet];
    }
}
