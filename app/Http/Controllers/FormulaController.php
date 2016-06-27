<?php

namespace App\Http\Controllers;
use App\Models\Formula;
use App\Models\FoGroup;
use App\Models\FoVar;
use App\Models\Facility;
use App\Models\LoArea;


use Illuminate\Http\Request;
use DB;

class FormulaController extends Controller {
	
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	
	public function _index() {
		
		$fo_group = $this->getFoGroup();
		/* $code_plan_type = CodePlanType::all(['ID', 'NAME']);
		$code_alloc_type = CodeAllocType::all(['ID', 'NAME']); */
		
		return view ( 'front.formula', ['fo_group'=>$fo_group]);
	}
	
	public function editGroupName(Request $request){
		$data = $request->all ();	
		
		FoGroup::where(['ID'=>$data['id']])->update(['NAME'=>$data['groupName']]);
		
		$fo_group = $this->getFoGroup();
		
		return response ()->json ( $fo_group );
	}
	
	private function getFoGroup(){
		return FoGroup::all(['ID', 'NAME']);
	}
	
	public function addGroupName(Request $request){
		$data = $request->all ();
	
		FoGroup::insert(['NAME'=>$data['groupName']]);
	
		$fo_group = $this->getFoGroup();
	
		return response ()->json ( $fo_group );
	}
	
	public function deleteGroup(Request $request){
		$data = $request->all ();
		
		DB::beginTransaction ();
		try {
			//\DB::enableQueryLog ();
			FoVar::whereIn('FORMULA_ID', function($query) use ($data)
		    {
		        $query->select('ID')
		              ->from('FORMULA')
		              ->where(['GROUP_ID' => $data['id']]);
		    })->delete();
			//\Log::info ( \DB::getQueryLog () );
			
		    Formula::where(['ID'=>$data['id']])->delete();
		    
		    FoGroup::where(['ID'=>$data['id']])->delete();
		
			$fo_group = $this->getFoGroup();
		} catch ( \Exception $e ) {
			\Log::info ( $e->getMessage() );
			DB::rollback ();
		}
			
		DB::commit ();
	
		return response ()->json ( $fo_group );
	}
	
	public function getformulaslist(Request $request){
		$data = $request->all ();

		$formula = Formula::where(['GROUP_ID'=>$data['group_id']])
		->orderBy('ID')->select('*')->get();

		/* $result = [];
		foreach ($formula as $row)
		{
			$r1 = [];
			$table_name=$row->OBJECT_TYPE;
			$entity = strtolower(str_replace('_', ' ', $table_name));
			$entity = ucwords($entity);
			$entity = str_replace(' ', '', $entity);
			
			$model = 'App\\Models\\' .$entity;
			$tablea = $model::getTableName ();
			$facility = Facility::getTableName ();
			$lo_area = LoArea::getTableName ();
			
			$key = ','.$row->OBJECT_ID.',';
			$tmp = DB::table ( $tablea . ' AS a' )
			->join ( $facility . ' AS b', 'b.ID', '=', 'a.FACILITY_ID' )
			->join ( $lo_area . ' AS c', 'c.ID', '=', 'b.AREA_ID' )
			->where('a.ID', 'like', $key ) // concat(',','$row[OBJECT_ID]',',') like concat('%,',a.ID,',%')
			->select('a.NAME AS OBJECT_NAME', 'a.FACILITY_ID', 'b.AREA_ID', 'c.PRODUCTION_UNIT_ID')
			->get();
			
			$sLO="";
			$rowLO = [];
			foreach ($tmp as $rowXO)
			{
				$rowLO=$rowXO;
				$sLO.=($sLO==""?"":"<br>").$rowLO->OBJECT_NAME;
			}
			$r1['PRODUCTION_UNIT_ID'] = $rowLO['PRODUCTION_UNIT_ID'];
			$r1['PRODUCTION_UNIT_ID1'] =$rowLO['PRODUCTION_UNIT_ID'];
			$r1['PRODUCTION_UNIT_ID2'] =$rowLO['PRODUCTION_UNIT_ID'];
			array($row, $r1);
			
			/* 
		
			$i++;
			if($i % 2==0) $bgcolor="#eeeeee"; else $bgcolor="#f8f8f8";
			echo "<tr bgcolor='$bgcolor' class='formula_item' rowid='$row[ID]' order='$row[ORDER]' new_order='".($row[ORDER]?$row[ORDER]:-1)."' id='Qrowformula_".$row[ID]."' style=\"cursor:pointer\" onclick=\"loadVarsList($row[ID],'$row[NAME]')\">
			<td align='center'>$i</td><td>
			<span id='Q_FormulaName_".$row[ID]."'>$row[NAME]</span>
			<span style='display:none'>
			<span id='Q_TableName_".$row[ID]."'>$row[TABLE_NAME]</span>
			<span id='Q_ValueColumn_".$row[ID]."'>$row[VALUE_COLUMN]</span>
			<span id='Q_IDColumn_".$row[ID]."'>$row[OBJ_ID_COLUMN]</span>
			<span id='Q_ObjType_".$row[ID]."'>$row[OBJECT_TYPE]</span>
			<span id='Q_ObjID_".$row[ID]."'>$row[OBJECT_ID]</span>
			<span id='Q_FlowPhase_".$row[ID]."'>$row[FLOW_PHASE]</span>
			<span id='Q_AllocType_".$row[ID]."'>$row[ALLOC_TYPE]</span>
			<span id='Q_PUID_".$row[ID]."'>$rowLO[PRODUCTION_UNIT_ID]</span>
			<span id='Q_AreaID_".$row[ID]."'>$rowLO[AREA_ID]</span>
			<span id='Q_FAcilityID_".$row[ID]."'>$rowLO[FACILITY_ID]</span>
			<span id='Q_DateColumn_".$row[ID]."'>$row[DATE_COLUMN]</span>
			</span>
			</td>
			<td>$sLO</td>
			<td>$row[TABLE_NAME]</td>
			<td>$row[VALUE_COLUMN]</td>
			<td><span id='Q_Formula_".$row[ID]."' style='word-wrap: break-word;'>$row[FORMULA]</span></td>
			<td><span id='Q_BeginDate_".$row[ID]."'>$row[BEGIN_DATE]</span></td>
			<td><span id='Q_EndDate_".$row[ID]."'>$row[END_DATE]</span></td>
			<td><span id='Q_Comment_".$row[ID]."'>$row[COMMENT]</span></td>
			</tr>
			"; */
		//} 
		
		return response ()->json ( $formula );
	}
	
	public function getVarList(Request $request){
		$data = $request->all ();
		
		$tmp = FoVar::where(['FORMULA_ID'=>$data['formula_id']])->orderBy('ORDER', 'ID')->select('*')->get();
		$s="";
		$i=0;
		$html="";
		$result = [];
		foreach ($tmp as $row)
		{
			$rowLO=null;
			$r = [];
			$table_name=$row->OBJECT_TYPE;
			$entity = strtolower(str_replace('_', ' ', $table_name));
			$entity = ucwords($entity);
			$entity = str_replace(' ', '', $entity);
				
			$model = 'App\\Models\\' .$entity;
			$tablea = $model::getTableName ();
			$facility = Facility::getTableName ();
			$lo_area = LoArea::getTableName ();
				
			$rowLO = DB::table ( $tablea . ' AS a' )
			->join ( $facility . ' AS b', 'b.ID', '=', 'a.FACILITY_ID' )
			->join ( $lo_area . ' AS c', 'c.ID', '=', 'b.AREA_ID' )
			->where(['a.ID'=>$row->OBJECT_ID] )
			->select('a.NAME AS OBJECT_NAME', 'a.FACILITY_ID', 'b.AREA_ID', 'c.PRODUCTION_UNIT_ID')
			->first();
			
			$row['PRODUCTION_UNIT_ID'] = $rowLO->PRODUCTION_UNIT_ID;
			$row['AREA_ID'] = $rowLO->AREA_ID;
			$row['FACILITY_ID'] = $rowLO->FACILITY_ID;
			$row['OBJECT_NAME'] = $rowLO->OBJECT_NAME;
			
			array_push($result, $row);
		}
		
		return response ()->json ( $result );
	}
}