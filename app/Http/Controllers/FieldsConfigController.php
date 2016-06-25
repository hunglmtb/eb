<?php

namespace App\Http\Controllers;
use App\Models\CfgDataSource;
use App\Models\CfgFieldProps;
use App\Models\CodeDataMethod;
use App\Models\CfgInputType;

use DB;
use Illuminate\Http\Request;

class FieldsConfigController extends Controller {
	
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	
	public function _index() {
		$cfg_data_source = collect(CfgDataSource::all('NAME')->toArray());
		$code_data_method = CodeDataMethod::where(['ACTIVE'=>1])->orderBy('ORDER')->get(['ID', 'NAME']);
		$cfg_input_type = CfgInputType::all('ID', 'NAME');
		
		return view ( 'front.fieldsconfig',[
				'cfg_data_source' =>$cfg_data_source,
				'code_data_method'=>$code_data_method,
				'cfg_input_type'=>$cfg_input_type
		]);
	}
	
	public function getColumn(Request $request){
		$data = $request->all ();	
		
		$getFields = $this->getFields($data['table']);
		$getFieldsEffected = $this->getFieldsEffected($data['table']);
		
		return response ()->json ( ['getFields' => $getFields, 'getFieldsEffected'=>$getFieldsEffected] );
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
		$re_full = CfgFieldProps::where(['TABLE_NAME'=>$table])->get(['COLUMN_NAME']);
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
		}
		
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
		$data = $request->all ();
		$table = $data['table'];
		$field = $data['field_effected'];
		//\DB::enableQueryLog ();
		$re_prop = CfgFieldProps::where(['TABLE_NAME'=>$table, 'COLUMN_NAME'=>$field])->select('*')->get();
		//\Log::info ( \DB::getQueryLog () );
		return response ()->json ($re_prop);
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
		
		if (isset ( $data ['formula'] ))
			$param ['FORMULA'] = $data ['formula'];
		
		if (isset ( $data ['input_type'] ))
			$param ['INPUT_TYPE'] = $data ['input_type'];
		
		if (isset ( $data ['data_format'] ))
			$param ['VALUE_FORMAT'] = $data ['data_format'];
		
		if (isset ( $data ['max_value'] ))
			$param ['VALUE_MAX'] = $data ['max_value'];
		
		if (isset ( $data ['min_value'] ))
			$param ['VALUE_MIN'] = $data ['min_value'];
		
		if (isset ( $data ['fdc_width'] ))
			$param ['FDC_WIDTH'] = $data ['fdc_width'];
		
		if (isset ( $data ['friendly_name'] ) && count ( $fields ) == 1)
			$param ['LABEL'] = $data ['friendly_name'];
		
		$param ['USE_FDC'] = $data ['us_data'];
		$param['USE_DIAGRAM'] = $data['us_sr'];
		$param['USE_GRAPH'] = $data['us_gr'];
		$param['IS_MANDATORY'] = $data['is_mandatory'];
	
			//\DB::enableQueryLog ();
		CfgFieldProps::where(['TABLE_NAME'=>$table])->whereIn('COLUMN_NAME', $fields)->update($param);
		//\Log::info ( \DB::getQueryLog () );
		
		return response ()->json ('OK');
	}
}