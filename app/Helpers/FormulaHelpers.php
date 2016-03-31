<?php
use App\Models\CfgFieldProps;
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
		      	\DB::enableQueryLog();
				$updateRecords = $mdl::whereIn($keyfield,$keyvalues)->update($values);
				\Log::info(\DB::getQueryLog());
	    		return $updateRecords;
     		}
    	}
    	return true;
    }
}
