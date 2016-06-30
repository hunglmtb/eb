<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EnergyUnitForecastController extends CodeController {
    
	public function getWorkingTable($postData){
		$data_source 	= 	$postData['ExtensionDataSource'];
		$src			=	'ENERGY_UNIT';
		$table			=	$src."_DATA_".$data_source;
		$mdl 			= 	\Helper::getModelName($table);
		return $mdl::getTableName();
	}
	
	public function getProperties($dcTable,$facility_id,$occur_date){
		$properties = $this->getOriginProperties($dcTable);
		$results = ['properties'	=>$properties,
		];
		return $results;
	}
	
	public function getOriginProperties($dcTable){
		$properties = collect([
					(object)['data' =>	'OCCUR_DATE'	,'title' => 'Occur time'    	,	'width'=>40,'INPUT_TYPE'=>3,],
					(object)['data' =>	'T'				,'title' => 'Time'    			,	'width'=>30,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>2],
					(object)['data' =>	'V'				,'title' => 'Value'    			,	'width'=>30,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>3],
		]);
		return $properties;
	}
	
	public function getFirstProperty($dcTable){
    	return  null;
    }
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties=null){
    	$date_end 		= 	$postData['date_end'];
    	$date_end		= 	Carbon::parse($date_end);
		$id 			= 	$postData['EnergyUnit'];
		
		$phase_type 	= 	$postData['ExtensionPhaseType'];
		$value_type 	= 	$postData['ExtensionValueType'];
		$data_source 	= 	$postData['ExtensionDataSource'];
		$table			=	$this->getWorkingTable($postData);
		$mdl 			= 	\Helper::getModelName($table);
	   
	    $where = [	"EU_ID" 			=> $id,
	    			"FLOW_PHASE" 		=> $phase_type,];
	    
// 		\DB::enableQueryLog();
	    $dataSet = $mdl::where($where)
					    ->whereDate("OCCUR_DATE", '>=', $occur_date)
					    ->whereDate("OCCUR_DATE", '<=', $date_end)
					    ->select(
					    		"OCCUR_DATE",
					    		\DB::raw("'$occur_date' as T "),
					    		"EU_DATA_$value_type as V"
					    		)
					   	->orderBy('OCCUR_DATE')
					    ->get();
// 		\Log::info(\DB::getQueryLog());
					    					
    	return ['dataSet'=>$dataSet];
    }
    
    
    public function run(Request $request){
    	$postData = $request->all();
    	$date_end 		= 	$postData['date_end'];
    	$date_end		= 	Carbon::parse($date_end);
    	$id 			= 	$postData['EnergyUnit'];
    	 
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
    	
		$mkey="_".date("Ymdhis_").rand(100,1000)."hung_test";
		
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
						if($lastT && $row[T]-$t!=1 && $continous)
						{
							$continous=false;
						}
						$lastT=$t;
					}
				}
		}
		else {
			$qData 		= $this->getDataSet($postData,null,null,$date_begin);
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
		$warning = '';
		if(!$continous) $warning = "Timing is not continuous";
		
		file_put_contents("t$mkey.txt",$timeForecast);
		echo "<b>Time forecast:</b> ".$timeForecast."<br>";
		
		if($a==="0" || $a==="1")
			$params="$a,$b,0,0,0,0,0";
		else if($c2>0)
			$params="$a,$b,0,0,0,$c1,$c2";
		else
			$params="$a,$b,$l,$u,$m,$c1,0";
		
		file_put_contents("prop$mkey.txt",$params);
		
		$error = "";
		$result = "";
		
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
						
					while(! feof($file))
					{
						$line=fgets($file);
// 						echo $line;
						$result.= $line;
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
									$x_time=($x_time)*60*60*24+$date_begin->timestamp;
// 									$x_date=date('Y-m-d',$x_time);
    								$x_date		= 	Carbon::parse($x_time);
									// 									echo " ($x_date) ";
									$result.= " ($x_date) ";
									/* if($cb_update_db==1)
									{
										$table=$src."_DATA_FORECAST";
										$field="$pre"."_DATA_$value_type";
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
											mysql_query($sql) or err(mysql_error());
									} */
								}
							}
						}
// 						echo "<br>";
					}
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
		
		$results = [
				'warning'		=>$warning,
				'params'		=>$params,
				'timeForecast'	=>$timeForecast,
				'result'		=>$result,
				'error'			=>$error,
		];
		
		$this->cleanFiles($mkey);
    	return response()->json($results);
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
