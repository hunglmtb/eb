<?php

namespace App\Http\Controllers;
use App\Models\CfgDataSource;
use App\Models\CfgFieldProps;

use DB;
use Illuminate\Http\Request;

class FieldsConfigController extends Controller {
	
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	
	public function _index() {
		$cfg_data_source = collect(CfgDataSource::all('NAME')->toArray());
		return view ( 'front.fieldsconfig',[
				'cfg_data_source' 		=> $cfg_data_source,
		]);
	}
	
	public function getColumn(Request $request){
		$data = $request->all ();	
		
		$getFields = $this->getFields($data['table']);
		$getFieldsEffected = $this->getFieldsEffected($data['table']);
		$dcEnable = 0;
		$tmp = CfgDataSource::where(['NAME'=>$data['table']])->select(['ENABLE_DC'])->first();
		if($tmp){
			$dcEnable = $tmp["ENABLE_DC"];
		}
		
		return response ()->json ( ['getFields' => $getFields, 'getFieldsEffected'=>$getFieldsEffected, 'dcEnable' => $dcEnable] );
	}
	
	public function saveEnableDC(Request $request){
		$data = $request->all ();	
		CfgDataSource::where(['NAME'=>$data['table']])->update(['ENABLE_DC'=>$data['enable_dc']]);
		return response ()->json ('OK');
	}
	
	private function getFields($table) {
		$cfg_field_props = collect($this->getFieldsEffected($table)->toArray());
		
		//\DB::enableQueryLog ();
		$tmps = DB::table ('INFORMATION_SCHEMA.COLUMNS')
		->where ( ['TABLE_NAME' => $table] )
		->whereNotIn('COLUMN_NAME', $cfg_field_props)
		->distinct ()
		->select ('COLUMN_NAME')->get();
		//\Log::info ( \DB::getQueryLog () );
		
		return $tmps;
	}	
	
	private function getFieldsEffected($table) {
		$result = CfgFieldProps::where(['TABLE_NAME'=>$table])
		->orderBy('FIELD_ORDER')->get(['COLUMN_NAME']);
		
		return $result;
	}
	
	public function saveconfig(Request $request){
		$vdata = $request->all ();
		$table = $vdata['table'];
		$data = $vdata['data'];		
		
		$i=1;
		$fields=explode(",",$data);
		foreach($fields as $field)
		{
			if($field)
			{
				$data_type = DB::table ('INFORMATION_SCHEMA.COLUMNS')
				->where ( ['TABLE_NAME' => $table, 'COLUMN_NAME'=>$field] )
				->select ('DATA_TYPE')->first(); 
				
				$type = $this->getInputType($data_type->DATA_TYPE);
				
				$re_exist = CfgFieldProps::where(['COLUMN_NAME'=>$field, 'TABLE_NAME'=>$table])->get(['COLUMN_NAME']);
				if(count($re_exist)!=0){
					CfgFieldProps::where(['COLUMN_NAME'=>$field, 'TABLE_NAME'=>$table])->update(['FIELD_ORDER'=>$i]);
				}else{
					CfgFieldProps::insert(['TABLE_NAME'=>$table, 'COLUMN_NAME'=>$field, 'FIELD_ORDER'=>$i, 'DATA_METHOD'=>$type, 'INPUT_TYPE'=>1, 'INPUT_ENABLE'=>1]);
				}
				$i++;
			}
		}
		
		//Xoa
		CfgFieldProps::where(['TABLE_NAME'=>$table])->whereNotIn('COLUMN_NAME',$fields)->delete();
		/* $re_full = CfgFieldProps::where(['TABLE_NAME'=>$table])->get(['COLUMN_NAME']);
		foreach ($re_full as $row_full)
		{
			$del = true;
			foreach ( $fields as $field ) {
				if ($row_full->COLUMN_NAME == $field) {
					$del = false;
					break;
				}
			}
			if ($del == true) {
				CfgFieldProps::where(['TABLE_NAME'=>$table, 'COLUMN_NAME'=>$row_full->COLUMN_NAME]);
			}
		} */
		
		return response ()->json ( 'ok' );
	}
	
	private function getInputType($dataType)
	{
		switch($dataType){
			case "varchar":
			case "text":
			case "char":
				return 1;				//Text input
			case "int":
			case "decimal":
			case "tinyint":
			case "float":
				return 2;				//Number input
			case "date":
				return 3;				//Date picker
			case "time":
				return 4;				//Date picker
			case "datetime":
				return 4;				//Datetime picker
		}
	}
	
	public function chckChange(Request $request){
		$data = $request->all ();
		$str = "";
		$tbl=$data['chk_tbl'];
		$vie=$data['chk_vie'];
		
		if($tbl=='true' and $vie=='true')
		{
			$w=-1;
		}
		else
		{
			if($vie=='true')
				$w=0;
				else if($tbl=='true')
					$w=1;
		};
		
		$re_tbl = [];
		if($w != -1){
			$re_tbl = CfgDataSource::where(['SRC_TYPE'=>$w])->orderBy('NAME')->get(['NAME']);
		}else{
			$re_tbl = CfgDataSource::orderBy('NAME')->get(['NAME']);
		}
		
		foreach ($re_tbl as $row_tbl)
		{
			$str .= "<option value='".$row_tbl->NAME."'>".$row_tbl->NAME."</option>";
		}
		
		return response ()->json ($str);
	}
	
	public function getprop(Request $request){
		$data 					= $request->all ();
		$table 					= $data['table'];
		$field 					= $data['field_effected'];
		$respondData			= CfgFieldProps::getFieldProperties($table,$field);
		return response ()->json ($respondData);
	}
	
	public function putValue(&$param,$data,$field){
		if (isset ( $data [$field] ))
			$param [$field] = $data [$field]==''?null:$data [$field];
	}
	
	public function saveprop(Request $request){
		$data = $request->all ();
		
		$vfield = $data ['field'];
		$fields = explode ( ",", $vfield );
		$table = $data ['table'];
		
		$param = [ ];
		if (isset ( $data ['data_method'] )) {
			$param ['DATA_METHOD'] = $data ['data_method'];
			$param ['INPUT_ENABLE'] = $data ['data_method'];
		}
		if (isset ( $data ['VALUE_FORMAT'] )) {
			$param ['VALUE_FORMAT'] = $data ['VALUE_FORMAT'];
		}
		$this->putValue($param,$data,'FORMULA');
		$this->putValue($param,$data,'INPUT_TYPE');
		$this->putValue($param,$data,'VALUE_FORMAT');
		$this->putValue($param,$data,'VALUE_MAX');
		$this->putValue($param,$data,'VALUE_MIN');
		$this->putValue($param,$data,'VALUE_WARNING_MAX');
		$this->putValue($param,$data,'VALUE_WARNING_MIN');
		$this->putValue($param,$data,'RANGE_PERCENT');
		$this->putValue($param,$data,'FDC_WIDTH');
		
		if (isset ( $data ['friendly_name'] ) && count ( $fields ) == 1)
			$param ['LABEL'] = $data ['friendly_name'];
		
		$objectExtension = isset ( $data ['objectExtension'] )&&count($data ['objectExtension'])>0?json_encode($data ['objectExtension']):null;
		$param['USE_FDC'] = $data ['us_data'];
		$param['USE_DIAGRAM'] = $data['us_sr'];
		$param['USE_GRAPH'] = $data['us_gr'];
		$param['IS_MANDATORY'] = $data['is_mandatory'];
		$param['OBJECT_EXTENSION'] = $objectExtension;
	
			//\DB::enableQueryLog ();
		CfgFieldProps::where(['TABLE_NAME'=>$table])->whereIn('COLUMN_NAME', $fields)->update($param);
		//\Log::info ( \DB::getQueryLog () );
		
		return response ()->json ('OK');
	}
}