<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdVoyage;
use App\Models\PdCargo;
use App\Models\PdVoyageDetail;

use Carbon\Carbon;

class CargoDocumentsController extends CodeController {
    
	public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
		
		$pd_voyage = PdVoyage::getTableName();
		$pd_cargo = PdCargo::getTableName();
		$pd_voyage_detail = PdVoyageDetail::getTableName();
		$date_begin = Carbon::createFromFormat('d/m/Y',$postData ['date_begin'])->format('Y-m-d');
		$date_end = Carbon::createFromFormat('d/m/Y',$postData ['date_end'])->format('Y-m-d');
		
		$column = array();
		$ObjColumn = $properties['properties'];
		foreach ($ObjColumn as $p){
			array_push($column, 'a.'.$p->data);
		}
		array_push($column, 'a.ID AS DT_RowId');
		array_push($column, 'c.PARCEL_NO');
		array_push($column, 'a.ID AS ID');
		
		\DB::enableQueryLog ();
		$tmp = \DB::table($pd_voyage.' AS a')
		->Join($pd_cargo.' AS b', 'a.CARGO_ID', '=', 'b.ID')
		->Join($pd_voyage_detail.' AS c', 'c.VOYAGE_ID', '=', 'a.ID')
		->where(['b.STORAGE_ID'=>$postData['Storage']])
		->whereDate('SCHEDULE_DATE', '>=', $date_begin)
		->whereDate('SCHEDULE_DATE', '<=', $date_end)
		->get($column);
		\Log::info ( \DB::getQueryLog () );
		return ['dataSet'=>$tmp];
	}
	
	public function getFirstProperty($dcTable){
		return  null;
	}
}
