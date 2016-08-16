<?php
use App\Models\CfgFieldProps;
use App\Models\Formula;
use App\Models\Fovar;
use App\Models\CodeQltySrcType;
use App\Models\QltyDataDetail;
use App\Models\QltyData;
use App\Models\QltyProductElementType;
use App\Models\StrappingTableData;
use App\Models\PdContractYear;
use App\Models\PdContractCalculation;
use App\Models\PdContractData;
use App\Models\PdCodeContractAttribute;

function sum(){
	$args = func_get_args();
	$s=0;
	foreach ($args as $arg)
	{
		if(is_array($arg))
		{
			foreach($arg as $value)
			{
				if(is_array($value))
					$s += sum($value);
					else $s += $value;
			}
		}
		else $s += $arg;
	}
	return $s;
}

function fn($n,$tem = 0) {
	global $aryMstCalcu;
	if($tem != 0) {
		$aryMstCalcu = $tem;
	}
	$value = array_key_exists('fn('.$n.')', $aryMstCalcu)?(int) $aryMstCalcu['fn('.$n.')']:0;
	return $value;

}

function contract_attr($formulaId,$code,$year = '') {
 	global $contractIdGlobal;
 	global $yearGlobal;
	
	if($year != '') {
		$year  = $yearGlobal - 1;
		$sSQL  =" SELECT a.FORMULA_VALUE FROM pd_contract_year a ,pd_contract_calculation b WHERE b.ID = a.CALCULATION_ID AND"
				. "  a.CONTRACT_ID =  ".$contractIdGlobal ." AND  a.YEAR =  '".$year."'"." AND  b.FORMULA_ID =  '".$formulaId."'";
		
		$pdContractYear			= PdContractYear::getTableName();
		$pdContractCalculation	= PdContractCalculation::getTableName();
		$contractYear			= PdContractYear::join($pdContractCalculation,"$pdContractYear.CALCULATION_ID", '=', "$pdContractCalculation.ID")
									->where("$pdContractYear.CONTRACT_ID", '=', $contractIdGlobal)
									->where("$pdContractYear.YEAR", '=', $year)
									->where("$pdContractCalculation.FORMULA_ID", '=', $formulaId)
									->select("$pdContractYear.FORMULA_VALUE as ATTRIBUTE_VALUE")
									->first();
	} else {
		$sSQL  =" SELECT ATTRIBUTE_VALUE 
				FROM pd_code_contract_attribute a
				,pd_contract_data b
				WHERE b.ATTRIBUTE_ID = a.ID AND"
				. "  b.CONTRACT_ID =  ".$contractIdGlobal ." 
						AND  a.CODE =  '".$code."'";
		
		$pdContractData				= PdContractData::getTableName();
		$pdCodeContractAttribute	= PdCodeContractAttribute::getTableName();
		$contractYear				= PdCodeContractAttribute::join($pdContractData,"$pdContractData.ATTRIBUTE_ID", '=', "$pdCodeContractAttribute.ID")
												->where("$pdCodeContractAttribute.CODE", '=', $code)
												->where("$pdContractData.CONTRACT_ID", '=', $contractIdGlobal)
												->select("ATTRIBUTE_VALUE")
												->first();
	}
	/* $result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
	while($row=mysql_fetch_array($result)) {
		return $row['ATTRIBUTE_VALUE'];
	} */
	if ($contractYear) {
		return $contractYear->ATTRIBUTE_VALUE;
	}
	return 0;
}


function evalErrorHandler($errno, $errstr, $errfile, $errline){
    \Log::info("$errstr at errno $errno file $errfile line $errline");
	if ($errstr == 'Division by zero') {
		return null;
	}
	elseif ($errstr == 'Undefined variable: rho_o_obs'){
		return null;
	}
	
	throw new Exception("$errstr at errno $errno file $errfile line $errline");
}
class FormulaHelpers {
	
	public static function doFormula($tName,$keyfield,$keyvalues,$echo_only=false){
    	if(!$keyfield || !$keyvalues) return false;
    	
    	$mdl = "App\Models\\$tName";
    	$tablename = $mdl::getTableName();
    	$formulas = CfgFieldProps::where('table_name', $tablename)
    	->whereNotNull('FORMULA')
    	->where('FORMULA','<>', '')
    	->select("COLUMN_NAME", "FORMULA")
    	->get();
    	
//     	$sSet="";
    	$values = [];
    	foreach($formulas as $row ){
    		if($row->FORMULA)
    		{
    			$ss=trim($row->FORMULA);
    			$sWhere="";
    			if (strpos($ss,'{') !== false){
    				$k1=strpos($ss,'{');
    				$k2=strpos($ss,'}',$k1);
    				if($k2>$k1+1){
    					$sWhere=substr($ss,$k1+1,$k2-$k1-1);
    				}
    			}
    			if(strpos(strtoupper($ss), "HOURS")!== false&&strpos(strtoupper($ss), "HOURS")==0)
    			{
    				$ss="time_to_sec(timediff".substr($ss,5).") / 3600";
    			}
    			else if(substr($ss,0,1)==="[") //table
    			{
    		
    				$k2=0;
    				$x_ss="";
    				$i=0;
    				while(true)
    				{
    					$i++;
    					if($i>100) break;
    		
    					$k1=strpos($ss,'[',$k2);
    					if($k1===false)
    						break;
    						$kx=$k2;
    						$k2=strpos($ss,']',$k1);
    						if($k2===false)
    							break;
    							if($kx>0 && $k1>$kx)
    								$x_ss.=substr($ss,$kx+1,$k1-$kx-1);
    		
    								$i1=strpos($ss,'(',$k1);
    								if($i1===false)
    									break;
    									$i2=strpos($ss,')',$i1);
    									if($i2===false)
    										break;
    		
    										$s_table=substr($ss,$k1+1,$i1-$k1-1);
    										$x_where_fields=explode(',',substr($ss,$i1+1,$i2-$i1-1));
    		
    										$s_where="";
    		
    										foreach($x_where_fields as $x_where_field)
    										{
    											$x_where_field_parts=explode('=',trim($x_where_field));
    											$is_raw_value = false;
    											if(count($x_where_field_parts)>1)
    											{
    												$v_val=$x_where_field_parts[1];
    												if(is_numeric($v_val) || substr($v_val,0,1)=="'")
    													$is_raw_value=true;
    											}
    											if($is_raw_value)
    												$s_where.=($s_where?" and ":"")."`$x_where_field_parts[0]`=$v_val";
    												else
    													$s_where.=($s_where?" and ":"")."`$x_where_field_parts[0]`=`$tablename`.`".$x_where_field_parts[count($x_where_field_parts)-1]."`";
    										}
    										$s_select=substr($ss,$i2+1,$k2-$i2-1);
    										$x_ss.="(select $s_select from $s_table where $s_where limit 1)";
    				}
    				$ss=$x_ss;
    			}
    			if($sWhere) {
//     				$sSet.=($sSet?",":"")."`$row->COLUMN_NAME`=(case when $sWhere then $ss else `$row->COLUMN_NAME` end)";
    				$values[$row->COLUMN_NAME]=\DB::raw("(case when $sWhere then $ss else `$row->COLUMN_NAME` end)");
    			}
    			else {
//     				$sSet.=($sSet?",":"")."`$row->COLUMN_NAME`=$ss";
    				$values[$row->COLUMN_NAME]=\DB::raw($ss);
    			}
    		}
    	}
    		 
//     	if($sSet)
    	if(count($values)>0)
    	{
     		if($echo_only) echo "SQL formular: $values";
     		else {
	    		/* $ids = implode("','",$keyvalues);
				$sSQL="update $tablename set $sSet where `$keyfield` in ($ids)";
	 			$result = \DB::update($sSQL); */
				//     		$result = \DB::update('update ? set ? where ? in ?', [$tablename,$sSet,$keyfield,$keyvalues]);
	    		//     		error_log("<br>sSQL: $sSQL</br>", 3, "C:/xampp/htdocs/eb/log/hung.log");
//      			$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
// 		      	\DB::enableQueryLog();
				$updateRecords = $mdl::whereIn($keyfield,$keyvalues)->update($values);
// 				\Log::info(\DB::getQueryLog());
	    		if ($updateRecords) {
					return array_keys($values);
	    		}
     		}
    	}
    	return true;
    }
    
    public static function applyFormula($mdlName,$objectIds,$occur_date,$returnAffectedIds=false){
    	
//     	global $object_id,$flow_phase,$occur_date,$facility_id;
    	$mdl = "App\Models\\$mdlName";
    	$objectType = $mdl::$typeName;
    	 
    	$result = [];
    	foreach($objectIds as $object_id){
	    	$formulas = self::getFormulatedFields($mdl::getTableName(),$object_id,$object_type,$occur_date);
	    	$values = [];
	    	foreach($formulas as $formula){
// 	    		$temp_value="'".doFormulaObject($tablename, $field, $object_type, $object_id, $occur_date)."'";
				$v=self::evalFormula($formula,$occur_date);
	    		if ($v!==null) $values[$formula->VALUE_COLUMN]=$v;
	    	}
	    	
	    	if (count($values)>0) {
	    		/* $updateRecords = $mdl::where('OCCUR_DATE',$occur_date)
	    		->where(config("constants.idColumn.$object_type"),$object_id)
	    		->update($values); */
	    		
	    		$updateRecords = $mdl::updateWithFormularedValues($values,$occur_date,$formulas);
	    		/* if ($updateRecords>0&&$returnAffectedIds) {
	    			$result[] = $mdl::where('OCCUR_DATE',$occur_date)
								    		->where(config("constants.idColumn.$object_type"),$object_id)
								    		->select('ID')
								    		->first()->ID;
	    		}; */
	    		
	    		if ($updateRecords>0&&$returnAffectedIds) {
	    			$result[] = $updateRecords;
	    		};
	    	}
    	}
    	return $result;
    }
    
    public static function getFormulatedFields($tableName,$object_id,$object_type,$occur_date,$flow_phase=false){
    	$where = ['OBJECT_TYPE'		=>	$object_type,
    			'OBJECT_ID'			=>	$object_id,
    			'TABLE_NAME'		=>	$tableName];
    	if ($flow_phase) $where['FLOW_PHASE'] = $flow_phase;
//     	if ($occur_date) $where['FLOW_PHASE'] = $flow_phase;
    	
//     	"(('".toDateString($occur_date)."' between a.BEGIN_DATE and a.END_DATE) or ('".toDateString($occur_date)."'>=a.BEGIN_DATE and a.END_DATE is null) or (a.BEGIN_DATE is null and a.END_DATE is null))""
//     	\DB::enableQueryLog();
    	$fields = Formula::where($where)
							->where(function ($query) use ($occur_date){
					                $query->where(['BEGIN_DATE'=>	null,'END_DATE'=>null])
					                	  ->orWhere(function ($query) use ($occur_date) {
									                $query->where('BEGIN_DATE','<=',$occur_date)
									                	  ->where('END_DATE',null);
									        		})
					                      ->orWhere(function ($query) use ($occur_date){
									                $query->where('BEGIN_DATE','<=',$occur_date)
									                	  ->where('END_DATE','>=',$occur_date);
									        		});
					        		})
					        ->get();
//     	\Log::info(\DB::getQueryLog());
    	return $fields;
    }
    
    public static function getAffects($mdlName,$columns,$objectId,$flow_phase=false){
    	
    	$mdl = "App\Models\\$mdlName";
		$objectType = $mdl::$typeName;
    	$where = ['OBJECT_TYPE'		=>	$objectType,
    			'OBJECT_ID'			=>	$objectId,
    			'TABLE_NAME'		=>	$mdl::getTableName()];
    	
    	if ($flow_phase) $where['FLOW_PHASE']=$flow_phase;
    	 
    	//     	\DB::enableQueryLog();
    	$foVars = FoVar::with('Formula')
    						->where($where)
    						->whereIn('VALUE_COLUMN',$columns)
//     						->select('AFFFECT_ID')
    						->get();
    	//     	\Log::info(\DB::getQueryLog());
    	$affectedFormulas = [];
	    foreach($foVars as $foVar ){
	    	$fml = $foVar->Formula;
	    	if ($fml) {
		    	$affectedFormulas[] = $fml;
//  		    	$affectedFormulas[] = $fml->OBJECT_ID;
	    	}
	    }
 	    $affectedFormulas = array_unique($affectedFormulas);
	     
    	return $affectedFormulas;
    }
    
    public static function applyAffectedFormula($objectWithformulas,$occur_date){
    	
    	if (!$objectWithformulas) return false;
    	$result = [];
    	foreach($objectWithformulas as $objectWithformula){
	    	$tableName = strtolower ( $objectWithformula->TABLE_NAME);
	    	$mdlName = \Helper::camelize($tableName,'_');
	    	if (!$mdlName)  continue;
	    	$mdl = "App\Models\\$mdlName";
	    	$object_type = $mdl::$typeName;
	    	$object_id = $objectWithformula->OBJECT_ID;
	    	$flow_phase= $objectWithformula->FLOW_PHASE;
	    	$formulas = self::getFormulatedFields($tableName,$object_id,$object_type,$occur_date,$flow_phase);
	    	$values = [];
	    	foreach($formulas as $formula){
// 	    		$temp_value="'".doFormulaObject($tablename, $field, $object_type, $object_id, $occur_date)."'";
				$v=self::evalFormula($formula,$occur_date);
	    		if ($v!==null) $values[$formula->VALUE_COLUMN]=$v;
	    	}
	    	
	    	if (count($values)>0) {
	    		$updateRecord = $mdl::updateWithFormularedValues($values,$object_id,$occur_date,$flow_phase);
	    		if ($updateRecord) {
	    			$updateRecord->{"modelName"} = $mdlName;
	    			$result[] = $updateRecord;
	    		};
	    	}
    	}
    	return $result;
    }
    
    
    
    
    public static function evalFormula($formulaRow,$occur_date,  $show_echo = false){
    	if(!$formulaRow)
    	{
    		return false;
    	}
    	 
    	$fid = $formulaRow->ID;
    	$flow_phase = $formulaRow->FLOW_PHASE;
    	$object_id = $formulaRow->OBJECT_ID;
    	$formula = $formulaRow->FORMULA;
    	$foVars = $formulaRow->FoVar()->get();
    
    	/* if(!$object_id)
    	 {
    	 $object_id=getOneValue("select a.OBJECT_ID from FORMULA a where a.ID=$fid");
    	 $object_id=explode(',',$object_id);
    	 $object_id=$object_id[0];
    	 } */
    	 
    	$CURRENT_DATE=date("Y-m-d");
    	//     	$formula=getOneValue("select FORMULA from `FORMULA` where id='$fid'");
    
    	/* $sSQL="select a.*, case when (a.STATIC_VALUE like '%-%-%' or a.STATIC_VALUE = '@OCCUR_DATE') then 1 else 0 end IS_DATE from fo_var a where a.formula_id='$fid' order by a.`ORDER`";
    	 $result=mysql_query($sSQL) or die (mysql_error()); */
    	$s="";
    	$i=0;
    	$vars=array();
    	$vvv=array();
    	/* if($occur_date)
    	{
    		$sdate=explode("/",$occur_date);
    		if(sizeof($sdate)>=3)
    			$occur_date=$sdate[2]."-".$sdate[0]."-".$sdate[1];
    	} */
    	 
    	foreach($foVars as $row ){
    		 
    		array_push($vvv,$row->NAME);
    		$row->STATIC_VALUE=str_replace("@OCCUR_DATE","'$occur_date'",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("@OBJECT_ID",$object_id,$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("@FLOW_PHASE",$flow_phase,$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("@VAR_OBJECT_ID",$row->OBJECT_ID,$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("#OIL#","1",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("#GAS#","2",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("#WATER#","3",$row->STATIC_VALUE);
    		if(strpos($row->STATIC_VALUE,"#CODE_")!==false)
    			$row->STATIC_VALUE=processFormulaCodeConstant($row->STATIC_VALUE);
    
    			if($row->IS_DATE>0)
    			{
    				$s='$'.$row->NAME."='$row->STATIC_VALUE';\$vs=\$$row->NAME;";
    				eval($s);
    				$vars[$row->NAME]=$vs;
    			}
    			else if(is_numeric($row->STATIC_VALUE))
    			{
    				$s='$'.$row->NAME."=$row->STATIC_VALUE;\$vs=\$$row->NAME;";
    				eval($s);
    				$vars[$row->NAME]=$vs;
    			}
    			else if(strpos($row->STATIC_VALUE,"[")>0)
    			{
    				$i=strpos($row->STATIC_VALUE,"[");
    				$j=strpos($row->STATIC_VALUE,"]",$i);
    				if($j>$i)
    				{
    					$ms=substr($row->STATIC_VALUE,0,$i);
    					$key=substr($row->STATIC_VALUE,$i+1,$j-$i-1);
    					$vs=explode("\r",$vars[$ms]);
    					$vl="";
    					foreach($vs as $v)
    					{
    						$vx=explode("=",$v);
    						if(trim($vx[0])==$key)
    						{
    							$vl=$vx[1];
    							break;
    						}
    					}
    					if($vl)
    					{
    						$s='$'.$row->NAME."=$vl;\$vs=\$$row->NAME;";
    						eval($s);
    						$vars[$row->NAME]=$vs;
    					}
    				}
    			}
    			else if(substr($row->STATIC_VALUE,0,6)=="matlab")
    			{
    				$i=strpos($row->STATIC_VALUE,"(");
    				$j=strpos($row->STATIC_VALUE,")",$i);
    				if($j>$i)
    				{
    					$ms=explode(",",substr($row->STATIC_VALUE,$i+1,$j-$i-1));
    					$args="";
    					$matlab_code=$ms[0];
    					for($i=1;$i<sizeof($ms);$i++)
    					{
    						$args.=($args==""?"":"%20").$vars[$ms[$i]];
    					}
    					$s="\$vs = file_get_contents('http://energybuilder.co/eb/matlab/$matlab_code/$matlab_code.php?act=get&a=".$args."', true);";
    					//echo "xxxxx".$s;
    					eval($s);
    					$vars[$row->NAME]=$vs;
    				}
    				//$s="$m = file_get_contents('http://energybuilder.co/eb/matlab/test.php?act=get&a=1%204%202', true);
    			}
    			else if(substr($row->STATIC_VALUE,0,7)=="getData")
    			{
    				if($row->TABLE_NAME && $row->VALUE_COLUMN)
    				{
    					$j=strpos($row->STATIC_VALUE,"(");
    					$k=self::findClosedSign(")",$row->STATIC_VALUE,$j);
    
    					if($k>$j && $j>0)
    					{
    						$table=$row->TABLE_NAME;
    						$field=$row->VALUE_COLUMN;
    						$sql="select $field from `$table` where 1";
    						//echo "field: $field<br>";
    						$params=explode(",",substr($row->STATIC_VALUE,$j+1,$k-$j-1));
    						$where = [];
    						$swhere = false;
    						foreach ($params as $param)
    						{
    							$deli="";
    							if (strpos($param,'>=') !== false) {
    								$deli=">=";
    							}
    							else if (strpos($param,'<=') !== false) {
    								$deli="<=";
    							}
    							else if (strpos($param,'>') !== false) {
    								$deli=">";
    							}
    							else if (strpos($param,'<') !== false) {
    								$deli="<";
    							}
    							else if (strpos($param,'=') !== false) {
    								$deli="=";
    							}
    							if($deli!=="")
    							{
    								$ps=explode($deli,$param);
    								if($ps[1]=="@DATE"){
    									$pp = "$ps[0] $deli $CURRENT_DATE";
    									$whereItem = [$ps[0],$deli,$CURRENT_DATE];
    								}
    								else if (isset($vars[$ps[1]])&&is_numeric($vars[$ps[1]])){
    									$pp = "$ps[0] $deli ".$vars[$ps[1]]."";
    									$whereItem = [$ps[0],$deli,$vars[$ps[1]]];
    								}
    								else{
    									if(isset($vars[$ps[1]])){
    										$pp = "$ps[0] $deli '".$vars[$ps[1]]."'";
    										$whereItem = [$ps[0],$deli,$vars[$ps[1]]];
    									}
    									else{
    										$pp = "$ps[0] $deli $ps[1]";
    										$whereItem = [$ps[0],$deli,$ps[1]];
    									}
	    							}
	    							$sql.=" and $pp";
//     								$swhere.=" and $pp";
	    							if($whereItem[0]=="OCCUR_DATE"||$whereItem[0]=="EFFECTIVE_DATE"){
	    								$whereItem[2] = $occur_date;
		    							$where[]=$whereItem;
	    							}
	    							else if (strpos($whereItem[0], 'month') !== false || strpos($whereItem[0], 'year') !== false) {
    									$swhere = $swhere?"$swhere and $pp":$pp;
	    							}
	    							else {
		    							$where[]=$whereItem;
	    							}
	    							//echo "param: $pp<br>";
    							}
    						}
    						$sql .= " limit 100";
//      						\DB::enableQueryLog();
//      						$getDataResult = DB::table($table)->where( \DB::raw($swhere))->select($field)->skip(0)->take(100)->get();
       						$queryField = DB::table($table)->where($where);
       						if ($swhere) {
       							$queryField->where( \DB::raw($swhere));
       						}
    						$getDataResult = $queryField->select($field)->skip(0)->take(100)->get();
//      						\Log::info(\DB::getQueryLog());
    						unset($table);
    						unset($where);
    						unset($params);
    						$num_rows = count($getDataResult);
//     						$rrr=mysql_query($sql) or die("fail: ".$sql."-> error:".mysql_error());
//     						$num_rows = mysql_num_rows($rrr);
    
    						//$sqlvalue=getOneRow($sql);
    						if($num_rows==0)
    						{
    							$s='$'.$row->NAME."='null';\$vs=\$$row->NAME;";
    							eval($s);
    						}
    						else if($num_rows==1)
    						{
//     							$sqlvalue=mysql_fetch_array($rrr);
    							$sqlvalue = $getDataResult[0];
    							if(count($sqlvalue)<=2)
    							{
    								$stdvl = $sqlvalue->$field;
    								$s='$'.$row->NAME."='$stdvl';\$vs=\$$row->NAME;";
    								eval($s);
    							}
    							else
    							{
    								$sqlarray=array();
    								foreach ($sqlvalue as $key => $value)
    								{
    									if(is_numeric($key))
    										$sqlarray[$key]=$value;
    								}
    								//for($i=0;$i<count($sqlvalue)/2;$i++)
    								//{
    								//	$sqlarray[]=$sqlvalue[$i];
    								//}
    								$s='$'.$row->NAME.'=$sqlarray;'."\$vs=\$$row->NAME;";
    								eval($s);
    							}
    						}
    						else
    						{
    							/* $sqlvalue=array();
    							while($rox=mysql_fetch_array($rrr))
    							{
    								$sqlvalue[]=$rox;
    							} */
    							
     							$sqlvalue= is_array ( $getDataResult )?$getDataResult:$getDataResult->toArray();
//     							$sqlvalue= $getDataResult;
    							$sqlarray=array();
    							for($k=0;$k<$num_rows;$k++)
    							{
    								foreach ($sqlvalue[$k] as $key => $value)
    								{
//     									if(is_numeric($key))
    										$sqlarray[$k][$key]=$value;
    								}
    								//for($i=0;$i<count($sqlvalue[$k])/2;$i++)
    								//{
    								//	$sqlarray[$k][]=$sqlvalue[$k][$i];
    								//}
    							}
    							$s='$'.$row->NAME.'=$sqlarray;'."\$vs=\$$row->NAME;";
    							eval($s);
    						}
    
    						$vars[$row->NAME]=$vs;
    						unset($field);
    						unset($getDataResult);
    						unset($s);
    					}
    				}
    			}
    			else
    			{
    				$v=$row->STATIC_VALUE;
    				$i=strpos($v,".");
    				if($i>0)
    				{
    					$table=substr($v,0,$i);
    					//echo "table: $table<br>";
    					$j=strpos($v,"(",$i);
    					$k=strpos($v,")",$i);
    					if($j>$i && $k>$j)
    					{
    						$field=substr($v,$i+1,$j-$i-1);
    						$sql="select `$field` from `$table` where 1";
    						//echo "field: $field<br>";
    						$params=explode(",",substr($v,$j+1,$k-$j-1));
    						foreach ($params as $param)
    						{
    							$ps=explode("=",$param);
    							if ($vars[$ps[1]])
    								$pp = "$ps[0] = '".$vars[$ps[1]]."'";
    								else
    									$pp = "$ps[0] = '$ps[1]'";
    									$sql.=" and $pp";
    									//echo "param: $pp<br>";
    						}
    						$sqlvalue=getOneValue($sql);
    						$s='$'.$row->NAME."='$sqlvalue';\$vs=\$$row->NAME;";
    						eval($s);
    						$vars[$row->NAME]=$vs;
    					}
    				}
    			}
    	}
    
    	/* while($row=mysql_fetch_array($result))
    	 {} */
    	$f=$formula;
    	$varsKey = [];
    	foreach($vvv as $key => $v)
    	{
    		//$f=str_replace($v,$vars[$v],$f);
    		if(!$vars[$v]) $f=str_replace($v,"0",$f);
    		
    		else if(is_array($vars[$v])) {
    			$varsKey[$v] = $vars[$v];
    			$f=str_replace($v,"\$varsKey['$v']",$f);
    		}
    		
    		else $f=str_replace($v,$vars[$v],$f);
    				//if() echo "$f<br>";
    	}
    	$f=str_replace("@DATE",$CURRENT_DATE,$f);
    
    	$s='$vf = '.$f.";";
    	if(!(self::php_syntax_error($s)))
    	{
      		set_error_handler("evalErrorHandler");
	    	try {
    			eval($s);
	    	} catch( Exception $e ){
    			\Log::info("Exception with eval $s ".$e->getMessage());
	    		$vf=null;
	    	}
	    		
  	    	restore_error_handler();
    	}
    	else
    	{
    		$vf=null;
    	}
    
    	return $vf;
    }

    public static function findClosedSign($sign,$s,$from){
    	$s1="";
    	if($sign===")") $s1="(";
    	else if($sign==="]") $s1="[";
    	else if($sign==="}") $s1="{";
    	if(!$s1)
    		return false;
    		if (strpos($s,$sign,$from) === false)
    			return false;
    			$i=$from;
    			$k=0;
    			while($i<strlen($s)){
    				if($s[$i]===$s1)
    					$k++;
    					else if($s[$i]===$sign)
    						$k--;
    						if($k==0)
    							return $i;
    							$i++;
    			}
    			return null;
    }
    
     public static function php_syntax_error($code){
    	$braces=0;
    	$inString=0;
    	foreach (token_get_all('<?php ' . $code) as $token) {
    		if (is_array($token)) {
    			switch ($token[0]) {
    				case T_CURLY_OPEN:
    				case T_DOLLAR_OPEN_CURLY_BRACES:
    				case T_START_HEREDOC: ++$inString; break;
    				case T_END_HEREDOC:   --$inString; break;
    			}
    		} else if ($inString & 1) {
    			switch ($token) {
    				case '`': case '\'':
    				case '"': --$inString; break;
    			}
    		} else {
    			switch ($token) {
    				case '`': case '\'':
    				case '"': ++$inString; break;
    				case '{': ++$braces; break;
    				case '}':
    					if ($inString) {
    						--$inString;
    					} else {
    						--$braces;
    						if ($braces < 0) break 2;
    					}
    					break;
    			}
    		}
    	}
    	$inString = @ini_set('log_errors', false);
    	$token = @ini_set('display_errors', true);
    	ob_start();
    	$braces || $code = "if(0){{$code}\n}";
    	if (eval($code) === false)
    	{
    		ob_end_clean();
    		$code = true;
    		/*
    		 if ($braces) {
    		 $braces = PHP_INT_MAX;
    		 } else {
    		 false !== strpos($code,CR) && $code = strtr(str_replace(CRLF,LF,$code),CR,LF);
    		 $braces = substr_count($code,LF);
    		 }
    		 $code = ob_get_clean();
    		 $code = strip_tags($code);
    		 if (preg_match("'syntax error, (.+) in .+ on line \d+)$'s", $code, $code)) {
    		 $code[2] = (int) $code[2];
    		 $code = $code[2] <= $braces
    		 ? array($code[1], $code[2])
    		 : array('unexpected $end' . substr($code[1], 14), $braces);
    		 }
    		 else $code = array('syntax error', 0);
    		 */
    	}
    	else
    	{
    		ob_end_clean();
    		$code = false;
    	}
    	@ini_set('display_errors', $token);
    	@ini_set('log_errors', $inString);
    	return $code;
    }
    
    
    public static function calculateBg($flow_phase,$T_obs,$P_obs,$API_obs,$occur_date,$object_id,$object_type_code)
    {
    	$_Bg=null;
    	set_error_handler("evalErrorHandler");
    	if($flow_phase==1)//OIL
    	{
    		try {
	    		if($T_obs && $P_obs && $API_obs)
	    			$_Bg=self::calculateCrudeOil($T_obs, $P_obs, $API_obs);
    		} catch( Exception $e ){
    			\Log::info($e->getTraceAsString());
    		}
    	}
    	else if($flow_phase==2 || $flow_phase==21)//GAS
    	{
    		$cqst = CodeQltySrcType::getTableName();
    		$qdata = QltyData::getTableName();
    		
    		$row= QltyData::with(['CodeQltySrcType' => function ($query) use ($object_type_code) {
														    $query->where('CODE', '=', $object_type_code);
														
														}])
     					->where('SRC_ID',$object_id)
     					->whereDate("$qdata.EFFECTIVE_DATE", '<=', $occur_date)
 				     	->select("$qdata.ID")
 				     	->orderBy("$qdata.EFFECTIVE_DATE",'desc')
 						->first();
 						
    		//Find composition %Mol
//     		$sSQL="select a.ID from qlty_data a, code_qlty_src_type b where a.SRC_ID='$object_id' and a.SRC_TYPE=b.ID and b.CODE='$object_type_code' and a.EFFECTIVE_DATE<=STR_TO_DATE('$occur_date', '%m/%d/%Y') order by a.EFFECTIVE_DATE desc limit 1";
    		if($row)
    		{
    			$dataID=$row->ID;
//     			\DB::enableQueryLog();
    			$querys = [
    					'C1' =>QltyDataDetail::whereHas('QltyProductElementType',function ($query) {$query->where("CODE",'C1' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C2' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'C2' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C3' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'C3' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C4I'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'IC4');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C4N'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'NC4');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C5I'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'IC5');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C5N'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'NC5');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C6' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'C6' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C7' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'C7+');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'H2S'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'H2S');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'CO2'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'CO2');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'N2' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'N2' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'M_C7' =>QltyProductElementType::where('CODE','C7+')->select(\DB::raw("max(MOL_WEIGHT)")),
    					'G_C7' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'C7+' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(GAMMA_C7)")),
    					 ];
	
    			$qr = \DB::table(null);
    			foreach($querys as $key => $query ){
    				$qr = $qr->selectSub($query->getQuery(),$key);
    			}
    			$qdltDatas = $qr->first();
//      			\Log::info("qdltDatas C1 ".$qdltDatas->C1);
    								
// 				\Log::info(\DB::getQueryLog());
				
    			if($row)
    			{
    				$MolWt_C7	=$qdltDatas->M_C7;
    				$gamma_C7	=$qdltDatas->G_C7;
    				$M_C1	= $qdltDatas->C1;
    				$M_C2	= $qdltDatas->C2;
    				$M_C3	= $qdltDatas->C3;
    				$M_C4n	= $qdltDatas->C4N;
    				$M_C4i	= $qdltDatas->C4I;
    				$M_C5n	= $qdltDatas->C5N;
    				$M_C5i	= $qdltDatas->C5I;
    				$M_C6n	= $qdltDatas->C6;
    				$M_C7	= $qdltDatas->C7;
    				$M_H2S	= $qdltDatas->H2S;
    				$M_CO2	= $qdltDatas->CO2;
    				$M_N2	= $qdltDatas->N2;
    
    				if($T_obs && $P_obs){
    					try {
    						$_Bg=self::calculateGas($T_obs, $P_obs,$MolWt_C7,$gamma_C7,$M_C1,$M_C2,$M_C3,$M_C4n,$M_C4i,$M_C5n,$M_C5i,$M_C6n,$M_C7,$M_H2S,$M_CO2,$M_N2);
    					} catch( Exception $e ){
    						\Log::info($e->getTraceAsString());
    					}
    				}
    			}
    		}
    	}
    	restore_error_handler();
    	return $_Bg;
    }
    
    

    public static function calculateGasWithImp(
    		$T_obs,
    		$P_obs,
    
    		$M_C1,
    		$M_C2,
    		$M_C3,
    		$M_C4n,
    		$M_C4i,
    		$M_C5n,
    		$M_C5i,
    		$M_C6n,
    		$M_C7,
    		$M_H2S,
    		$M_CO2,
    		$M_N2,
    
    		$MolWt_C7
    		)
    {
    
    	$al_0=0.05207300;
    	$al_1=1.01600000;
    	$al_2=0.86961000;
    	$al_3=0.72646000;
    	$al_4=0.85101000;
    	$al_5=0.00000000;
    	$al_6=0.02081800;
    	$al_7=-0.00015060;
    
    	$b_0=-0.39741000;
    	$b_1=1.05030000;
    	$b_2=0.96592000;
    	$b_3=0.78569000;
    	$b_4=0.98211000;
    	$b_5=0.00000000;
    	$b_6=0.45536000;
    	$b_7=-0.00376840;
    
    	$A_1=0.32650;
    	$A_2=-1.07000;
    	$A_3=-0.53390;
    	$A_4=0.01569;
    	$A_5=-0.05165;
    	$A_6=0.54750;
    	$A_7=-0.73610;
    	$A_8=0.18440;
    	$A_9=0.10560;
    	$A_10=0.61340;
    	$A_11=0.72100;
    
    	$yi1	=$M_C1/1;
    	$yi2	=$M_C2/1;
    	$yi3	=$M_C3/1;
    	$yi4	=$M_C4n/1;
    	$yi5	=$M_C4i/1;
    	$yi6	=$M_C5n/1;
    	$yi7	=$M_C5i/1;
    	$yi8	=$M_C6n/1;
    	$yi9	=$M_C7/1;
    	$yi10	=$M_H2S/1;
    	$yi11	=$M_CO2/1;
    	$yi12	=$M_N2/1;
    
    	$Tc1	=(-116.67+460);
    	$Tc2	=(89.92+460);
    	$Tc3	=(206.06+460);
    	$Tc4	=(305.62+460);
    	$Tc5	=(274.46+460);
    	$Tc6	=(385.8+460);
    	$Tc7	=(369.1+460);
    	$Tc8	=(453.6+460);
    	$Tc9	=(512.7+460);
    	$Tc10	=(212.45+460);
    	$Tc11	=(87.91+460);
    	$Tc12	=(-232.51+460);
    
    	$Mw1	=$yi1*16.04;
    	$Mw2	=$yi2*30.07;
    	$Mw3	=$yi3*44.1;
    	$Mw4	=$yi4*58.12;
    	$Mw5	=$yi5*58.12;
    	$Mw6	=$yi6*72.15;
    	$Mw7	=$yi7*72.15;
    	$Mw8	=$yi8*86.18;
    	$Mw9	=$yi9*$MolWt_C7;
    	$Mw10	=$yi9*34.08;
    	$Mw11	=$yi9*44.01;
    	$Mw12	=$yi9*28.01;
    
    	$Pc1	=666.4;
    	$Pc2	=706.5;
    	$Pc3	=616;
    	$Pc4	=550.6;
    	$Pc5	=527.9;
    	$Pc6	=488.6;
    	$Pc7	=490.4;
    	$Pc8	=436.9;
    	$Pc9	=396.8;
    	$Pc10	=1300;
    	$Pc11	=1071;
    	$Pc12	=493.1;
    
    	$I30 = $Mw1+$Mw2+$Mw3+$Mw4+$Mw5+$Mw6+$Mw7+$Mw8+$Mw9+$Mw10+$Mw11+$Mw12;
    	$I31 = $I30/29;
    
    	$J = $al_0+
    	($al_1*$yi10*$Tc10/$Pc10+
    			$al_2*$yi11*$Tc11/$Pc11+
    			$al_3*$yi12*$Tc12/$Pc12)+
    			$al_4*(
    					$yi1*$Tc1/$Pc1+
    					$yi2*$Tc2/$Pc2+
    					$yi3*$Tc3/$Pc3+
    					$yi4*$Tc4/$Pc4+
    					$yi5*$Tc5/$Pc5+
    					$yi6*$Tc6/$Pc6+
    					$yi7*$Tc7/$Pc7+
    					$yi8*$Tc8/$Pc8)+
    					$al_6*$yi9*$MolWt_C7+$al_7*pow(($yi9*$MolWt_C7),2);
    
    					$K = $b_0+(
    							$b_1*$yi10*$Tc10/sqrt($Pc10)+
    							$b_2*$yi11*$Tc11/sqrt($Pc11)+
    							$b_3*$yi12*$Tc12/sqrt($Pc12)
    							)+
    							$b_4*(
    									$yi1*$Tc1/sqrt($Pc1)+
    									$yi2*$Tc2/sqrt($Pc2)+
    									$yi3*$Tc3/sqrt($Pc3)+
    									$yi4*$Tc4/sqrt($Pc4)+
    									$yi5*$Tc5/sqrt($Pc5)+
    									$yi6*$Tc6/sqrt($Pc6)+
    									$yi7*$Tc7/sqrt($Pc7)+
    									$yi8*$Tc8/sqrt($Pc8)
    									)+
    									$b_6*$yi9*$MolWt_C7+$b_7*pow(($yi9*$MolWt_C7),2);
    
    									$T_pc 	= $K*$K/$J;
    									$P_pc 	= $T_pc/$J;
    
    									$T_pr	= ($T_obs+460)/$T_pc;
    									$P_pr	= $P_obs/$P_pc;
    
    									$C_1	=$A_1+ $A_2/$T_pr+$A_3/pow($T_pr,3)+$A_4/pow($T_pr,4)+$A_5/pow($T_pr,5);
    									$C_2 	=$A_6+ $A_7/$T_pr+$A_8/pow($T_pr,2);
    									$C_3	=$A_9*($A_7/$T_pr+$A_8/pow($T_pr,2));
    
    									$z=1;
    									$ii=0;
    									while(true)
    									{
    										$ii++;
    										if($ii>100)
    											break;
    											$rho=0.27*$P_pr/($z*$T_pr);
    											$C_4=$A_10*(1+$A_11*pow($rho,2))*(pow($rho,2)/pow($T_pr,3))*exp(-$A_11*pow($rho,2));
    											$Fx=$z-(1+$C_1*$rho+$C_2*pow($rho,2)-$C_3*pow($rho,5)+$C_4);
    											$dFx=1+($C_1*$rho/$z)+(2*$C_2*pow($rho,2)/$z)-(5*$C_3*pow($rho,5)/$z)+(2*$A_10*pow($P_pr,2)*pow(0.27,2))*exp(-$A_11*pow(($P_pr*0.27/($T_pr*$z)),2))*(-1*pow($A_11,2)*pow($P_pr,4)*pow(0.27,4)+$A_11*pow($P_pr,2)*pow($T_pr,2)*pow(0.27,2)*pow($z,2)+pow($T_pr,4)*pow($z,4))/(pow($T_pr,9)*pow($z,7));
    
    											if(abs($Fx)<0.0000000000001)
    												break;
    
    												$z=$z-$Fx/$dFx;
    									}
    
    									if($ii>100)
    										return -1;
    										else
    										{
    											if($P_obs!=0)
    											{
    												$Bg=0.02827*$z*($T_obs+460)/$P_obs;
    												return $Bg;
    											}
    											else
    												return -2;
    										}
    }
    //**********************************************************
    
    public static function calculateGasNoImp(
    		$T_obs,
    		$P_obs,
    		$M_C1,
    		$M_C2,
    		$M_C3,
    		$M_C4n,
    		$M_C4i,
    		$M_C5n,
    		$M_C5i,
    		$M_C6n,
    		$M_C7,
    		$MolWt_C7,
    		$gamma_C7
    		)
    {
    	$T_b	= pow(4.5579*pow($MolWt_C7,0.15178)*pow($gamma_C7,0.15427),3);
    	$Tc_C7  = 341.7+811*$gamma_C7+(0.4244+0.1174*$gamma_C7)*$T_b+(0.4669-3.2623*$gamma_C7)*pow(10,5)/$T_b;
    	$Pc_C7	= exp(8.3634-0.0566/$gamma_C7-(0.24244+2.2898/$gamma_C7+0.11857/(pow($gamma_C7,2)))*pow(10,-3)*$T_b+(1.4685+3.648/$gamma_C7+0.47227/(pow($gamma_C7,2)))*(pow(10,-7))*pow($T_b,2)-(0.42019+1.6977/(pow($gamma_C7,2)))*(pow(10,-10))*pow($T_b,3));
    
    	$A_1=0.32650;
    	$A_2=-1.07000;
    	$A_3=-0.53390;
    	$A_4=0.01569;
    	$A_5=-0.05165;
    	$A_6=0.54750;
    	$A_7=-0.73610;
    	$A_8=0.18440;
    	$A_9=0.10560;
    	$A_10=0.61340;
    	$A_11=0.72100;
    
    	$yi1	=$M_C1/1;
    	$yi2	=$M_C2/1;
    	$yi3	=$M_C3/1;
    	$yi4	=$M_C4n/1;
    	$yi5	=$M_C4i/1;
    	$yi6	=$M_C5n/1;
    	$yi7	=$M_C5i/1;
    	$yi8	=$M_C6n/1;
    	$yi9	=$M_C7/1;
    
    	$Tc1	=$yi1*(-116.67+460);
    	$Tc2	=$yi2*(89.92+460);
    	$Tc3	=$yi3*(206.06+460);
    	$Tc4	=$yi4*(305.62+460);
    	$Tc5	=$yi5*(274.46+460);
    	$Tc6	=$yi6*(385.8+460);
    	$Tc7	=$yi7*(369.1+460);
    	$Tc8	=$yi8*(453.6+460);
    	$Tc9	=$yi9*$Tc_C7;
    
    	$Mw1	=$yi1*16.04;
    	$Mw2	=$yi2*30.07;
    	$Mw3	=$yi3*44.1;
    	$Mw4	=$yi4*58.12;
    	$Mw5	=$yi5*58.12;
    	$Mw6	=$yi6*72.15;
    	$Mw7	=$yi7*72.15;
    	$Mw8	=$yi8*86.18;
    	$Mw9	=$yi9*128;
    
    	$Pc1	=$yi1*666.4;
    	$Pc2	=$yi2*706.5;
    	$Pc3	=$yi3*616;
    	$Pc4	=$yi4*550.6;
    	$Pc5	=$yi5*527.9;
    	$Pc6	=$yi6*488.6;
    	$Pc7	=$yi7*490.4;
    	$Pc8	=$yi8*436.9;
    	$Pc9	=$yi9*$Pc_C7;
    
    	$I30 = $Mw1+$Mw2+$Mw3+$Mw4+$Mw5+$Mw6+$Mw7+$Mw8+$Mw9;
    	$I31 = $I30/29;
    
    	$T_pc 	= $Tc1+$Tc2+$Tc3+$Tc4+$Tc5+$Tc6+$Tc7+$Tc8+$Tc9;
    	$P_pc 	= $Pc1+$Pc2+$Pc3+$Pc4+$Pc5+$Pc6+$Pc7+$Pc8+$Pc9;
    
    	$T_pr	= ($T_obs+460)/$T_pc;
    	$P_pr	= $P_obs/$P_pc;
    
    	$C_1	=$A_1+ $A_2/$T_pr+$A_3/pow($T_pr,3)+$A_4/pow($T_pr,4)+$A_5/pow($T_pr,5);
    	$C_2 	=$A_6+ $A_7/$T_pr+$A_8/pow($T_pr,2);
    	$C_3	=$A_9*($A_7/$T_pr+$A_8/pow($T_pr,2));
    
    	$z=1;
    	$ii=0;
    	while(true)
    	{
    		$ii++;
    		if($ii>100)
    			break;
    			$rho=0.27*$P_pr/($z*$T_pr);
    			$C_4=$A_10*(1+$A_11*pow($rho,2))*(pow($rho,2)/pow($T_pr,3))*exp(-$A_11*pow($rho,2));
    			$Fx=$z-(1+$C_1*$rho+$C_2*pow($rho,2)-$C_3*pow($rho,5)+$C_4);
    			$dFx=1+($C_1*$rho/$z)+(2*$C_2*pow($rho,2)/$z)-(5*$C_3*pow($rho,5)/$z)+(2*$A_10*pow($P_pr,2)*pow(0.27,2))*exp(-$A_11*pow(($P_pr*0.27/($T_pr*$z)),2))*(-1*pow($A_11,2)*pow($P_pr,4)*pow(0.27,4)+$A_11*pow($P_pr,2)*pow($T_pr,2)*pow(0.27,2)*pow($z,2)+pow($T_pr,4)*pow($z,4))/(pow($T_pr,9)*pow($z,7));
    
    			if(abs($Fx)<0.0000000000001)
    				break;
    
    				$z=$z-$Fx/$dFx;
    	}
    
    	if($ii>100)
    		return -1;
    		else
    		{
    			$Bg=0.02827*$z*($T_obs+460)/$P_obs;
    			return $Bg;
    		}
    }
    //********************************************************************************************************************
    
    public static function calculateGas($T_obs, $P_obs,
    		$MolWt_C7,
    		$gamma_C7,
    		$M_C1,
    		$M_C2,
    		$M_C3,
    		$M_C4n,
    		$M_C4i,
    		$M_C5n,
    		$M_C5i,
    		$M_C6n,
    		$M_C7,
    		$M_H2S,
    		$M_CO2,
    		$M_N2
    		)
    {
    	if($M_H2S>0 || $M_CO2>0 || $M_N2>0)
    		return self::calculateGasWithImp(
    				$T_obs,
    				$P_obs,
    				$M_C1,
    				$M_C2,
    				$M_C3,
    				$M_C4n,
    				$M_C4i,
    				$M_C5n,
    				$M_C5i,
    				$M_C6n,
    				$M_C7,
    				$M_H2S,
    				$M_CO2,
    				$M_N2,
    				$MolWt_C7);
    		else
    			return self::calculateGasNoImp(
    					$T_obs,
    					$P_obs,
    					$M_C1,
    					$M_C2,
    					$M_C3,
    					$M_C4n,
    					$M_C4i,
    					$M_C5n,
    					$M_C5i,
    					$M_C6n,
    					$M_C7,
    					$MolWt_C7,
    					$gamma_C7);
    
    }
    //********************************************************************
    
public static function calculateCrudeOil($T_obs, $P_obs, $API_obs) {
		// Step 1: Check for data's validity
		if ($T_obs <= - 58.0)
			$T_obs = - 58.0;
		if ($T_obs >= 302.0)
			$T_obs = 302.0;
		if ($P_obs <= 0)
			$P_obs = 0;
		if ($P_obs >= 1500)
			$P_obs = 1500;
		
		$a_1 = - 0.148759;
		$a_2 = - 0.267408;
		$a_3 = 1.08076;
		$a_4 = 1.269056;
		$a_5 = - 4.089591;
		$a_6 = - 1.871251;
		$a_7 = 7.438081;
		$a_8 = - 3.536296;
		
		$R1 = ($T_obs - 32) / 1.8;
		$tau = $R1 / 630;
		$R9 = ($a_1 + ($a_2 + ($a_3 + ($a_4 + ($a_5 + ($a_6 + ($a_7 + $a_8 * $tau) * $tau) * $tau) * $tau) * $tau) * $tau) * $tau) * $tau;
		$R13 = $R1 - $R9;
		$R17 = 1.8 * $R13 + 32;
		
		$gamma_o = 141.5 / ($API_obs + 131.5);
		$rho_o_obs = $gamma_o * 999.016;
		if ($rho_o_obs <= 470.5)
			$rho_o_obs = 470.5;
		if ($rho_o_obs >= 1201.8)
			$rho_o_obs = 1201.8;
		
		$T_star = $R17;
		$delta_60 = 0.013749795470;
		
		// Step 2: Initial Density
		
		$C32 = $rho_o_obs;
		$C33 = 341.0957;
		$C34 = 0;
		$C35 = 0;
		
		$ii = 0;
		while ( true ) {
			$ii ++;
			if ($ii >= 100)
				break;
			
			$C36 = $delta_60 / 2 * (($C33 / $C32 + $C34) / $C32 + $C35);
			$C37 = (2 * $C33 + $C34 * $C32) / ($C33 + ($C34 + $C35 * $C32) * $C32);
			$C38 = $C32 * (1 + (exp ( $C36 * (1 + 0.8 * $C36) ) - 1) / (1 + $C36 * (1 + 1.6 * $C36) * $C37));
			$C39 = ($C33 / $C38 + $C34) / $C38 + $C35;
			$C40 = exp ( - $C39 * ($T_star - 60.0068749) * (1 + 0.8 * $C39 * ($T_star - 60.0068749 + $delta_60)) );
			$C41 = 2;
			$C42 = exp ( - 1.9947 + 0.00013427 * $T_star + (793920 + 2326 * $T_star) / ($C38 * $C38) );
			$C43 = 1 / (1 - 0.00001 * $C42 * $P_obs);
			$C44 = $C40 * $C43;
			$C45 = $C32 * $C44;
			
			$C48 = $rho_o_obs - $C45;
			$C51 = $rho_o_obs / $C44 - $C32;
			$C52 = $C41 * $C39 * ($T_obs - 60) * (1 + 1.6 * $C39 * ($T_obs - 60));
			$C53 = (2 * $C43 * $P_obs * $C42 * (7.9392 + 0.02326 * $T_obs)) / ($C32 * $C32);
			$C54 = $C51 / (1 + $C52 + $C53);
			
			$C32 = $C32 + $C54;
			
			if (abs ( $C48 ) < 0.000001) {
				$rho_60 = $C32;
				break;
			}
		}
		if ($ii >= 100)
			return - 4;
		if ($C39 <= 0.00023 || $C39 >= 0.00093)
			return - 5;
		
		$B62 = $rho_60;
		$B63 = $B62 / 999.016;
		$B64 = $C40;
		$B65 = $C42;
		$B66 = $C43;
		$B67 = $C44;
		$B68 = round ( $B67, 8 );
		
		$ret = $B68;
		return $ret;
	}
    
    //***************************************************************
    
	
	public static function calculateTankVolume($tank_id,$tank_level){
		$tank2 = StrappingTableData:: whereHas('Tank',function ($query) use ($tank_id) {
													$query->where("ID",$tank_id );
											})
					->where("STRAPPING_READING",'>=',$tank_level )
					->orderBy('STRAPPING_READING')
					->first();
		if($tank2){
			if($tank2->STRAPPING_READING==$tank_level) return $tank2->STRAPPING_READING;
			
			$tank1 = StrappingTableData:: whereHas('Tank',function ($query) use ($tank_id) {
								$query->where("ID",$tank_id );
							})
							->where("STRAPPING_READING",'<',$tank_level )
							->orderBy('STRAPPING_READING','desc')
							->first();
			if($tank1){
				return $tank1->STRAPPING_VALUE+
						(($tank2->STRAPPING_VALUE-$tank1->STRAPPING_VALUE)*($tank_level-$tank1->STRAPPING_READING)/
							($tank2->STRAPPING_READING-$tank1->STRAPPING_READING));
			}
		}
							
		/* $sSQL=
		"SELECT b.`STRAPPING_READING`,b.`STRAPPING_VALUE` FROM tank a,strapping_table_data b
		WHERE a.STRAPPING_TABLE_ID=b.STRAPPING_TABLE_ID and a.id='$tank_id'
		and STRAPPING_READING>=$tank_level
		order by STRAPPING_READING limit 1";
		$r2=getOneRow($sSQL);
		if($r2){
			if($r2["STRAPPING_READING"]==$tank_level) return $r2["STRAPPING_VALUE"];
			$sSQL=
			"SELECT b.`STRAPPING_READING`,b.`STRAPPING_VALUE` FROM tank a,strapping_table_data b
			WHERE a.STRAPPING_TABLE_ID=b.STRAPPING_TABLE_ID and a.id='$tank_id'
			and STRAPPING_READING<$tank_level
			order by STRAPPING_READING desc limit 1";
			
			$r1=getOneRow($sSQL);
			if($r1){
				//echo "$r1[STRAPPING_VALUE]+(($r2[STRAPPING_VALUE]-$r1[STRAPPING_VALUE])*($tank_level-$r1[STRAPPING_READING])/($r2[STRAPPING_READING]-$r1[STRAPPING_READING]))<br>";
				return $r1["STRAPPING_VALUE"]+(($r2["STRAPPING_VALUE"]-$r1["STRAPPING_VALUE"])*($tank_level-$r1["STRAPPING_READING"])/($r2["STRAPPING_READING"]-$r1["STRAPPING_READING"]));
			}
		} */
		return -1;
	}
	
	
	public static function getDataFormulaContract($qltyFormulas,$contractId,$year) {
		$aryMstCalcu = array ();
		$aryValue = array ();
		$x = array ();
		$contractIdGlobal = $contractId;
		$yearGlobal = $year;
		
		foreach($qltyFormulas 	as 	$key 	=> $row) {
			if ($row->LEVEL == 0) {
				$str = str_replace ( 'contract_attr(', '', $row ->FORMULA );
				$str = str_replace ( ')', '', $str );
				$str = $row->ID . "," . $str;
				$str = "$str,,$contractId,$year";
				
				$aryMstCalcu ['fn' . $row->FORMULA_NO] = call_user_func_array ( "contract_attr", explode ( ',', $str ) );
				$aryValue [$row->ID] = ( int ) $aryMstCalcu ['fn' . $row->FORMULA_NO];
			} else {
			
				$x [$row->ID] = $row->FORMULA;
			}
		}
		
		/* while ( $row = mysql_fetch_array ( $result ) ) {
			if ($row ['LEVEL'] == 0) {
				$str = str_replace ( 'contract_attr(', '', $row ['FORMULA'] );
				$str = str_replace ( ')', '', $str );
				$str = $row ['ID'] . "," . $str;
	
				$aryMstCalcu ['fn' . $row ['FORMULA_NO']] = call_user_func_array ( "contract_attr", explode ( ',', $str ) );
				$aryValue [$row ['ID']] = ( int ) $aryMstCalcu ['fn' . $row ['FORMULA_NO']];
			} else {
	
				$x [$row ['ID']] = $row ['FORMULA'];
			}
		} */
		fn ( 1, $aryMstCalcu );
		set_error_handler("evalErrorHandler");
		foreach ( $x as $kk => $vv ) {
			$vvoutput = preg_replace("/fn\(([a-z][0-9])\)/", "fn('$1')", $vv);
			try {
				eval ( '$aryValue[$kk] = (' . $vvoutput . ');' );
			} catch( Exception $e ){
				\Log::info("Exception with eval $s ".$e->getMessage());
				$aryValue[$kk] = 0;
			}
			 
		}
		restore_error_handler();
		return $aryValue;
	}
    
}
