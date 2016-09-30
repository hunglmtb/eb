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
use Carbon\Carbon;

class DemurrageeboController extends CodeController {
    
	public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
		 
		$mdlName 					= $postData[config("constants.tabTable")];
		$mdl 						= "App\Models\\$mdlName";
		$date_end 					= $postData['date_end'];
		$date_end 					= \Helper::parseDate($date_end);
		$storage_id 				= $postData['Storage'];
		
		$pdCargoLoad 				= PdCargoLoad::getTableName();
		$terminalTimesheetData 		= TerminalTimesheetData::getTableName();
		$pdCodeLoadActivity 		= PdCodeLoadActivity::getTableName();
		$pdBerth 					= PdBerth::getTableName();
		$pdCargo 					= PdCargo::getTableName();
		$pdCodeDemurrageEbo 		= PdCodeDemurrageEbo::getTableName();
		$pdCargoUnload				= PdCargoUnload::getTableName();
		$demurrage					= Demurrage::getTableName();
		$pdContractData				= PdContractData::getTableName();
		
		$result 					= array();
		$aryMst 					= array();
		
		$lquery = PdCargoLoad::join($terminalTimesheetData,
										function ($query) use ($terminalTimesheetData,$pdCargoLoad) {
											$query->on("$pdCargoLoad.ID",'=',"$terminalTimesheetData.PARENT_ID")
												->where("$terminalTimesheetData.IS_LOAD",'=',1) ;
									})
								->join($pdCargo,
										function ($query) use ($pdCargo,$pdCargoLoad,$storage_id) {
											$query->on("$pdCargo.ID",'=',"$pdCargoLoad.CARGO_ID")
											->where("$pdCargo.STORAGE_ID",'=',$storage_id) ;
										})
								->leftJoin($pdCodeLoadActivity,"$terminalTimesheetData.ACTIVITY_ID", '=', "$pdCodeLoadActivity.ID")
								->leftJoin($pdBerth,"$pdCargoLoad.BERTH_ID", '=', "$pdBerth.ID")
								->leftJoin($pdCodeDemurrageEbo,"$pdCargoLoad.DEMURRAGE_EBO", '=', "$pdCodeDemurrageEbo.ID")
								->select(
									"$pdCargoLoad.CARGO_ID",           
									"$pdCargoLoad.BERTH_ID",           
									"$pdCargoLoad.ID as ID",           
									"$pdCargoLoad.DATE_LOAD",           
									"$pdCargoLoad.DEMURRAGE_EBO",
									"$terminalTimesheetData.ID as TE_ID", 
									"$terminalTimesheetData.IS_LOAD", 
									"$terminalTimesheetData.START_TIME", 
									"$terminalTimesheetData.END_TIME", 
									"$terminalTimesheetData.ACTIVITY_ID",
									"$pdCodeLoadActivity.NAME as ACTIVITY_NAME",
									"$pdBerth.NAME as BERTH_NAME",
									"$pdCargo.NAME as CARGO_NAME",
									"$pdCargo.CONTRACT_ID",
									"$pdCodeDemurrageEbo.NAME as DEMURRAGE_EBO_NAME"
									)
								->with(["Demurrage"		=>	function ($query) use ($terminalTimesheetData,$demurrage) {
															$query->where("$demurrage.ACTIVITY_ID",'=',"$terminalTimesheetData.ACTIVITY_ID") ;
								}])
								->with(["PdContractData"=>	function ($query) use ($pdContractData,$pdCargo) {
															$query->where("$pdContractData.CONTRACT_ID",'=',"$pdCargo.CONTRACT_ID") ;
								}]);
		$dataSet = $lquery->get();
		
		/* 
		$sSQL="SELECT 
				a.CARGO_ID,
				a.BERTH_ID,
				a.ID as ID,
				a.DATE_LOAD, 
				a.DEMURRAGE_EBO,
				b.ID as TE_ID, 
				b.IS_LOAD, 
				b.START_TIME,
				b.END_TIME,
				b.ACTIVITY_ID,
				c.NAME as ACTIVITY_NAME,
				d.NAME as BERTH_NAME,
				e.NAME as CARGO_NAME ,
				e.CONTRACT_ID 
				f.NAME as DEMURRAGE_EBO_NAME,
				
				FROM PD_CARGO_LOAD a 
				INNER JOIN TERMINAL_TIMESHEET_DATA b 
				ON ( a.ID = b.PARENT_ID AND b.IS_LOAD = 1 )
				LEFT JOIN pd_code_load_activity c ON b.ACTIVITY_ID = c.ID
				LEFT JOIN pd_berth d ON a.BERTH_ID = d.ID
				JOIN pd_cargo e ON a.CARGO_ID = e.ID and e.STORAGE_ID=$storage_id
				LEFT JOIN pd_code_demurrage_ebo f ON a.DEMURRAGE_EBO = f.ID ";
		
		$sSQL .= " UNION ALL ";
		$sSQL .="SELECT a1.CARGO_ID,a1.BERTH_ID,a1.ID  as ID, a1.DATE_UNLOAD, b1.ID as TE_ID, b1.IS_LOAD, b1.START_TIME, b1.END_TIME,c1.NAME as ACTIVITY_NAME,b1.ACTIVITY_ID,d1.NAME as BERTH_NAME,e1.NAME as CARGO_NAME ,a1.DEMURRAGE_EBO,f1.NAME as DEMURRAGE_EBO_NAME,e1.CONTRACT_ID "
				. " FROM PD_CARGO_UNLOAD a1 INNER JOIN TERMINAL_TIMESHEET_DATA b1 ON ( a1.ID = b1.PARENT_ID AND ( b1.IS_LOAD = 0 OR b1.IS_LOAD IS NULL ) ) "
						. " LEFT JOIN pd_code_load_activity c1 ON b1.ACTIVITY_ID = c1.ID"
								. " LEFT JOIN pd_berth d1 ON a1.BERTH_ID = d1.ID"
										. " JOIN pd_cargo e1 ON a1.CARGO_ID = e1.ID and e1.STORAGE_ID=$storage_id"
										. " LEFT JOIN pd_code_demurrage_ebo f1 ON a1.DEMURRAGE_EBO = f1.ID ";
										$sSQL .= "  ORDER BY START_TIME DESC"; */
		/* 
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
		->join($terminalTimesheetData.' AS b', function ($j) {
			$j->on('a.ID', '=', 'b.PARENT_ID')
			->where('b.IS_LOAD','=', '1');
		})
		->leftJoin($pdCodeLoadActivity.' AS c', 'b.ACTIVITY_ID', '=', 'c.ID')
		->leftJoin($pdBerth.' AS d', 'a.BERTH_ID', '=', 'd.ID')
		->leftJoin($pd_cargo.' AS e', 'a.CARGO_ID', '=', 'e.ID')
		->leftJoin($pdCodeDemurrageEbo.' AS f', 'a.DEMURRAGE_EBO', '=', 'f.ID')
		->select(
			$listColumn
		);
		//->get (); 
		
		$tmp2 = \DB::table($ppdCargoUnload AS a')
		->join($terminalTimesheetData.' AS b', function ($j) {
			$j->on('a.ID', '=', 'b.PARENT_ID')
			->where('b.IS_LOAD','=', '0')
			->whereNull('IS_LOAD');
		})
		->leftJoin($pdCodeLoadActivity.' AS c', 'b.ACTIVITY_ID', '=', 'c.ID')
		->leftJoin($pdBerth.' AS d', 'a.BERTH_ID', '=', 'd.ID')
		->leftJoin($pd_cargo.' AS e', 'a.CARGO_ID', '=', 'e.ID')
		->leftJoin($pdCodeDemurrageEbo.' AS f', 'a.DEMURRAGE_EBO', '=', 'f.ID')
		->union($tmp1)
		->select(
			$listColumn1
		)
		->get (); */
		
		/* $pd_code_contract_attribute = PdCodeContractAttribute::where(['CODE'=>'DEMUR_RATE_HOUR'])
									->orWhere(['CODE'=>'EBO_RATE_HOUR'])
									->get(['ID', 'CODE']);
		
		foreach ($pd_code_contract_attribute as $p){
			$key = ($p->CODE == 'EBO_RATE_HOUR') ? 1 : 2;
			$aryMst[$key] = $p->ID;
		} */
		
		/* foreach ( $tmp2 as $t ) {
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
		}  */
		
		
		foreach($dataSet as $key => $t ){
 			$rate 				= 0;
			$elapse_time 		= -1;
			if ($t->END_TIME&&$t->START_TIME) {
				$endTime 		= Carbon::parse($t->END_TIME);
				$startTime 		= Carbon::parse($t->START_TIME);
				$elapse_time 	= $endTime->diffInHours($startTime); 
			}
			$elapse_time 		= ($elapse_time < 0) ? null : $elapse_time;
			$t->ELAPSE_TIME 	= $elapse_time;
			$demurrages			= $t->Demurrage;
 			$t->OVERRIDE_AMOUNT	= $demurrages&&$demurrages->count()>0?$demurrages->first()->OVERRIDE_AMT:"";
 			$pdContractDatas	= $t->PdContractData;
 			$rate				= $pdContractDatas&&$pdContractDatas->count()>0?$pdContractDatas->first()->ATTRIBUTE_VALUE:0;
			$amount 			= $elapse_time * $rate;
			$t->AMOUNT 			= $amount==0?"0.000":$amount;
			$t->RATE_HOUR 		= ($rate == 0)?null:$rate;
		}
		
		return ['dataSet'=>$dataSet];
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
