<?php
use App\Models\CfgFieldProps;
use App\Models\Formula;
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
	    		return $updateRecords;
     		}
    	}
    	return true;
    }
    
    public static function applyFormula($mdlName,$objectIds,$occur_date,$object_type){
    	
//     	global $object_id,$flow_phase,$occur_date,$facility_id;
    	$mdl = "App\Models\\$mdlName";
    	
    	$upids = [];
    	foreach($objectIds as $object_id){
	    	$formulas = self::getFormulatedFields($mdl::getTableName(),$object_id,$object_type,$occur_date);
	    	$values = [];
	    	foreach($formulas as $formula){
// 	    		$temp_value="'".doFormulaObject($tablename, $field, $object_type, $object_id, $occur_date)."'";
				$v=self::evalFormula($formula,$occur_date);
	    		if ($v) $values[$formula->VALUE_COLUMN]=$v;
	    	}
	    	
	    	if (count($values)>0) {
	    		$updateRecords = $mdl::where('OCCUR_DATE',$occur_date)
							    		->where('flow_id',$object_id)
							    		->update($values);
    			$upids[] = $object_id;
	    	}
    	}
    	return $upids;
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
    	if($occur_date)
    	{
    		$sdate=explode("/",$occur_date);
    		if(sizeof($sdate)>=3)
    			$occur_date=$sdate[2]."-".$sdate[0]."-".$sdate[1];
    	}
    	 
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
	    							if($whereItem[0]=="OCCUR_DATE"){
	    								$whereItem[2] = $occur_date;
	    							}
	    							$where[]=$whereItem;
	    							//echo "param: $pp<br>";
    							}
    						}
    						$sql .= " limit 100";
//     						\DB::enableQueryLog();
    						$getDataResult = DB::table($table)->where($where)->select($field)->skip(0)->take(100)->get();
//     						\Log::info(\DB::getQueryLog());
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
    							
    							$sqlvalue= $getDataResult->toArray();
    							$sqlarray=array();
    							for($k=0;$k<$num_rows;$k++)
    							{
    								foreach ($sqlvalue[$k] as $key => $value)
    								{
    									if(is_numeric($key))
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
    	foreach($vvv as $v)
    	{
    		//$f=str_replace($v,$vars[$v],$f);
    		if(!$vars[$v])
    			$f=str_replace($v,"0",$f);
    			else
    				$f=str_replace($v,$vars[$v],$f);
    				//if() echo "$f<br>";
    	}
    	$f=str_replace("@DATE",$CURRENT_DATE,$f);
    
    	$s='$vf = '.$f.";";
    	if(!(self::php_syntax_error($s)))
    	{
    		eval($s);
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
    
    
    public static function evalFormula_bak($formulaRow,$occur_date, $show_echo = false){
    	if(!$formulaRow)
    	{
    		if($show_echo) echo "<span style='color:red'><i>Formula is out of date range</i></span>";
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
    	if($occur_date)
    	{
    		$sdate=explode("/",$occur_date);
    		if(sizeof($sdate)>=3)
    			$occur_date=$sdate[2]."-".$sdate[0]."-".$sdate[1];
    	}
    	
    	foreach($foVars as $row ){
    	
    		if($show_echo) echo "Processing $row->NAME=$row->STATIC_VALUE ...<br>";
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
    				if($show_echo) echo "<span style='color:blue'><i>";
    				$s='$'.$row->NAME."='$row->STATIC_VALUE';\$vs=\$$row->NAME;";
    				eval($s);
    				if($show_echo) echo "$row->NAME = $vs";
    				if($show_echo) echo "</i></span>";
    				if($show_echo) echo "<br>";
    				$vars[$row->NAME]=$vs;
    			}
    			else if(is_numeric($row->STATIC_VALUE))
    			{
    				if($show_echo) echo "<span style='color:blue'><i>";
    				$s='$'.$row->NAME."=$row->STATIC_VALUE;\$vs=\$$row->NAME;";
    				eval($s);
    				if($show_echo) echo "$row->NAME = $vs";
    				if($show_echo) echo "</i></span>";
    				if($show_echo) echo "<br>";
    				$vars[$row->NAME]=$vs;
    			}
    			else if(strpos($row->STATIC_VALUE,"[")>0)
    			{
    				$i=strpos($row->STATIC_VALUE,"[");
    				$j=strpos($row->STATIC_VALUE,"]",$i);
    				if($j>$i)
    				{
    					if($show_echo) echo "<span style='color:blue'><i>";
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
    						if($show_echo) echo "$row->NAME = $vs";
    						$vars[$row->NAME]=$vs;
    					}
    					if($show_echo) echo "</i></span>";
    					if($show_echo) echo "<br>";
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
    					if($show_echo) echo "<span style='color:blue'><i>";
    					//				if($show_echo) echo $s."<br>";
    					if($show_echo) echo $vs;
    					if($show_echo) echo "</i></span>";
    					if($show_echo) echo "<br>";
    					$vars[$row->NAME]=$vs;
    				}
    				//$s="$m = file_get_contents('http://energybuilder.co/eb/matlab/test.php?act=get&a=1%204%202', true);
    			}
    			else if(substr($row->STATIC_VALUE,0,7)=="getData")
    			{
    				if($row->TABLE_NAME && $row->VALUE_COLUMN)
    				{
    					if($show_echo) echo "<span style='color:blue'><i>";
    					$j=strpos($row->STATIC_VALUE,"(");
    					$k=self::findClosedSign(")",$row->STATIC_VALUE,$j);
    
    					if($k>$j && $j>0)
    					{
    						$table=$row->TABLE_NAME;
    						$field=$row->VALUE_COLUMN;
    						$sql="select $field from `$table` where 1";
    						//echo "field: $field<br>";
    						$params=explode(",",substr($row->STATIC_VALUE,$j+1,$k-$j-1));
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
    								if($ps[1]=="@DATE")
    									$pp = "$ps[0] $deli $CURRENT_DATE";
    									else if (is_numeric($vars[$ps[1]]))
    										$pp = "$ps[0] $deli ".$vars[$ps[1]]."";
    										else{
    											if(isset($vars[$ps[1]]))
    												$pp = "$ps[0] $deli '".$vars[$ps[1]]."'";
    												else
    													$pp = "$ps[0] $deli $ps[1]";
    										}
    										$sql.=" and $pp";
    										//echo "param: $pp<br>";
    							}
    						}
    						$sql .= " limit 100";
    						if($show_echo) echo "sql=$sql<br>";
    
    						$rrr=mysql_query($sql) or die("fail: ".$sql."-> error:".mysql_error());
    						$num_rows = mysql_num_rows($rrr);
    
    						//$sqlvalue=getOneRow($sql);
    						if($num_rows==0)
    						{
    							$s='$'.$row->NAME."='null';\$vs=\$$row->NAME;";
    							eval($s);
    							if($show_echo) echo "$row->NAME = $vs";
    						}
    						else if($num_rows==1)
    						{
    							$sqlvalue=mysql_fetch_array($rrr);
    							if(count($sqlvalue)<=2)
    							{
    								$s='$'.$row->NAME."='$sqlvalue[0]';\$vs=\$$row->NAME;";
    								eval($s);
    								if($show_echo) echo "$row->NAME = $vs";
    							}
    							else
    							{
    								$sqlarray=array();
    								foreach ($sqlvalue as $key => $value)
    								{
    									if(is_numeric($key))
    										$sqlarray[$key]=$value;
    										if($show_echo) echo "$row->NAME[$key]=".$sqlvalue[$key]."<br>";
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
    							$sqlvalue=array();
    							while($rox=mysql_fetch_array($rrr))
    							{
    								$sqlvalue[]=$rox;
    							}
    							$sqlarray=array();
    							for($k=0;$k<$num_rows;$k++)
    							{
    								foreach ($sqlvalue[$k] as $key => $value)
    								{
    									if(is_numeric($key))
    										$sqlarray[$k][$key]=$value;
    								}
    								//for($i=0;$i<count($sqlvalue[$k])/2;$i++)
    								//{
    								//	$sqlarray[$k][]=$sqlvalue[$k][$i];
    								//}
    							}
    							if($show_echo) echo "$row->NAME is an array with $num_rows rows";
    							$s='$'.$row->NAME.'=$sqlarray;'."\$vs=\$$row->NAME;";
    							eval($s);
    						}
    
    						if($show_echo) echo "</i></span>";
    						if($show_echo) echo "<br>";
    						$vars[$row->NAME]=$vs;
    					}
    					if($show_echo) echo "</i></span>";
    				}
    			}
    			else
    			{
    				$v=$row->STATIC_VALUE;
    				$i=strpos($v,".");
    				if($i>0)
    				{
    					if($show_echo) echo "<span style='color:blue'><i>";
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
    						if($show_echo) echo "sql=$sql<br>";
    						$sqlvalue=getOneValue($sql);
    						$s='$'.$row->NAME."='$sqlvalue';\$vs=\$$row->NAME;";
    						eval($s);
    						if($show_echo) echo "$row->NAME = $vs";
    						if($show_echo) echo "</i></span>";
    						if($show_echo) echo "<br>";
    						$vars[$row->NAME]=$vs;
    					}
    					if($show_echo) echo "</i></span>";
    				}
    			}
    	}
    
    	/* while($row=mysql_fetch_array($result))
    	{} */
    	if($show_echo) echo "Processing final expression ...<br>";
    	$f=$formula;
    	foreach($vvv as $v)
    	{
    		//$f=str_replace($v,$vars[$v],$f);
    		if(!$vars[$v])
    			$f=str_replace($v,"0",$f);
    			else
    				$f=str_replace($v,"$".$v,$f);
    				//if($show_echo) echo "$f<br>";
    	}
    	$f=str_replace("@DATE",$CURRENT_DATE,$f);
    
    	if($show_echo) echo "<span style='color:blue'><i>";
    	$s='$vf = '.$f.";";
    	if($show_echo) eval("echo \"".$f."\".'<br>';");
    	if(!php_syntax_error($s))
    	{
    		eval($s);
    		if($show_echo) echo "<b>Final result: $vf<b></i></span>";
    		if($show_echo) echo "<br>";
    	}
    	else
    	{
    		if($show_echo) echo "<span style='color:red'>Syntax error in final expression $s </span></i></span>";
    		$vf=null;
    	}
    
    	return $vf;
    }
    
    
}
