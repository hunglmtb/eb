<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AllocatePlanController extends CodeController {
	
	public function getWorkingTable($postData){
		return null;
	}
	
	public function getObjectTable($src,$data_source){
		$table			=	$src."_DATA_".$data_source;
		$mdl 			= 	\Helper::getModelName($table);
		return $mdl;
	}
	
	public function getProperties($dcTable,$facility_id,$occur_date,$postData){
		$properties = collect([
					(object)['data' =>	'OCCUR_DATE','title' => 'Occur Date',	'width'=>80,'INPUT_TYPE'=>3,],
					(object)['data' =>	'GRS_VOL'	,'title' => 'Gross Vol'	,	'width'=>50,'INPUT_TYPE'=>2,	'DATA_METHOD'=>2,'FIELD_ORDER'=>2],
					(object)['data' =>	'GRS_MASS'	,'title' => 'Gross Mass',	'width'=>50,'INPUT_TYPE'=>2,	'DATA_METHOD'=>2,'FIELD_ORDER'=>3],
					(object)['data' =>	'GRS_ENGY'	,'title' => 'Gross Energy',	'width'=>50,'INPUT_TYPE'=>2,	'DATA_METHOD'=>2,'FIELD_ORDER'=>4],
					(object)['data' =>	'GRS_PWR'	,'title' => 'Gross Power',	'width'=>50,'INPUT_TYPE'=>2,	'DATA_METHOD'=>2,'FIELD_ORDER'=>5],
		]);
		return $properties;
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){

    	$phase_type 	= 	$postData['ExtensionPhaseType'];
    	$object_id 		= 	$postData['ObjectName'];
		$source_type 	= 	$postData['IntObjectType'];
    	$date_from		=	$occur_date;
    	$date_to 		= 	$postData['date_end'];
    	$date_to 		= 	Carbon::parse($date_to);
    	
    	if($object_id<=0)return response("Object Name $object_id not okay", 401);
    	
    	$obj_id_prefix	=	$source_type;
    	$field_prefix	=	$source_type;
    	$table			=	$source_type."_DATA_PLAN";
    	$mdl 			= 	\Helper::getModelName($table);
    	 
    	$where = [];
    	if($source_type=="ENERGY_UNIT"){
			$obj_id_prefix="EU";
			$where["FLOW_PHASE" ] = $flow_phase;
		}
		else if($source_type=="FLOW") $obj_id_prefix="FL";
		if($source_type=="FLOW"||$source_type=="ENERGY_UNIT"){
			$field_prefix=$obj_id_prefix."_DATA";
		}
		$where["$obj_id_prefix"."_ID" ] = $object_id;
		
		//     	\DB::enableQueryLog();
		$dataSet = $mdl::where($wheres)
						->whereBetween('OCCUR_DATE', [$date_from,$date_to])
						->select(
								"OCCUR_DATE",
								"$field_prefix"."_GRS_VOL as GRS_VOL",
								"$field_prefix"."_GRS_MASS as GRS_MASS",
								"$field_prefix"."_GRS_ENGY as GRS_ENGY",
								"$field_prefix"."_GRS_PWR as GRS_PWR"
								)
						->get();
				//  		\Log::info(\DB::getQueryLog());
		
		/* $s="select DATE_FORMAT(OCCUR_DATE,'%m/%d/%Y') S_DATE,
		OCCUR_DATE,".
		$field_prefix."_GRS_VOL GRS_VOL,".
		$field_prefix."_GRS_MASS GRS_MASS,".
		$field_prefix."_GRS_ENGY GRS_ENGY,".
		$field_prefix."_GRS_PWR GRS_PWR
				from $table
				where OCCUR_DATE>='$date_from'
				and OCCUR_DATE<='$date_to'
				and ".$obj_id_prefix."_ID=$object_id
				$ext_condition"; */
		
    	return ['dataSet'=>$dataSet];
    }
    
    public function run(Request $request){
    	$postData 		= 	$request->all();
		$phase_type 	= 	$postData['ExtensionPhaseType'];
		$cb_update_db	=	$postData['cb_update_db'];
    	$occur_date 	= 	$postData['date_begin'];
		$value_type 	= 	$postData['ExtensionValueType'];
    	$occur_date 	= 	Carbon::parse($occur_date);
    	$inputDataSet	=	$this->getInputDataSet($postData,$occur_date);
    	$objdata		=	$inputDataSet['data'];
    	$objinfo		=	$inputDataSet['info'];
    	
		$mkey			=	"";
// 		$mkey			=	"_".date("Ymdhis_").rand(100,1000)/* ."hung_test" */;
		$preos = "";
		$files = [
				'gas'				=>	"$preos"."prvap.exe",
				'oil'				=>	"$preos"."prliq.exe",
				'data'				=>	"$preos"."data$mkey.txt",
				'm_ij'				=>	"$preos"."m_ij$mkey.txt",
				'prop'				=>	"$preos"."prop$mkey.txt",
				'error'				=>	"$preos"."error$mkey.txt",
				'PR_single_V'		=>	"$preos"."PR_single_V$mkey.csv",
				'PR_single_L'		=>	"$preos"."PR_single_L$mkey.csv",
		];
		
		$cc=count($objdata);
		if($cc<=0) return response('empty input data', 401);//['error'=>"empty input data"];
		
		$ele = array_values($objdata)[0];
		$data="";
		$inputData = [];
		foreach ($ele as $key => $value)
		{
			$ss=[];
			foreach($objdata as $source =>$objValue){
				if($objValue[$key]!==""){
					$ss[]=$objValue[$key];
					$inputData[] = $objValue[$key]." <- [$source][$key]";
				}
			}
			
			if(count($ss)>0) $data.=($data?"\r\n":"").implode(",", $ss);
		}
		
		file_put_contents($files['data'],$data);
		
		$error = [];
		$results = [];
		$sqls = [];
		//Gas
		$exe=$phase_type==2?$files['gas']:$files['oil'];
		if(!file_exists($exe)) $error[] = "Exec $exe file not found";
		else{
			if(file_exists($files['data']) /* && file_exists($files['m_ij']) && file_exists($files['prop']) */){
				set_time_limit(300);
				exec("$exe $mkey");
				if(file_exists($files['error'])) $error[] = file_get_contents($files['error'], true);
		
				if(file_exists($files['PR_single_V'])) {
					$fileName = $files['PR_single_V'];
					$file = fopen($fileName,"r");
					$lastline="";
					$result = [];
					while(! feof($file))
					{
						$line=fgets($file);
						$result[] =$line;
						if($line)
						{
							if($line) $lastline=$line;
						}
					}
					$results[$fileName] = $result;
					fclose($file);
					
					$fileName = $files['PR_single_L'];
					$file = fopen($fileName,"r");
					$lastline="";
					$result = [];
					while(! feof($file))
					{
						$line=fgets($file);
						$result[] =$line;
						if($line)
						{
							if($line) $lastline=$line;
						}
					}
					$results[$fileName] = $result;
					fclose($file);
					
					if($lastline && $cb_update_db=='true')
					{
						$xs=explode(",",$lastline);
						$i=0;
						foreach($xs as $svol){
							if ($i<count($objinfo)) {
								$src= $objinfo[$i]["src"];
								$pre= $objinfo[$i]["pre"];
								$table=$src."_DATA_VALUE";
								$field=$pre."_DATA_$value_type";
								$field = strtoupper( $field );
								
								$attributes = ["OCCUR_DATE" => $occur_date];
								if($src=="ENERGY_UNIT"){
									$attributes['FLOW_PHASE'] = $phase_type;
								}
								$attributes["$pre"."_ID" ] = $objinfo[$i]["obj_id"];
								$values = $attributes;
								$values[$field] = $svol;
								$mdl 			= 	\Helper::getModelName($table);
								
								\DB::enableQueryLog();
								$mdl::updateOrCreate($attributes,$values);
								$sqls[]=\Helper::logger();
							}
							$i++;
						}
						\DB::disableQueryLog();
					}
				}
				else{
					$error[]= "Result file not found";
				}
			}
			else $error[]= "Input files not found";
		}
		
		
		
		$finalResults = [
				'data'			=>$inputData,
				'warning'		=>'',
// 				'params'		=>'',
// 				'time'			=>'',
				'result'		=>$results,
				'error'			=>$error,
				'key'			=>$mkey,
				'exe'			=>$exe,
				'sqls'			=>$sqls,
				
		];
		
// 		$this->cleanFiles($mkey);
    	return response()->json($finalResults);
    }
    
    
    public function cleanFiles($mkey)
    {
    	if(file_exists("forecast_q$mkey.csv")) unlink("forecast_q$mkey.csv");
    	if(file_exists("forecast_Np$mkey.csv")) unlink("forecast_Np$mkey.csv");
    	if(file_exists("data$mkey.txt")) unlink("data$mkey.txt");
    	if(file_exists("t$mkey.txt")) unlink("t$mkey.txt");
    	if(file_exists("prop$mkey.txt")) unlink("prop$mkey.txt");
    	if(file_exists("error$mkey.txt")) unlink("error$mkey.txt");
    }
}
