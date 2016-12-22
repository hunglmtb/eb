<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdCargo;
use App\Models\PdVoyage;
use App\Models\PdVoyageDetail;
use App\Models\PdDocumentSetData;
use App\Models\PdReportList;
use App\Models\PdDocumentSetList;
use Illuminate\Http\Request;

class CargoDocumentsController extends CodeController {

	public function __construct() {
		parent::__construct();
		$this->detailModel = "PdDocumentSetData";
	}
	
	public function getFirstProperty($dcTable){
		return  null;
	}
	
	public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		if ($dcTable==PdDocumentSetData::getTableName()) {
			$properties = collect([
					(object)['data' =>	'ID',		'title' => 'Action',		'width'	=>	50,	'INPUT_TYPE'=>3,	'DATA_METHOD'=>2,'FIELD_ORDER'=>1],
					(object)['data' =>	"NAME"	,	'title' => 'Report Name',	'width'	=>	150,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>2],
			]);
			$uoms		= [];
			$uoms[]		= \App\Models\PdCodeOrginality::all();
			$uoms[]		= \App\Models\PdCodeNumber::all();
				
			$selects 	= ['BaAddress'		=> \App\Models\BaAddress::all()];
			$results 	= ['properties'		=> $properties,
							'selects'		=> $selects,
 	  						'suoms'			=> $uoms,
			];
			return $results;
		}
		return parent::getProperties($dcTable,$facility_id,$occur_date,$postData);
	}
	
	public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
		$date_end 			= array_key_exists('date_end',  $postData)?$postData['date_end']:null;
    	$date_end			= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
		$storageId			= $postData['Storage'];
		$pd_voyage 			= PdVoyage::getTableName();
		$pd_cargo 			= PdCargo::getTableName();
		$pd_voyage_detail 	= PdVoyageDetail::getTableName();
		
		$column = array();
		$ObjColumn = $properties['properties'];
		foreach ($ObjColumn as $p){
			array_push($column, "$pd_voyage.$p->data");
		}
		array_push($column, "$pd_voyage_detail.ID AS DT_RowId");
		array_push($column, "$pd_voyage.ID AS VOYAGE_ID");
		array_push($column, "$pd_voyage_detail.PARCEL_NO as MASTER_NAME");
		
		$dataSet = 	PdVoyage::join($pd_cargo, "$pd_voyage.CARGO_ID",		 '=', "$pd_cargo.ID")
					->join($pd_voyage_detail, "$pd_voyage_detail.VOYAGE_ID", '=', "$pd_voyage.ID")
					->where(["$pd_cargo.STORAGE_ID"	=> $storageId])
					->whereDate('SCHEDULE_DATE', '>=', $occur_date)
					->whereDate('SCHEDULE_DATE', '<=', $date_end)
					->orderBy("DT_RowId")
					->get($column);
		
		return ['dataSet'=>$dataSet];
	}
	
	public function getDetailData($id,$postData,$properties){
		$voyageId			= $postData['voyageId'];
		$cargoId			= $postData['cargoId'];
		$parcelNo			= $postData['parcelNo'];
		$lifftingAcount		= $postData['lifftingAcount'];
		
		$pdDocumentSetData	= PdDocumentSetData::getTableName();
		$pdReportList		= PdReportList::getTableName();
		$dataSet 			= PdDocumentSetData::with("PdDocumentSetContactData")
								->join($pdReportList,
									"$pdDocumentSetData.DOCUMENT_ID",
									'=',
									"$pdReportList.ID")
								->where("$pdDocumentSetData.VOYAGE_ID",'=',$voyageId)
								->where("$pdDocumentSetData.CARGO_ID",'=',$cargoId)
								->where("$pdDocumentSetData.PARCEL_NO",'=',$parcelNo)
								->select(
										"$pdDocumentSetData.ID as DT_RowId",
										"$pdDocumentSetData.ID",
										"$pdDocumentSetData.DOCUMENT_ID",
										"$pdDocumentSetData.CARGO_ID",
										"$pdReportList.NAME as NAME",
										"$pdReportList.CODE as CODE"
// 										"$pdReportList.ID as ACTIVITY_ID"
										)
								->get();
		return $dataSet;
	}
	
	public function activities(Request $request){
		$postData 					= $request->all();
		$set_id						= $postData['id'];
		
		$pdDocumentSetList 	= PdDocumentSetList::getTableName();
		$pdReportList 		= PdReportList::getTableName();
		
		$dataSet 					= PdDocumentSetList::join($pdReportList,
											"$pdDocumentSetList.DOCUMENT_ID",
											'=',
											"$pdReportList.ID")
										->where("SET_ID",'=',$set_id)
 										->select("$pdReportList.ID as ID","$pdReportList.CODE as CODE","$pdReportList.NAME")
										->get();
		$results = ['updatedData'	=>[$this->detailModel	=> $dataSet],
					'postData'		=>$postData];
		return response()->json($results);
	}
}
