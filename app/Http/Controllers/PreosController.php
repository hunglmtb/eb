<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\EnergyUnitDataForecast;
use App\Models\QltyData;
use App\Models\CodeQltySrcType;
use App\Models\QltyProductElementType;
use App\Models\QltyDataDetail;

class PreosController extends CodeController {
	
	public function getWorkingTable($postData){
		return null;
	}
	
	public function getObjectTable($src,$data_source){
		$table			=	$src."_DATA_".$data_source;
		$mdl 			= 	\Helper::getModelName($table);
		return $mdl;
	}
	
	public function getProperties($dcTable,$facility_id,$occur_date,$postData){
		$objs 			= 	$this->getPostObjects($postData);
		if ($objs&&count($objs)>0) {
			$objectArray	=	[(object)[
											'data' 			=>	"NAME",
											'title' 		=> 	"NAME",
											'width'			=>	45,
											'INPUT_TYPE'	=>	1,
											'DATA_METHOD'	=>	2]
			];
			foreach($objs as $obj){
				$ss				=	explode(":",$obj);
				$obj_id			=	$ss[1];
				$src			=	$ss[0];
				$obj_name		=	$ss[2];
				
				$objectArray[]	=	(object)[
											'data' 			=>	"$src"."_$obj_id",
											'title' 		=> 	$obj_name,
											'width'			=>	55,
											'INPUT_TYPE'	=>	2,
											'DATA_METHOD'	=>	2,
				];
			}
			
			$properties 	= collect($objectArray);
			$results 		= ['properties'	=>$properties];
			return $results;
		}
		return null;
	}
	
	public function getPostObjects($postData){
		$objs 			= 	$postData['objs'];
		if ($objs&&!empty($objs)) {
			$objs			=	explode(";",$objs);
		}
		else $objs = null;
		return $objs;
	}
	
	public function getElement($obj_name){
		return array(
// 						"Object"=>$obj_name,
						"Pressure"=>"0",
						"Temperature"=>"0",
						"Volume"=>"0",
						"CO2"=>"0",
						"Nitrogen"=>"0",
						"H2S"=>"0",
						"Methane"=>"0",
						"Ethane"=>"0",
						"Propane"=>"0",
						"i-Butane"=>"0",
						"n-Butane"=>"0",
						"i-Pentane"=>"0",
						"n-Pentane"=>"0",
						"22-Mbutane"=>"0",
						"23-Mbutane"=>"0",
						"3-Mpentane"=>"0",
						"n-Hexane"=>"0",
						"2-Mhexane"=>"0",
						"3-Mhexane"=>"0",
						"n-Heptane"=>"0",
						"22-Mhexane"=>"0",
						"2-Mheptane"=>"0",
						"3-Mheptane"=>"0",
						"n-Octane"=>"0",
						"25-Mheptane"=>"0",
						"n-Nonane"=>"0",
						"n-Decane"=>"0",
						"n-C11"=>"0",
						"n-C12"=>"0",
						"n-C13"=>"0",
						"n-C14"=>"0",
						"n-C15"=>"0",
						"n-C16"=>"0",
						"n-C17"=>"0",
						"n-C18"=>"0",
						"n-C19"=>"0",
						"n-C20"=>"0",
						"n-C21"=>"0",
						"n-C22"=>"0",
						"n-C23"=>"0",
						"n-C24"=>"0",
						"n-C25"=>"0",
						"n-C26"=>"0",
						"n-C27"=>"0",
						"n-C28"=>"0",
						"n-C29"=>"0",
						"n-C30"=>"0",
						"Cyclohexane"=>"0",
						"Mcyclopentan"=>"0",
						"11Mcycpentan"=>"0",
						"1tr2ci4-MCC5"=>"0",
						"1-tr2-MCC6"=>"0",
						"1-ci2-MCC6"=>"0",
						"Benzene"=>"0",
						"Toluene"=>"0",
						"o-Xylene"=>"0"
				);
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	if (!$properties) return [];
    		
		$phase_type 	= 	$postData['ExtensionPhaseType'];
		$value_type 	= 	$postData['ExtensionValueType'];
		$data_source 	= 	$postData['ExtensionDataSource'];
		$objs 			= 	$this->getPostObjects($postData);
		
		$objdata=array();
		foreach($objs as $obj)
			if($obj){
				$ss				=	explode(":",$obj);
				$obj_id			=	$ss[1];
				$src			=	$ss[0];
				$obj_name		=	$ss[2];
				$pre			=	$src;
				$pvalue			=	$src;
				$ele			= 	$this->getElement($obj_name);
				
				$where = ["OCCUR_DATE" => $occur_date];
				if($src=="ENERGY_UNIT"){
					$pre="EU";
					$pvalue=$pre;
					$where['FLOW_PHASE'] = $phase_type;
				}
				else if($src=="FLOW") $pvalue="FL";
				
				$where["$pre"."_ID" ] = $obj_id;
				
				$qlty_src_type	=	CodeQltySrcType::where('CODE','=',$src)->first();
				
				$mdl 			= 	$this->getObjectTable($src,$data_source);
				$workingDataSet = 	$mdl::where($where)
										->select(
// 												"ID as DT_RowId",
												"OBS_TEMP",
												"OBS_PRESS",
												"$pvalue"."_DATA_$value_type as OBJ_VALUE"
												)
										->orderBy('OCCUR_DATE')
										->get();
				
				$qltyData 				=	QltyData::getTableName();
    			$qltyProductElementType =	QltyProductElementType::getTableName();
    			$qltyDataDetail 		=	QltyDataDetail::getTableName();
    			 
				$qlty_datas = QltyData::join($qltyDataDetail,"$qltyData.ID", '=', "$qltyDataDetail.QLTY_DATA_ID")
								    	->join($qltyProductElementType,function ($query)
								    			use ($qltyDataDetail,$phase_type,$qltyProductElementType) {
										    		$query->on("$qltyDataDetail.ELEMENT_TYPE",'=',"$qltyProductElementType.ID")
										    				->where("$qltyProductElementType.PRODUCT_TYPE",'=',$phase_type) ;
									    	})
								    	->whereHas('CodeQltySrcType',function ($query) use ($src) {
											$query->where("CODE",$src )->skip(0)->take(1);
										})
										->where("$qltyData.SRC_ID",$obj_id)
										->where("$qltyData.PRODUCT_TYPE",$phase_type)
// 										->where('SRC_TYPE',$qlty_src_type)
										->where('EFFECTIVE_DATE','=',$occur_date)
										->select(
												"$qltyProductElementType.NAME",
												"$qltyDataDetail.MOLE_FACTION"
												)
										->get();
										
				foreach($workingDataSet as $objectData){
					foreach($qlty_datas as $qlty){
						if (array_key_exists($qlty->NAME,$ele)){
							$ele[$qlty->NAME]=$qlty->MOLE_FACTION?$qlty->MOLE_FACTION:0;
						}
					}
						
					$ele["Pressure"]			=	\Helper::getRoundValue($objectData->OBS_PRESS);
					$ele["Temperature"]			=	\Helper::getRoundValue($objectData->OBS_TEMP);
					$ele["Volume"]				=	\Helper::getRoundValue($objectData->OBJ_VALUE);
// 					$objdata["$obj_id"]=$ele;
				}
				$objdata["$src"."_$obj_id"]	=	$ele;
			}
		
		if (count($objdata)>0) {
			$renderData = [];
			foreach($ele as $key =>$value){
				$values = ['NAME'=>$key];
				foreach($objdata as $source =>$objValue){
					$values[$source] = $objValue[$key];
				}
				$renderData[] = $values;
			}
		}
		else $renderData = $objdata;
		
// 		\Log::info(\DB::getQueryLog());
					    					
    	return ['dataSet'=>$renderData];
    }
    
    public function run(Request $request){
    	$postData = $request->all();
    	$date_end 		= 	$postData['date_end'];
    	$date_end		= 	Carbon::parse($date_end);
    	$object_id 		= 	$postData['EnergyUnit'];
    	 
    	$phase_type 	= 	$postData['ExtensionPhaseType'];
    	$value_type 	= 	$postData['ExtensionValueType'];
    	$data_source 	= 	$postData['ExtensionDataSource'];
    	$table			=	$postData['EnergyUnit'];
    	$mdl 			= 	\Helper::getModelName($table);
    	
    	$cb_update_db	=	$postData['cb_update_db'];
    	$a				=	$postData['a'];
    	$b				=	$postData['b'];
    	$u				=	$postData['u'];
    	$l				=	$postData['l'];
    	$m				=	$postData['m'];
    	$c1				=	$postData['c1'];
    	$c2				=	$postData['c2'];
    	
    	$date_begin 	= 	$postData['date_begin'];
    	$date_begin		= 	Carbon::parse($date_begin);
    	$date_from 		= 	$postData['f_from_date'];
    	$date_from		= 	Carbon::parse($date_from);
    	$date_to 		= 	$postData['f_to_date'];
    	$date_to		= 	Carbon::parse($date_to);
    	
    	$from_date 		= 	$date_begin;
    	
		$mkey="_".date("Ymdhis_").rand(100,1000)/* ."hung_test" */;
		
		$data="";
		$continous=true;
		$lastT=null;
		
		if (array_key_exists('forecast', $postData)) {
			$txt_modify_data=$postData['forecast'];
			$ds=explode("\n",$txt_modify_data);
			foreach($ds as $line)
				if($line)
				{
					$ls=explode(",",$line);
					if(count($ls)>=2)
					{
						$t=trim($ls[0]);
						$v=trim($ls[1]);
						$data.=($data?"\r\n":"")."$t,$v";
						if($lastT && ($t-$lastT)!=1 && $continous)
						{
							$continous=false;
						}
						$lastT=$t;
					}
				}
		}
		else {
			$qData 		= $this->getDataSet($postData,null,null,$date_begin,null);
			$dataSet 	= $qData['dataSet'];
			foreach($dataSet as $row){
				$occur_date = $row->OCCUR_DATE;
				$time = $occur_date->diffInDays($from_date);
				$value = $row->V;
				$data.=($data?"\r\n":"")."$time,$value";
				if($lastT && ($time-$lastT)!=1 && $continous)
				{
					$continous=false;
				}
				$lastT=$time;
			}
		}
		
		file_put_contents("data$mkey.txt",$data);
		
		//$end = '2013-08-29';
		//$start = '2013-08-25';
		/* $d1 = strtotime($date_from) - strtotime($date_begin);
		$d1 = floor($d1/(60*60*24));
		$d2 = strtotime($date_to) - strtotime($date_begin);
		$d2 = floor($d2/(60*60*24)); */
		
		$d1 = $date_from->timestamp - $date_begin->timestamp;
		$d1 = floor($d1/(60*60*24));
		$d2 = $date_to->timestamp - $date_begin->timestamp;
		$d2 = floor($d2/(60*60*24));
		
		$timeForecast="";
		for($i = $d1; $i < $d2 + 1; $i++){
		    $timeForecast.=($timeForecast?"\r\n":"").$i;
			if($lastT && $i-$lastT!=1 && $continous)
			{
				$continous=false;
			}
			$lastT=$i;
		}
		
		$sqls = [];
		$warning = '';
		if(!$continous) $warning = "Timing is not continuous";
		
		file_put_contents("t$mkey.txt",$timeForecast);
// 		echo "<b>Time forecast:</b> ".$timeForecast."<br>";
		
		if($a==="0" || $a==="1")
			$params="$a,$b,0,0,0,0,0";
		else if($c2>0)
			$params="$a,$b,0,0,0,$c1,$c2";
		else
			$params="$a,$b,$l,$u,$m,$c1,0";
		
		file_put_contents("prop$mkey.txt",$params);
		
		$error = "";
		$results = [];
		
		if(!file_exists('pdforecast.exe'))
		{
			$error = "Exec file not found";
		}
		else
		{
			if(file_exists("data$mkey.txt") && file_exists("t$mkey.txt") && file_exists("prop$mkey.txt"))
			{
				set_time_limit(300);
				exec("pdforecast.exe $mkey");
				if(file_exists("error$mkey.txt"))
				{
					$error = file_get_contents("error$mkey.txt", true);
// 					logError(file_get_contents("error$mkey.txt", true));
				}
		
				if(file_exists("forecast_q$mkey.csv"))
				{
// 					echo "<b>Result:</b><br>";
					$file = fopen("forecast_q$mkey.csv","r");
					
					$configuration = auth()->user()->getConfiguration();
					$format = $configuration['time']['DATE_FORMAT_CARBON'];//'m/d/Y';
					
					while(! feof($file))
					{
						$line=fgets($file);
// 						echo $line;
// 						$result.= $line;
						$result = ['value'=>$line];
						
						if($line)
						{
							$xs=explode(",",$line);
							if(count($xs>=2))
							{
								$x_time=trim($xs[0]);
								$x_value=trim($xs[1]);
								if($x_time>=$d1)
								{
// 									$x_time=($x_time)*60*60*24+strtotime($date_begin);
									$beginTimeStamp = $date_begin->timestamp;
									$x_time=($x_time)*60*60*24+$beginTimeStamp;
// 									$x_date=date('Y-m-d',$x_time);
    								$x_date		= 	Carbon::createFromTimestamp($x_time);
//     								$x_date		= 	$x_date->createFromTimestamp($x_time);
    								// 									echo " ($x_date) ";
									$rxDate=$x_date?$x_date->format($format):$x_date;
//     								$result.= " ($rxDate) ";
									$result['date']=$rxDate;
    								if($cb_update_db=='true')
									{
										$field="EU_DATA_$value_type";
										$field=strtoupper($field);
										
										$attributes = [
												'EU_ID'				=>$object_id,
												'OCCUR_DATE'		=>$x_date,
												"FLOW_PHASE" 		=>$phase_type
												
										];
										$values = [
												'EU_ID'				=>$object_id,
												'OCCUR_DATE'		=>$x_date,
												"FLOW_PHASE" 		=>$phase_type,
												$field				=>$x_value
										];
 										\DB::enableQueryLog();
										EnergyUnitDataForecast::updateOrCreate($attributes,$values);
										$result['sql']=\Helper::logger();
// 										$sqls[] = \DB::getQueryLog();
										/* $table=$src."_DATA_FORECAST";
										$sql="select ID from $table a where a.$pre"."_ID=$object_id and OCCUR_DATE='$x_date' $phasecondition";
										$id=getOneValue($sql);
										if($id>0)
											$sql="update $table set $field='$x_value' where ID=$id";
											else
											{
												if($src=="ENERGY_UNIT")
													$sql="insert into $table($pre"."_ID,OCCUR_DATE,FLOW_PHASE,$field) values ($object_id,'$x_date',$phase_type,'$x_value')";
													else
														$sql="insert into $table($pre"."_ID,OCCUR_DATE,$field) values ($object_id,'$x_date','$x_value')";
											}
											$sql=str_replace("''","null",$sql);
											echo " sql: $sql";
											mysql_query($sql) or err(mysql_error()); */
									}
								}
							}
						}
						$results[] = $result;
// 						echo "<br>";
					}
					\DB::disableQueryLog();
					fclose($file);
				}
				else
				{
// 					logError("Result file not found");
					$error.= "Result file not found";
				}
			}
			else
			{
// 				logError("Input files not found");
				$error.= "Input files not found";
			}
		}
		
		$finalResults = [
				'data'			=>$data,
				'warning'		=>$warning,
				'params'		=>$params,
				'time'			=>$timeForecast,
				'result'		=>$results,
				'error'			=>$error,
		];
		
		$this->cleanFiles($mkey);
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
