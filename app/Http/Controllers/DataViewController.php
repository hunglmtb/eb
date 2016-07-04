<?php

namespace App\Http\Controllers;
use App\Models\SqlList;
use App\Models\SqlConditionFilter;

use Illuminate\Http\Request;
use DB;
use App\Models\User;
use Excel;

class DataViewController extends Controller {
	
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	
	public function _index() {
		
		$viewslist = SqlList::where(['ENABLE'=>1, 'TYPE'=>2])->get(['ID', 'NAME']);
		
		$sqllist = $this->getSqlList();		
		$user = auth()->user();
		$role = $user->hasRole('ADMIN');
		return view ( 'front.dataview', ['viewslist'=>$viewslist, 'sqllist'=>$sqllist, '_role'=>$role]);
	}
	
	private function getSqlList(){
		$sqllist = SqlList::where(['ENABLE'=>1])
		->where ( function ($q) {
			$q->whereNull('TYPE');
			$q->orWhere ( [
					'TYPE' => 0
			] );
		} )
		->get(['ID', 'NAME']);
		
		return $sqllist;
	}
	
	public function getsql(Request $request) {
		$data = $request->all ();
		
		$sql1 = SqlList::where(['ID'=>$data['id']])->select('SQL')->first();
		
		return response ()->json ( $sql1->SQL );
	}
	
	public function loaddata(Request $request) {
		$data = $request->all ();
		$str = "";
		$option = "";
		if(isset($data['sql']))
		{
			$sql = trim($data['sql']);
			//$sql = strip_tags(trim($data['sql']));
		
			if(strtoupper(substr($sql,0,6))=="SQLID:")
			{
				$ss=explode(";",$sql);
				$id=substr($ss[0],6);
				$tmp = SqlList::where(['ID'=>$id])->select('SQL')->first();
				$sql = $tmp->SQL;
				for($i=1;$i<count($ss);$i++)
					if($ss[$i])
					{
						$xs=explode(":",$ss[$i]);
						if(count($xs)>=2)
						{
							$lastchar=substr($xs[1],-1);
							$last2char=substr($xs[1],-2);
							if($lastchar=='=' || $lastchar=='<' || $lastchar=='>' || $last2char=="''")
								$xs[1]="true";
							$sql = str_replace($xs[0],$xs[1],$sql);
						}
					}
			}
			else if(strtoupper(substr($sql,0,6))!=="SELECT")
			{
				$str .= "<table border='0' id='data' class='display compact'><thead><tr><th>Error</th></tr></thead><tr><td>Only accept SELECT statement</td></tr></table>";
				exit();
			}
		
			$page=$data['page'];
			$rows_in_page=$data['rows_in_page'];
			try{
				$total_row = DB::select($sql);
			}catch (\Exception $exp){
				return response ()->json ( $exp->getMessage() );
			}
			
			$total_row = count($total_row); 	//Total record
			$total_page = ceil($total_row/$rows_in_page);			//Toal page
		
			$sql_export=$sql;
			$sql.=" LIMIT ".(($page-1)*$rows_in_page).", ".$rows_in_page;
			
			$re = DB::select($sql);
			if(count($re) <= 0) return response ()->json ( $str );
			$fields=collect($re[0])->toArray();
		
			$occur_date_exist=false;
			$str .= "<table border='0' id='data' class='display compact'><thead><tr>";
			$keys = "";
			foreach($fields as $key => $value)
			{
				$str .= "<th>".$key."</th>";
				$keys .= $key.",";
			}
			$str .= "</tr></thead>";
			$index = explode(",",$keys);
			
			foreach ($re as $ro)
			{
				$str .= "<tr>";
				for($i=0; $i<count($index)-1; $i++)
				{
					$str .= "<td>".$ro->$index[$i]."</td>";
				}
				$str .= "</tr>";
			}
		}
		else{
			$view_name=$data['view_name'];
			$page=$data['page'];
			$object_id=($data['object']==""? NULL: $data['object']);
			$from_date=($data['from_date']==""? NULL: $data['from_date']);
		
			$to_date=($data['to_date']==""? NULL: $data['to_date']);
			$rows_in_page=$data['rows_in_page'];;
		
			//Check existing
			$occur_date_exist=check_exist_field("OCCUR_DATE", $view_name, 0, NULL);
			$flow_exist=check_exist_field("FLOW_ID", $view_name, 1, $object_id);
			$eu_exist=check_exist_field("EU_ID", $view_name, 1, $object_id);
			$option=($flow_exist!=false? $flow_exist: ($eu_exist!=false? $eu_exist: ""));
		
			if($object_id!=NULL and $object_id !=-1)
			{
				$temp="";
				foreach($object_id as $obj)
				{
					$temp.=($temp? ",": "")."'$obj'";
				}
				$cond=($flow_exist==true? "FLOW_ID IN (".$temp.")": ($eu_exist==true? "EU_ID IN (".$temp.")": ""));
			}
			$cond.=($cond? ($from_date? " AND OCCUR_DATE >= STR_TO_DATE('".$from_date."', '%m/%d/%Y')": ""): ($from_date? "OCCUR_DATE >= STR_TO_DATE('".$from_date."', '%m/%d/%Y')": ""));
			$cond.=($cond? ($to_date? " AND OCCUR_DATE <= STR_TO_DATE('".$to_date."', '%m/%d/%Y')": ""): ($to_date? "OCCUR_DATE <= STR_TO_DATE('".$to_date."', '%m/%d/%Y')": ""));
			$cond=($cond? "WHERE ": "").$cond;
		
			$sql_export="SELECT * FROM ".$view_name." ".$cond;
			$sql="SELECT * FROM ".$view_name." ".$cond." LIMIT ".(($page-1)*$rows_in_page).", ".$rows_in_page;
			$re=mysql_query($sql) or die("Error: ".mysql_error());
		
			$total_row = mysql_num_rows(mysql_query("SELECT * FROM ".$view_name." ".$cond)); 	//Total record
			$total_page = ceil($total_row/$rows_in_page);			//Toal page
		
			$s_field="SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$view_name."'";
			$re_field=mysql_query($s_field) or die("Error: ".mysql_error());
				
			echo "
		<table border='0' id='data' class='display compact'>
		<thead><tr>";
			while($ro_field=mysql_fetch_array($re_field))
			{
				echo ("<th>".$ro_field['COLUMN_NAME']."</th>");
			}
			echo "</tr></thead>";
		
			while($ro=mysql_fetch_array($re))
			{
				echo "<tr>";
				mysql_data_seek($re_field, 0);
				while($ro_field=mysql_fetch_array($re_field))
				{
					echo "<td>".$ro[$ro_field['COLUMN_NAME']]."</td>";
				}
				echo "</tr>";
			}
		}
		$str .= "</table><div id='paging'>
    You are here: ";
		$page_list = "";
		$skip=false;
		for($i=1; $i<=$total_page; $i++)
		{
			if(($i-$page>5 || $page-$i>5) && $i<$total_page-1 && $i>2)
			{
				$skip=true;
				continue;
			}
			if($skip==true) $page_list.="...";
			$page_list.=($page_list? "-": "")."<span page='".$i."' ".($i==$page? "class='current_page'": "").">".($i==$page?"[<b>".$i."</b>]": $i)."</span>";
			$skip=false;
		}
		$str .= $page_list."<input type='text' value='' size='4' id='txtpage'><input type='button' value='Go' id='go'></div>
	<div style='display:none'>
		<span id='sql_export'>".$sql_export."</span>
		<span id='occurdate_exist'>".$occur_date_exist."</span>
		<span id='option'>".$option."</span>
	<div>";
		
		return response ()->json ( $str );
	}
	
	private function check_exist_field($field, $table_view, $option, $selected)
	{
		$field_num = DB::statement("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='".$table_view."' AND COLUMN_NAME='".$field."'");
		/* $field_num=mysql_num_rows(mysql_query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='".$table_view."' AND COLUMN_NAME='".$field."'"));
		if($field_num==1)		//Exist
		{
			if($option==1)
			{
				$re=mysql_query("SELECT id, name FROM ".($field=='EU_ID'? "energy_unit": "flow")) or die("Error: ".mysql_error());
				while($ro=mysql_fetch_array($re))
				{
					$option_s.="<option value='".$ro[id]."' ".($selected? ($selected==-1? "selected": (in_array($ro[id], $selected)? "selected": "")): "").">".$ro[name]."</option>";
				}
				return $option_s;
			}
			else if($option==0)
				return true;
		}
		else
			return false; */
	}
	
	public function checkSQL(Request $request) {
		$data = $request->all ();
		
		$ret = SqlConditionFilter::where(['SQL_ID'=>$data['id']])->orderBy('ORDER')->select('*')->get();
		$html="";
		foreach ($ret as $row)
		{
			$html.="<div style='margin:5px;font-size:13px'><span class='condition_field' filed_name='$row->FIELD_NAME' IS_DATE_RANGE='$row->IS_DATE_RANGE' FIELD_VALUE_REF_TABLE='$row->FIELD_VALUE_REF_TABLE' style='margin-bottom:8px;font-weight:bold'>".($row->LABEL?$row->LABEL:$row->FIELD_NAME)."</span>";
			if($row->IS_DATE_RANGE=="1")
			{
				$html.=
				" From <input class='datepicker' style='width:80px' id='$row->FIELD_NAME"."_FROM' name='$row->FIELD_NAME"."_FROM'>
				To <input class='datepicker' style='width:80px' id='$row->FIELD_NAME"."_TO' name='$row->FIELD_NAME"."_TO'>
				";
			}
			else if($row->FIELD_VALUE_REF_TABLE)
			{
				$table_name = $row->FIELD_VALUE_REF_TABLE;
				$entity = strtolower(str_replace('_', ' ', $table_name));
				$entity = ucwords($entity);
				$entity = str_replace(' ', '', $entity);
					
				$model = 'App\\Models\\' .$entity;
				
				$childload="";
				if($row->CHILD_LOAD){
					$childload="onchange=_dataview.childLoad('$row->CHILD_LOAD','$row->FIELD_NAME',this)";
				}
				$result = $model::all(['ID', 'NAME']);
				$options = "";
				$options .= "<option value='0'></option>";
				foreach ($result as $row1){
					$options .= "<option value='$row1->ID'>$row1->NAME</option>";
				}
				$html.=" <select $childload id='$row->FIELD_NAME"."_SELECT' table='$row->FIELD_VALUE_REF_TABLE' field='$row->FIELD_NAME'>$options</select>";
			}
			$html.="</div>";
		}
		$str = "";
		if($html) $str = "filter:$html";
			return response ()->json ( $str );
			
		}
	
	public function deletesql(Request $request) {
		$data = $request->all ();
		
		SqlList::where(['ID'=>$data['id']])->update(['ENABLE'=>0]);
		
		$sqllist = $this->getSqlList();
		
		return response ()->json ( $sqllist );
	}
	
	public function downloadExcel($sql)
	{
		
		if(strtoupper(substr($sql,0,6))=="SQLID:")
		{
			$ss=explode(";",$sql);
			$id=substr($ss[0],6);
			$tmp = SqlList::where(['ID'=>$id])->select('SQL')->first();
			$sql = $tmp->SQL;
			for($i=1;$i<count($ss);$i++)
				if($ss[$i])
				{
					$xs=explode(":",$ss[$i]);
					if(count($xs)>=2)
					{
						$lastchar=substr($xs[1],-1);
						$last2char=substr($xs[1],-2);
						if($lastchar=='=' || $lastchar=='<' || $lastchar=='>' || $last2char=="''")
							$xs[1]="true";
							$sql=str_replace($xs[0],$xs[1],$sql);
					}
				}
		
		
			$tmp = DB::select($sql);
			$data = [];
			foreach ($tmp as $t){
				$t = collect($t)->toArray();
				array_push($data, $t);
			}
			return Excel::create('export', function($excel) use ($data) {
				$excel->sheet('mySheet', function($sheet) use ($data)
				{
					$sheet->fromArray($data);
				});
			})->download('xlsx');
		}
	}
	
	public function savesql(Request $request) {
		$data = $request->all ();
		
		$name=addslashes($data['name']);
		$sql=addslashes($data['sql']);
		$id=$data['id'];
		
		if($id>0){
			SqlList::where(['ID'=>$id])->update(['SQL'=>$sql]);
		}else{
			SqlList:: insert(['SQL'=>$sql, 'NAME'=>$name]);
		}
		return response ()->json ( $this->getSqlList() );
	}
}