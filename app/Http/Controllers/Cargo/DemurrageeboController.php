<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdCargoLoad;
use App\Models\TerminalTimesheetData;
use App\Models\PdCodeLoadActivity;
use App\Models\PdBerth;
use App\Models\PdCargo;
use App\Models\PdCodeDemurrageEbo;
use App\Models\PdCargoUnload;
use App\Models\PdContractData;
use App\Models\PdCodeContractAttribute;
use App\Models\Demurrage;

use Illuminate\Http\Request;

class DemurrageeboController extends CodeController {
    
	public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
		 
		$mdlName = $postData[config("constants.tabTable")];
		$mdl = "App\Models\\$mdlName";
		$date_end = $postData['date_end'];
		$date_end = \Helper::parseDate($date_end);
		$result = array();
		$aryMst = array();
		$pd_cargo_load = PdCargoLoad::getTableName();
		$terminal_timesheet_data = TerminalTimesheetData::getTableName();
		$pd_code_load_activity = PdCodeLoadActivity::getTableName();
		$pd_berth = PdBerth::getTableName();
		$pd_cargo = PdCargo::getTableName();
		$pd_code_demurrage_ebo = PdCodeDemurrageEbo::getTableName();
		$pd_cargo_unload = PdCargoUnload::getTableName();
		
		
		$listColumn = [
				'a.CARGO_ID', 'a.BERTH_ID', 'a.ID as ID', 'b.ID as TE_ID', 'b.IS_LOAD', 'a.ID as DT_RowId',
				'b.START_TIME', 'b.END_TIME', 'c.NAME as ACTIVITY_NAME','b.ACTIVITY_ID', 
				'd.NAME as BERTH_NAME', 'e.NAME as CARGO_NAME', 'a.DEMURRAGE_EBO', 'f.NAME as DEMURRAGE_EBO_NAME', 
				'e.CONTRACT_ID','a.DATE_LOAD', 'TIME_LAPSE AS ELAPSE_TIME', 'a.BERTH_ID AS OVERRIDE_AMOUNT', 'a.BERTH_ID AS RATE_HOUR'
		];
		
		$listColumn1 = [
				'a.CARGO_ID', 'a.BERTH_ID', 'a.ID as ID', 'b.ID as TE_ID', 'b.IS_LOAD', 'a.ID as DT_RowId',
				'b.START_TIME', 'b.END_TIME', 'c.NAME as ACTIVITY_NAME','b.ACTIVITY_ID', 
				'd.NAME as BERTH_NAME', 'e.NAME as CARGO_NAME', 'a.DEMURRAGE_EBO', 'f.NAME as DEMURRAGE_EBO_NAME', 
				'e.CONTRACT_ID','a.DATE_UNLOAD AS DATE_LOAD', 'TIME_LAPSE AS ELAPSE_TIME', 'a.BERTH_ID AS OVERRIDE_AMOUNT', 'a.BERTH_ID AS RATE_HOUR'
		];

		$tmp1 = \DB::table($pd_cargo_load.' AS a')
		->join($terminal_timesheet_data.' AS b', function ($j) {
			$j->on('a.ID', '=', 'b.PARENT_ID')
			->where('b.IS_LOAD','=', '1');
		})
		->leftJoin($pd_code_load_activity.' AS c', 'b.ACTIVITY_ID', '=', 'c.ID')
		->leftJoin($pd_berth.' AS d', 'a.BERTH_ID', '=', 'd.ID')
		->leftJoin($pd_cargo.' AS e', 'a.CARGO_ID', '=', 'e.ID')
		->leftJoin($pd_code_demurrage_ebo.' AS f', 'a.DEMURRAGE_EBO', '=', 'f.ID')
		->select(
			$listColumn
		);
		//->get (); 
		
		$tmp2 = \DB::table($pd_cargo_unload.' AS a')
		->join($terminal_timesheet_data.' AS b', function ($j) {
			$j->on('a.ID', '=', 'b.PARENT_ID')
			->where('b.IS_LOAD','=', '0')
			->whereNull('IS_LOAD');
		})
		->leftJoin($pd_code_load_activity.' AS c', 'b.ACTIVITY_ID', '=', 'c.ID')
		->leftJoin($pd_berth.' AS d', 'a.BERTH_ID', '=', 'd.ID')
		->leftJoin($pd_cargo.' AS e', 'a.CARGO_ID', '=', 'e.ID')
		->leftJoin($pd_code_demurrage_ebo.' AS f', 'a.DEMURRAGE_EBO', '=', 'f.ID')
		->union($tmp1)
		->select(
			$listColumn1
		)
		->get ();
		
		/* $pd_code_contract_attribute = PdCodeContractAttribute::where(['CODE'=>'DEMUR_RATE_HOUR'])
									->orWhere(['CODE'=>'EBO_RATE_HOUR'])
									->get(['ID', 'CODE']);
		
		foreach ($pd_code_contract_attribute as $p){
			$key = ($p->CODE == 'EBO_RATE_HOUR') ? 1 : 2;
			$aryMst[$key] = $p->ID;
		} */
		
		foreach ( $tmp2 as $t ) {
			$rate = 0;
			$elapse_time = strtotime ( $t->END_TIME ) - strtotime ( $t->START_TIME );
			$elapse_time = ($elapse_time < 0) ? null : floor ( $elapse_time / 3600 );
			
			$t->ELAPSE_TIME = $elapse_time;
			
			$demurrage = Demurrage::where(['CARGO_ID'=>$t->CARGO_ID])->select('ACTIVITY_ID', 'OVERRIDE_AMT')->first();
			
			$t->OVERRIDE_AMT = $demurrage['OVERRIDE_AMT'];
			
			if($t->DEMURRAGE_EBO)
			{				
				$pd_contract_data = PdContractData::where(['ATTRIBUTE_ID'=>$t->DEMURRAGE_EBO, 'CONTRACT_ID'=>$t->CONTRACT_ID])->select('ATTRIBUTE_VALUE')->first();
				$rate = $pd_contract_data['ATTRIBUTE_VALUE'];
			}
			
			$amount = $elapse_time * $rate;
			
			$t->AMOUNT = ($amount == 0)?null:$amount;
			
			$t->RATE_HOUR = ($rate == 0)?null:$rate;
			
			array_push($result, $t);
		}
		
		return ['dataSet'=>$result];
	}
	
	public function getFirstProperty($dcTable){
		return  null;
	}
	
 	public function saveDemurrage(Request $request){
 		$postData = $request->all();
 		$datas = $postData['data'];
 		
 		$insert = array();
 		foreach ($datas as $data){
 			$tmp = array();
 			
 			$tmp['CARGO_ID'] = $data['CARGO_ID'];
 			$tmp['BERTH_ID'] = $data['BERTH_ID'];
 			$tmp['ACTIVITY_ID'] = $data['ACTIVITY_ID'];
 			$tmp['DEMURRAGE_TYPE'] = $data['DEMURRAGE_EBO'];
 			$tmp['START_TIME'] = $data['START_TIME'];
 			$tmp['END_TIME'] = $data['END_TIME'];
 			$tmp['DEMURRAGE_RATE'] = $data['RATE_HOUR'];
 			$tmp['AMOUNT'] = $data['AMOUNT'];
 			$tmp['OVERRIDE_AMT'] = $data['OVERRIDE_AMT'];
 			
 			array_push($insert, $tmp);
 		}
 		
 		Demurrage::insert($insert);
 		
		return response ()->json ( 'ok' );
	}
	
	public function loadsrc(Request $request){
		$postData = $request->all();
		
		$dataSet = array();
		$dataSet['START_TIME'] ='2016-01-01 12:12';
		$dataSet['END_TIME'] = '2016-01-02 12:12';
		$dataSet['ELAPSE_TIME'] = 1234;
		 
		return response()->json(['dataSet'=>$dataSet,
				'postData'=>$postData]);
	}
}
