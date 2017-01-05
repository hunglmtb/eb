<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\Flow;
use App\Models\FlowDataValue;
use App\Models\PdCargo;
use App\Models\PdCargoNomination;
use App\Models\PdLiftingAccount;
use App\Models\ShipCargoBlmr;
use App\Models\StorageDataValue;
use App\Models\PdLiftingAccountMthData;
use Carbon\Carbon;

class LiftDailyController extends CodeController {
    
	/* public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=> 50];
	} */
	public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		$properties = collect([
 				(object)['data' =>	'UOM',			'title' => 'Month',			'width'	=>	70,'INPUT_TYPE'=>2,		'DATA_METHOD'=>5,'FIELD_ORDER'=>1],
				(object)['data' =>	"cargo_name",	'title' => 'Cargo',			'width'	=>	130,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>2],
				(object)['data' =>	"xdate",		'title' => 'Date',			'width'	=>	60,'INPUT_TYPE'=>3,	'DATA_METHOD'=>5,'FIELD_ORDER'=>3],
				(object)['data' =>	"opening_balance",'title' => 'Opening Balance','width'=>110,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>3],
				(object)['data' =>	"n_qty",		'title' => 'Nominated Qty',	'width'	=>	110,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>4],
				(object)['data' =>	"b_qty",		'title' => 'Lifted Qty',	'width'	=>	110,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>5],
				(object)['data' =>	"flow_qty",		'title' => 'Flow Qty',		'width'	=>	110,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>6],
				(object)['data' =>	"flow_name",	'title' => 'Flow Name',		'width'	=>	110,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>7],
				(object)['data' =>	"cal_qty",		'title' => 'Balance Qty',	'width'	=>	110,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>8],
		]);
		/* $uoms		= [];
		$uoms[]		= \App\Models\PdCodeOrginality::all();
		$uoms[]		= \App\Models\PdCodeNumber::all();

		$selects 	= ['BaAddress'		=> \App\Models\BaAddress::all()]; */
		
		$results 	= ['properties'		=> $properties,
// 				'selects'		=> $selects,
// 				'suoms'			=> $uoms,
		];
		return $results;
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$accountId 			= $postData['PdLiftingAccount'];
    	$date_end 			= array_key_exists('date_end',  $postData)?$postData['date_end']:null;
    	$date_end			= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	
//     	\DB::enableQueryLog();
//  		\Log::info(\DB::getQueryLog());
  		
  		$pdCargo 			= PdCargo::getTableName();
  		$pdCargoNomination 	= PdCargoNomination::getTableName();
  		$shipCargoBlmr 		= ShipCargoBlmr::getTableName();
  		$flowDataValue 		= FlowDataValue::getTableName();
  		$flow			 	= Flow::getTableName();
  		$pdLiftingAccount 	= PdLiftingAccount::getTableName();
		$storageDataValue	= StorageDataValue::getTableName();
		$storageID			= $postData["Storage"];
  		
  		$query = ShipCargoBlmr::join($pdCargo,function ($query) use ($shipCargoBlmr,$accountId,$pdCargo) {
							  			$query->on("$pdCargo.ID",'=',"$shipCargoBlmr.CARGO_ID")
							  			->where("$pdCargo.LIFTING_ACCT",'=',$accountId) ;
							  		})
							  		->whereNotNull("$shipCargoBlmr.DATE_TIME")
							  		->whereDate("$shipCargoBlmr.DATE_TIME", '>=', $occur_date)
							  		->whereDate("$shipCargoBlmr.DATE_TIME", '<=', $date_end)
							  		->select(
							  				"$shipCargoBlmr.CARGO_ID",
							  				"$pdCargo.NAME as cargo_name",
							  				"$shipCargoBlmr.DATE_TIME as xdate",
							  				\DB::raw("null as nom_qty"),
							  				"$shipCargoBlmr.ITEM_VALUE as b_qty"
							  		);
  		
  		$cquery = PdCargoNomination::join($pdCargo,function ($query) use ($pdCargoNomination,$accountId,$pdCargo) {
							  			$query->on("$pdCargo.ID",'=',"$pdCargoNomination.CARGO_ID")
							  			->where("$pdCargo.LIFTING_ACCT",'=',$accountId) ;
							  		})
							  		->whereDate("$pdCargoNomination.NOMINATION_DATE", '>=', $occur_date)
							  		->whereDate("$pdCargoNomination.NOMINATION_DATE", '<=', $date_end)
							  		->select(
							  				"$pdCargoNomination.CARGO_ID",
							  				"$pdCargo.NAME as cargo_name",
							  				"$pdCargoNomination.NOMINATION_DATE as xdate",
							  				"$pdCargoNomination.NOMINATION_QTY as nom_qty",
							  				\DB::raw("null as b_qty")
							  		);
  		$query->union($cquery);
  		$xquery = \DB::table(\DB::raw("({$query->toSql()}) as x") )
			  		->select('x.CARGO_ID',
			  				'x.cargo_name',
			  				'x.xdate',
			  				\DB::raw('sum(x.nom_qty) as n_qty'),
			  				\DB::raw('sum(x.b_qty) as b_qty')
			  				)
	  				->addBinding($query->getBindings())
	  				->groupBy('x.xdate')
	  				->groupBy('x.CARGO_ID');
  		$xxquery = \DB::table(\DB::raw("({$xquery->toSql()}) as x") )
  					->select(
			  				'x.cargo_name',
  							'x.xdate',
  							'x.n_qty',
  							'x.b_qty',
			  				\DB::raw('null as flow_name'),
			  				\DB::raw('null as flow_qty'),
			  				\DB::raw('-ifnull(x.b_qty,x.n_qty) as cal_qty')
  							)
  					->addBinding($xquery->getBindings());
  		
  					
  		$flowquery = FlowDataValue::join($flow,"$flowDataValue.FLOW_ID", '=', "$flow.ID")
							  		->join($pdLiftingAccount,function ($query) use ($pdLiftingAccount,$accountId,$flow) {
								  			$query->on("$pdLiftingAccount.PROFIT_CENTER",'=',"$flow.COST_INT_CTR_ID")
								  					->where("$pdLiftingAccount.ID",'=',$accountId) ;
							  		})
							  		->whereDate("$flowDataValue.OCCUR_DATE", '>=', $occur_date)
							  		->whereDate("$flowDataValue.OCCUR_DATE", '<=', $date_end)
							  		->select(
							  				\DB::raw("null as cargo_name"),
							  				"$flowDataValue.OCCUR_DATE as xdate",
							  				\DB::raw("null as n_qty"),
							  				\DB::raw("null as b_qty"),
							  				\DB::raw("concat($flow.name,' (',round($pdLiftingAccount.INTEREST_PCT),'%)') as flow_name"),
							  				\DB::raw("round($flowDataValue.FL_DATA_GRS_VOL*$pdLiftingAccount.INTEREST_PCT/100,3) as flow_qty"),
							  				\DB::raw("round($flowDataValue.FL_DATA_GRS_VOL*$pdLiftingAccount.INTEREST_PCT/100,3) as cal_qty")
							  				)
							  		->groupBy("$flowDataValue.OCCUR_DATE")
							  		->groupBy("$flow.ID");
  		$xxquery->union($flowquery);
  		$xxxquery = \DB::table(\DB::raw("({$xxquery->toSql()}) as x") )
/*
						->leftjoin ( $storageDataValue." as y",function ($query) use($storageID){
								  			$query->on("y.OCCUR_DATE",'=',"x.xdate")
								  			->where("y.STORAGE_ID",'=',$storageID) ;
							  		})
*/
				  		->select(
				  				"x.*",
//								"y.AVAIL_SHIPPING_VOL as opening_balance",
			  					\DB::raw("(select AVAIL_SHIPPING_VOL from $storageDataValue y where y.storage_id=".$postData["Storage"]." and y.OCCUR_DATE=x.xdate) as opening_balance"),
			  					\DB::raw('case when x.b_qty is null then x.n_qty else null end as n_qty'),
				  				\DB::raw('sum(x.flow_qty) as flow_qty'),
				  				\DB::raw('ifnull(sum(x.cal_qty),0) cal_qty'),
				  				\DB::raw("'' as UOM")
				  				)
				  		->addBinding($xxquery->getBindings())
  						->groupBy("x.xdate")
  						->groupBy("x.cargo_name")
  						->groupBy("x.flow_name");
			
  		$dataSet = $xxxquery->get();
  		
  		$month="";
  		$balance = 0;
  		foreach($dataSet as $key => $item ){
	    	$date 			= Carbon::parse($item->xdate);
  			$monthOfItem 	= $date->month;//$item->xdate;
  			if($month!=$monthOfItem){
				/*
   				$monthData = PdLiftingAccountMthData::where("LIFTING_ACCOUNT_ID",$accountId)
						   				->whereMonth('BALANCE_MONTH','=', $monthOfItem)
						   				->select("BAL_VOL")
						   				->first();
  				$balance	= $monthData!=null?$monthData->BAL_VOL:0;
				*/
  				$month		= $monthOfItem;
  			}
  			$balance+=$item->cal_qty;
  			$item->cal_qty = $balance;
  			$dataSet[$key] = $item;
  		}
  		
  		/* $dataSet = $dataSet->each(function ($item, $key) use ($accountId,&$month,&$balance){
  		}); */
  		return ['dataSet'=>$dataSet];
  		/* $sSQL="select x.xdate,
  		DATE_FORMAT(x.xdate,'%m/%d/%Y') sdate,
  		DATE_FORMAT(x.xdate,'%m/01/%Y') xmonth,
  		x.cargo_name,
  		case when x.b_qty is null then x.n_qty else null end 
  		n_qty,
  		x.b_qty b_qty,
  		x.flow_name,
  		sum(x.flow_qty) flow_qty,
  		ifnull(sum(x.cal_qty),0) cal_qty
  		from(
	  		select x.cargo_name,
	  		x.xdate,
	  		x.n_qty,
	  		x.b_qty,
	  		null flow_name,
	  		null flow_qty,
	  		-ifnull(x.b_qty,x.n_qty) cal_qty
	  		from(
	  			SELECT x.cargo_name, 
	  			x.xdate,
	  			sum(x.nom_qty) n_qty,
	  			sum(x.b_qty) b_qty
		  		from(
		  		
			  		select a.cargo_id,
			  		b.name cargo_name,
			  		a.NOMINATION_DATE xdate,
			  		a.NOMINATION_QTY nom_qty,
			  		null b_qty 
			  		from pd_cargo_nomination a,
			  		pd_cargo b
			  		where a.cargo_id=b.id 
			  		and b.LIFTING_ACCT=$accountId
			  		and a.NOMINATION_DATE between '$date_from' and '$date_to'
			  		
			  		union all
				  		select a.cargo_id,
				  		b.name cargo_name, 
				  		date(a.DATE_TIME) xdate,
				  		null nom_qty, 
				  		a.ITEM_VALUE b_qty
				  		from ship_cargo_blmr a,
				  		pd_cargo b
				  		where a.cargo_id=b.id 
				  		and b.LIFTING_ACCT=$accountId 
				  		and a.DATE_TIME is not null
				  		and date(a.DATE_TIME) 
				  		between '$date_from'
				  		and '$date_to'
		  		) x
  				group by x.xdate,x.cargo_id
  			) x
  			union all
	  		select x.cargo_name,
	  		x.xdate,
	  		null n_qty,
	  		null b_qty,
	  		x.flow_name,
	  		x.flow_qty,
	  		x.flow_qty cal_qty
	  		from(
		  		select null cargo_name,
		  		a.occur_date xdate,
		  		concat(b.name,' (',round(d.INTEREST_PCT),'%)') flow_name,
		  		round(a.FL_DAY_GRS_VOL*d.INTEREST_PCT/100,3) flow_qty
		  		from 	flow_day_value a, 
				  		flow b, 
				  		pd_lifting_account d
		  		where d.id=$accountId 
		  		and d.PROFIT_CENTER=b.COST_INT_CTR_ID 
		  		and b.id=a.FLOW_ID
		  		#and b.DISP in ('PROD_OIL','IMPORT_OIL')
		  		and a.OCCUR_DATE between '$date_from' and '$date_to'
		  		group by a.occur_date,b.id
	  		) x
  		) x
  		group by x.xdate,x.cargo_name,x.flow_name"; */
  		
    }
}
