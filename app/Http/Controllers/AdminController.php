<?php

namespace App\Http\Controllers;

use App\Models\AuditApproveTable;
use App\Models\AuditTrail;
use App\Models\AuditValidateTable;
use App\Models\CodeAuditReason;
use App\Models\DataTableGroup;
use App\Models\Facility;
use App\Models\IntMapTable;
use App\Models\LoArea;
use App\Models\LockTable;
use App\Models\LogUser;
use App\Models\LoProductionUnit;
use App\Models\User;
use App\Models\UserDataScope;
use App\Models\UserRight;
use App\Models\UserRole;
use App\Models\UserRoleRight;
use App\Models\UserUserRole;
use App\Models\EbFunctions;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class AdminController extends Controller {
	
	
	public function __construct() {
		$this->isReservedName = config('database.default')==='oracle';
		$this->middleware ( 'auth' );
	}
	public function _index() {
		
		return view ( 'admin.users');
	}
		
	public function getData(Request $request) {
		
		$listControls = $request->all ();
		$result = array ();
		
		$records = array (
				'ID' => 0,
				'NAME' => 'All' 
		);
		
		$LoProductionUnitID = 0;
		$LoAreaID = 0;
		
		foreach ( $listControls as $listControl ) {
			
			if(!isset($listControl['TYPE'])){
				
				$ID = $listControl ['ID'];
				
				$tmp = array ();
				
				$model = 'App\\Models\\' . $listControl ['ID'];
				
				if (isset ( $listControl ['default'] )) {
					array_push ( $tmp, $records );
				}
				
				if ($ID == "LoProductionUnit" || $ID == "LoArea" || $ID == "Facility") {
					if ($ID == "LoProductionUnit") {
						$tmps = $model::all ( [ 
								'ID',
								'NAME' 
						] );
						if (! isset ( $listControl ['default'] )) {
							$LoProductionUnitID = $tmps [0]->ID;
						}
					} else {
						if ($ID == "LoArea") {
							if ($LoProductionUnitID != 0) {
								$tmps = $model::where ( [ 
										'PRODUCTION_UNIT_ID' => $LoProductionUnitID 
								] )->select ( 'ID', 'NAME' )->get ();
							} else {
								$tmps = $model::all ( [ 
										'ID',
										'NAME' 
								] );
							}
							
							if (! isset ( $listControl ['default'] )) {
								$LoAreaID = $tmps [0]->ID;
							}
						} else {
							if ($ID == "Facility") {
								if ($LoAreaID != 0) {
									$tmps = $model::where ( [ 
											'AREA_ID' => $LoAreaID 
									] )->select ( 'ID', 'NAME' )->get ();
								} else {
									$tmps = $model::all ( [ 
											'ID',
											'NAME' 
									] );
								}
							}
						}
					}
				} else {
					if($ID != "USER"){
						$listColumn = ['ID','NAME'];
						$tmps = $model::all ($listColumn);
						/* if($ID == 'IntObjectType'){
							$tmps = $model::where(['ACTIVE'=>1])->orderBy('ORDER','ASC')->get ($listColumn);
						}else{
							$tmps = $model::all ($listColumn);
						} */
					}else{
						$listColumn = ['ID','USERNAME'];
						$tmps = $model::all ($listColumn);
					}					
				}
				
				foreach ( $tmps as $v ) {
					if($ID == "USER"){
						$v->NAME = $v->USERNAME;
					}
					array_push ( $tmp, $v );
				}
				
				$result [$listControl ['label']] = $tmp;
				
			}else{
				if($listControl['TYPE'] == 'DATE'){
// 					$value = Carbon::now('Europe/London');
					$value 	= Carbon::now();
					if (isset($listControl['FORMAT'])) {
						$format = $listControl['FORMAT'];
						$value	=$value->format($format);
						$listControl['default'] = $value;				
					}
					else $listControl['default'] = date('m/d/Y', strtotime($value));				
					$result [$listControl ['ID']] = $listControl;
				}else{
					$result [$listControl ['ID']] = $listControl;
				}
			}
		}
		
		return response ()->json ( array (
				'result' => $result
		) );
	}
	public function selectedID(Request $request) {
		$id = $request->input ( 'ID' );
		$table = $request->input ( 'TABLE' );
		
		$model = 'App\\Models\\' . $table;
		
		if ($id != 0) {
			if ($table == "LoArea") {
				$where = [ 
						'PRODUCTION_UNIT_ID' => $id 
				];
			} else if ($table == "Facility") {
				$where = [ 
						'AREA_ID' => $id 
				];
			}
			
			$tmps = $model::where ( $where )->select ( 'ID', 'NAME' )->get ();
		} else {
			$tmps = $model::all ( [ 
					'ID',
					'NAME' 
			] );
		}
		
		return response ()->json ( array (
				'result' => $tmps 
		) );
	}
	
	// Start admin user
	public function getUsersList(Request $request) {
		$role_id = $request->input ( 'ROLES_ID' );
		$production_unit_id = $request->input ( 'PRODUCTION_ID' );
		$area_id = $request->input ( 'AREA_ID' );
		$facility_id = $request->input ( 'FACILITY' );
		
		$result = array();
		
		
		$userDataScope 		= UserDataScope::getTableName();
		$loProductionUnit 	= LoProductionUnit::getTableName();
		$loArea 			= LoArea::getTableName();
		$facility 			= Facility::getTableName();
		$user				= User::getTableName();
		$userUserRole 		= UserUserRole::getTableName();
		$userRole 			= UserRole::getTableName();
		$listColumn = [
				"$user.ID", "$user.USERNAME","$user.PASSWORD_CHANGED","$userDataScope.PU_ID" , "$userDataScope.AREA_ID",
				"$userDataScope.FACILITY_ID", "$user.ID AS ROLE",
				"$loProductionUnit.NAME AS PU_NAME","$loArea.NAME AS AREA_NAME","$facility.NAME AS FACILITY_NAME", 
				"$user.EXPIRE_DATE", "$user.ACTIVE", "$user.ACTIVE AS STATUS", "$user.expire_date AS expire_status"
		];
		
		
		$query = User::leftJoin($userDataScope, "$user.id", "=", "$userDataScope.user_id")
		->leftJoin($loProductionUnit, "$loProductionUnit.id", '=', "$userDataScope.PU_ID")
		->leftJoin($loArea, "$loArea.id", '=', "$userDataScope.AREA_ID")
		->leftJoin($facility, "$userDataScope.FACILITY_ID", 'like', "%$facility.id")
		->distinct("$user.ID")
		->groupBy("$user.ID")
		->select($listColumn)
		->orderBy ( "$user.id", 'asc' );
		
		$listData = $query->get ();
		
		foreach ( $listData as $data ) {
			$sRole = "";
			
					
			$subList = DB::table ( $userUserRole.' AS x' )
			->join ( $userRole.' AS z', 'x.role_id', '=', 'z.id' )
			->where ( ['x.user_id' => $data->ID] )
			
			->where(function($q) use ($role_id) {
				$q->where(function($query) use ($role_id){
					
					if($role_id != 0){
						$query->where(['x.role_id' => $role_id]);
					}
				});
			})
			
			->select ('z.name')
			->get();
			
			if(count($subList) > 0){
				foreach ($subList as $sub){
					$sRole.=($sRole==""?"":"<br>").$sub->name;
				}
			}			
			
			if ($role_id > 0 && $sRole == ""){
				continue; 
			}
			
			if ($sRole == "")
				$sRole = "(No role)";
			
			if ($production_unit_id != 0 && $production_unit_id != $data->PU_ID){
				continue;
			}
			
			if ($area_id != 0 && $area_id != $data->AREA_ID){
				continue;
			}
			
			if ($facility_id != 0 && $facility_id != $data->FACILITY_ID){
				continue;
			}
			
			if ($data->FACILITY_ID && $data->FACILITY_ID!=0 && $data->FACILITY_ID!="0") {
				$facilityIds = explode(",", $data->FACILITY_ID);
				if (count($facilityIds)>0) {
					$facilities = Facility::whereIn("ID",$facilityIds)->select("NAME")->get();
					$facilities	= $facilities->pluck("NAME")->toArray();
					if ($facilities&&count($facilityIds)>0) {
						$data->FACILITY_NAME	= implode("<br>", $facilities);
					}
				}
			}
			
			$data->ROLE = $sRole;	
			
			if($data->ACTIVE == 1){
				$data->STATUS = 'Active';
			}else {
				$data->STATUS = 'Not Active';
			}
			
			$now = Carbon::now('Europe/London');
			
			if($data->EXPIRE_DATE > $now){
				$data->EXPIRE_STATUS = "";
			}else {
				$data->EXPIRE_STATUS = 'Expired';
			}
			
			$data->EXPIRE_DATE = date('m/d/Y',strtotime($data->EXPIRE_DATE));
			$data->PASSWORD_CHANGED = date('m/d/Y H:i:s', strtotime($data->PASSWORD_CHANGED));
			
			 if($data->EXPIRE_STATUS == ''){
				$data->EXPIRE_STATUS = '';
			}else {
				$data->EXPIRE_STATUS = ', '.$data->EXPIRE_STATUS;
			} 
			
			$data->STATUS = $data->STATUS.$data->EXPIRE_STATUS;
			
			array_push ( $result, $data );
		}
		
		return response ()->json ( array (
				'result' => $result 
		) );
	}
	
	public function editUser(Request $request, $id){
		$userDataScope = UserDataScope::getTableName();
		$loProductionUnit = LoProductionUnit::getTableName();
		$loArea = LoArea::getTableName();
		$facility = Facility::getTableName();
		$user = User::getTableName();
		
		$listColumn = [
				'a.ID', 'a.USERNAME','a.PASSWORD','b.PU_ID' , 'b.AREA_ID', 'b.FACILITY_ID', 
				'LAST_NAME', 'MIDDLE_NAME', 'FIRST_NAME', 'EMAIL', 'a.EXPIRE_DATE', 'a.ACTIVE'
		];
		
// 		\DB::enableQueryLog();
		$user = DB::table($user.' AS a')
		->leftJoin($userDataScope.' AS b', 'a.id', '=', 'b.user_id')
		->leftJoin($loProductionUnit.' AS pu', 'pu.id', '=', 'b.PU_ID')
		->leftJoin($loArea.' AS ar', 'ar.id', '=', 'b.AREA_ID')
		->leftJoin($facility.' AS fa', 'fa.id', '=', 'b.FACILITY_ID')
		->where(['a.ID' => $id])
		->select($listColumn)->first();	
// 		\Log::info(\DB::getQueryLog());
		
			$user->EXPIRE_DATE = date('m/d/Y',strtotime($user->EXPIRE_DATE));
			
			$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);
			
			$userUserRole = UserUserRole::where(['USER_ID'=>$user->ID])->get(['ROLE_ID']);
			
			$loProductionUnit = LoProductionUnit::all(['ID', 'NAME']);
			
			$area = LoArea::all(['ID', 'NAME']);
			
			$facility = Facility::all(['ID', 'NAME']);
		
		return view ( 'admin.edit_users', ['user'=>$user, 'userRole'=>$userRole, 'userUserRole'=>$userUserRole, 'loProductionUnit' => $loProductionUnit, 'LoArea'=>$area, 'facility' => $facility]);
	}
	
	public function addUser(){
		
		$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);
		
		$loProductionUnit = LoProductionUnit::all(['ID', 'NAME']);		
		
		return view ( 'admin.add_users',  ['userRole'=>$userRole, 'loProductionUnit' => $loProductionUnit]);
	}
	
	public function addNewUser(Request $request){
		
		$data = $request->all();
		$obj = new CommonController();
		
		DB::beginTransaction();
		try {
			$check = User::where(['USERNAME'=>$data['username']])->get();
			
			if(count($check) <=0 ){		
				
				$user = new User;
				$user->USERNAME = $data['username'];
				$user->PASSWORD = $obj->myencrypt($data['pass']);
				$user->LAST_NAME = $data['lastname'];
				$user->MIDDLE_NAME = $data['middlename'];
				$user->FIRST_NAME = $data['firstname'];
				$user->EMAIL = $data['email'];
				$user->EXPIRE_DATE = date('Y/m/d', strtotime($data['expireDate']));
				$user->ACTIVE = $data['active'];
				$user->save();
				
				$userDataScope = new UserDataScope;
				$userDataScope->USER_ID = $user->ID;
				
				$userDataScope->PU_ID = ($data['pu_id']==0)?null:$data['pu_id'];
				$userDataScope->AREA_ID = ($data['area_id'] == 0)?null:$data['area_id'];
				
				$facility			= null;
				if (is_array($data['fa_id'])&&count($data['fa_id'])&&!in_array(0, $data['fa_id'])&&!in_array("0", $data['fa_id'])) {
					$facility	= implode($data['fa_id'], ",");
				}
				$userDataScope->FACILITY_ID = $facility;
// 				$userDataScope->FACILITY_ID = ($data['fa_id'] == 0)?null:$data['fa_id'];
				UserDataScope::insert(json_decode(json_encode($userDataScope), true));
				
				$roles = explode(',',$data['roles']);
				
				if(count($roles) > 0){
					foreach ($roles as $role){
						$userUserRole = new UserUserRole;
						$userUserRole->USER_ID = $user->ID;
						$userUserRole->ROLE_ID = $role;
						
						UserUserRole::insert(json_decode(json_encode($userUserRole), true));
					}
				}
			}			
		} catch(\Exception $e)
		{
			DB::rollback();
		}
		
		DB::commit();
		
		return response ()->json ( array (
				'Message' => 'Insert successfully' 
		) );
	}
	
	public function deleteUser(Request $request){
		
		$id = $request->input('ID');
		$error	 = false;
		DB::beginTransaction();
		try {
			UserDataScope::where(['USER_ID'=>$id])->delete();
			
			UserUserRole::where(['USER_ID'=>$id])->delete();
			
			User::where(['ID'=>$id])->delete();
			
		} catch(\Exception $e){
			DB::rollback();
			$error = true;
			if($e){
				$error = $e->getMessage();
				\Log::info($error);
				\Log::info($e->getTraceAsString());
			}
		}			
		DB::commit();
		
		if ($error) return response ()->json ( array (
				'Message' => "Delete error: $error"
		) );
		
		return response ()->json ( array (
				'Message' => 'Delete successfully'
		) );
	}
	
	public function updateUser(Request $request){
	
		$data = $request->all();
		$obj = new CommonController();
	
		DB::beginTransaction();
		$msg = 'no update to database';
		try {
			$isUpdate 				= $data['isUpdate'];
			$doUpdate				= false;
			$userName				= array_key_exists('username', $data)?$data['username']:null;
			if ($userName&&$userName!="") {
				$attributes			= ["username"	=> $userName];
				$values				= $attributes;
				$user				= User::updateOrInsert($attributes, $values )->first();
				$user->USERNAME 	= $userName;
				$user->LAST_NAME 	= $data['lastname'];
				$user->MIDDLE_NAME 	= $data['middlename'];
				$user->FIRST_NAME 	= $data['firstname'];
				$user->EMAIL 		= $data['email'];
				$user->EXPIRE_DATE 	= date('Y/m/d', strtotime($data['expireDate']));
				$user->ACTIVE 		= $data['active'];
				$user->save();
				if($data['pass'] != ""){
					$now = Carbon::now('Europe/London');
					$user->PASSWORD_CHANGED = date('Y-m-d H:i:s', strtotime($now));
					$user->PASSWORD 		= $obj->myencrypt($data['pass']);
					$user->save();
				}
				$userId				= $user->ID;
					
				UserDataScope::where(['USER_ID'=>$userId])->delete();
				UserUserRole::where(['USER_ID'=>$userId])->delete();
				
				$userDataScope = new UserDataScope;
				$userDataScope->USER_ID = $userId;
				$userDataScope->PU_ID = ($data['pu_id']==0)?null:$data['pu_id'];
				$userDataScope->AREA_ID = ($data['area_id'] == 0)?null:$data['area_id'];
				$facility			= null;
				if (is_array($data['fa_id'])&&count($data['fa_id'])&&!in_array(0, $data['fa_id'])&&!in_array("0", $data['fa_id'])) {
					$facility	= implode($data['fa_id'], ",");
				}
				$userDataScope->FACILITY_ID = $facility;
				UserDataScope::insert(json_decode(json_encode($userDataScope), true));
				
				$roles = explode(',',$data['roles']);	
				if(count($roles) > 0){
					foreach ($roles as $role){
						$userUserRole = new UserUserRole;
						$userUserRole->USER_ID = $userId;
						$userUserRole->ROLE_ID = $role;
						UserUserRole::insert(json_decode(json_encode($userUserRole), true));
					}
				}
				$msg = $user->wasRecentlyCreated?'add new user successfully':'Update user successfully';
			}
			else $msg = "user name must empty";
// 			}
		   } catch(\Exception $e){
				DB::rollback();
				$msg = 'error when update database';
				\Log::info($e->getMessage());
				\Log::info($e->getTraceAsString());
		  }  
		DB::commit();
		return response ()->json ( array (
				'Message' => $msg
		) );
	}
	
	public function _indexRoles() {
		
		$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);
	
		return view ( 'admin.roles', ['userRole'=>$userRole]);
	}
	
	public function editRole(Request $request){
		$data = $request->all();
		
		UserRole::where(['ID'=>$data['ID']])->update(['NAME'=>$data['NAME']]);
		
		$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);
		
		return view ( 'admin.roles', ['userRole'=>$userRole]);
	}
	
	public function addRole(Request $request){
		$data = $request->all();
	
		UserRole::insert(['NAME'=>$data]);
	
		$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);
	
		return response ()->json ( array (
				'userRole' => $userRole
		) );
	}
	
	public function deleteRole(Request $request){
		$data = $request->all();
		
		DB::beginTransaction();
		try {
			
			UserRoleRight::where(['ROLE_ID'=>$data['ID']])->delete();
			UserUserRole::where(['ROLE_ID'=>$data['ID']])->delete();	
			UserRole::where(['ID'=>$data['ID']])->delete();
			
		} catch(\Exception $e){
			DB::rollback();
		}
		
		DB::commit();
		
		$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);
	
		return response ()->json ( array (
				'userRole' => $userRole
		) );
	}
	
	public function loadRightsList(Request $request){		
		$data = $request->all();
		$userRoleRight = UserRoleRight::getTableName();
		$userRight = UserRight::getTableName();
		
		$roleLeft = DB::table($userRoleRight.' AS a')
		->join($userRight.' AS b', 'a.RIGHT_ID', '=', 'b.ID')
		->where(['a.ROLE_ID' => $data['ROLE_ID']])
		->select(['b.ID', 'b.NAME'])
		->get();
		
		$roleRight = DB::table($userRight.' AS a')
		->whereNotExists(function($query) use ($userRoleRight, $data){
			$query->select(DB::raw('A.ID')) 
				  ->from($userRoleRight.' AS b') 
				  ->whereRaw('b.RIGHT_ID = a.ID')
				  ->where(['b.ROLE_ID'=>$data['ROLE_ID']]);
		}) 
		->select(['a.ID', 'a.NAME'])
		->get();
		
		return response ()->json ( array (
				'roleLeft' => $roleLeft, 'roleRight' => $roleRight
		) );
	}
	
	public function removeOrGrant(Request $request){
		$data = $request->all();
		
		if($data['TYPE'] == 1) { // remove
			UserRoleRight::where(['ROLE_ID'=>$data['ROLE_ID'], 'RIGHT_ID'=> $data['RIGHT_ID']])->delete();
		}else{
			UserRoleRight::insert(['ROLE_ID'=>$data['ROLE_ID'], 'RIGHT_ID'=> $data['RIGHT_ID']]);
		}
		
		$userRoleRight = UserRoleRight::getTableName();
		$userRight = UserRight::getTableName();
		
		$roleLeft = DB::table($userRoleRight.' AS a')
		->join($userRight.' AS b', 'a.RIGHT_ID', '=', 'b.ID')
		->where(['a.ROLE_ID' => $data['ROLE_ID']])
		->select(['b.ID', 'b.NAME'])
		->get();
		
// 		\DB::enableQueryLog();
		$roleRight = DB::table($userRight.' AS a')
		->whereNotExists(function($query) use ($userRoleRight, $data){
			$query->select(DB::raw('A.ID'))
			->from($userRoleRight.' AS b')
			->whereRaw('b.RIGHT_ID = a.ID')
			->where(['b.ROLE_ID'=>$data['ROLE_ID']]);
		}) ->get();
// 		\Log::info(\DB::getQueryLog());
		
		return response ()->json ( array (
				'roleLeft' => $roleLeft, 'roleRight' => $roleRight
		) );
	}
	
	public function _indexAudittrail() {
		$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);
		$filterGroups = array(
								'productionFilterGroup'	=> [['name'			=>'IntObjectType',
															'independent'	=>true,
// 															'default'	=> ['ID'=>0,'NAME'=>'All'],
															// 															"getMethod"		=> "getGraphObjectType",
// 															'extra'			=> ["Facility","CodeProductType","IntObjectType","ObjectDataSource"],
															'dependences'	=> [
																					["name"		=>	"ObjectDataSource"],
																				]
															]],
								'dateFilterGroup'		=> array(
																['id'=>'date_begin','name'=>'From Date'],
																['id'=>'date_end','name'=>'To Date'],
															),
								'frequenceFilterGroup'	=> [
															["name"			=> "ObjectDataSource",
															"getMethod"		=> "loadBy",
															"filterName"	=>	"Table Name",
															"source"		=> ['productionFilterGroup'=>["IntObjectType"]]]
								],
								'enableSaveButton'		=> 	false,
		);
		return view ( 'admin.audittrail',['filters'=>$filterGroups,
										'userRole'=>$userRole
		]);
	}
	
	public function _indexValidatedata(){
		return view ( 'admin.validatedata');
	}
	
	public function loadValidateData(Request $request){
		
		$data = $request->all();
		
		$objType_id = $data['OBJECTTYPE'];
		$facility_id = $data['FACILITY_ID'];
		$group_id = $data['GROUP_ID'];
		$result = array();
		
		$intMapTable = IntMapTable::getTableName();
		$auditValidateTable = AuditValidateTable::getTableName();
		
		if($group_id != 0){
			$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();
			$group = $datatablegroup->TABLES;
			$group=str_replace("\r","",$group);
			$group=str_replace(" ","",$group);
			$group=str_replace("\t","",$group);
			$group=",".str_replace("\n",",",$group).",";
		}else{
			$group = '';
		}
		
// 		\DB::enableQueryLog();
		$loadValidateData = DB::table($intMapTable.' AS a')
		->leftjoin($auditValidateTable.' AS b', function ($join) use ($facility_id){
			$join->on('a.TABLE_NAME', '=', 'b.TABLE_NAME')
			->where('b.FACILITY_ID', '=', $facility_id);
		})
		->where(function($q) use ($objType_id) {
			if($objType_id != 0){
				$q->where(['a.OBJECT_TYPE' => $objType_id]);
			}
		})
		->select(['b.ID AS T_ID', 'a.ID', 'a.TABLE_NAME', 'a.FRIENDLY_NAME', 'b.DATE_FROM', 'b.DATE_TO'])
		->get();
// 		\Log::info(\DB::getQueryLog());
		
		foreach ($loadValidateData as $v){
		
			if($group){
		
				if (strpos($group,",$v->TABLE_NAME,") === false)
					continue;
			}
		
			$v->T_ID = ($v->T_ID?"checked":"");
		
			array_push($result, $v);
		}
		
		return response ()->json ( array (
				'result' => $result
		) );
	}
	
	public function validateData(Request $request){
		$data = $request->all();	
		
		try{
			$result = \DB::transaction(function () use ($data){
				
				$table_names = explode(',',$data['TABLE_NAMES']);
				
				$current_username 	= '';
				$userId				= null;
				if((auth()->user() != null)){
					$current_username = auth()->user()->username;
					$userId			= auth()->user()->ID;
				}
				$dateFrom			= \Helper::parseDate($data['DATE_FROM']);
				$dateTo				= \Helper::parseDate($data['DATE_TO']);
				$facility_id 		= $data['FACILITY_ID'];
				$obj['DATE_FROM'] 	= $dateFrom;
				$obj['DATE_TO'] 	= $dateTo;
				$obj['USER_ID'] 	= $userId;
				$obj['FACILITY_ID'] = $facility_id;
				foreach ($table_names as $table){
				
					$condition = array(
							'TABLE_NAME'=>$table,
							'FACILITY_ID'=>$data['FACILITY_ID']
					);
						
					$obj['TABLE_NAME'] = $table;
						
					//\DB::enableQueryLog();
					AuditValidateTable::updateOrCreate($condition,$obj);
					$this->updateRecordStatus("V",$table,$facility_id,$dateFrom,$dateTo,$current_username);
					//\Log::info(\DB::getQueryLog());
				}
				
				$objType_id = $data['OBJECTTYPE'];
				$group_id = $data['GROUP_ID'];
				$result = array();
				
				$intMapTable = IntMapTable::getTableName();
				$auditValidateTable = AuditValidateTable::getTableName();
				
				if($group_id != 0){
					$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();	;
					$group = $datatablegroup->TABLES;
					$group=str_replace("\r","",$group);
					$group=str_replace(" ","",$group);
					$group=str_replace("\t","",$group);
					$group=",".str_replace("\n",",",$group).",";
				}else{
					$group = '';
				}
				
				// 		\DB::enableQueryLog();
				$loadValidateData = DB::table($intMapTable.' AS a')
				->leftjoin($auditValidateTable.' AS b', function ($join) use ($facility_id){
					$join->on('a.TABLE_NAME', '=', 'b.TABLE_NAME')
					->where('b.FACILITY_ID', '=', $facility_id);
				})
				->where(function($q) use ($objType_id) {
					if($objType_id != 0){
						$q->where(['a.OBJECT_TYPE' => $objType_id]);
					}
				})
				->select(['b.ID AS T_ID', 'a.ID', 'a.TABLE_NAME', 'a.FRIENDLY_NAME', 'b.DATE_FROM', 'b.DATE_TO'])
				->get();
				// 		\Log::info(\DB::getQueryLog());
				
				foreach ($loadValidateData as $v){
				
					if($group){
				
						if (strpos($group,",$v->TABLE_NAME,") === false)
							continue;
					}
				
					$v->T_ID = ($v->T_ID?"checked":"");
				
					array_push($result, $v);
				}
				return $result;
			});
				
		}
		catch (\Exception $e)
		{
			\Log::info("\nApproveData\nException wher run transation ApproveData\n ");
			\Log::info($e->getMessage());
			\Log::info($e->getTraceAsString());
			// 			return response($e->getMessage(), 400);
			return response ()->json ("error: ".$e->getMessage());
			// 			throw $e;
		}
		
		return response ()->json ( array (
				'result' => $result
		) );
	}
	
	public function _indexApprove() {
		return view ( 'admin.approvedata');
	}
	
	public function loadApproveData(Request $request){
	
		$data = $request->all();
	
		$objType_id = $data['OBJECTTYPE'];
		$facility_id = $data['FACILITY_ID'];
		$group_id = $data['GROUP_ID'];
		$result = array();
	
		$intMapTable = IntMapTable::getTableName();
		$auditApproveTable = AuditApproveTable::getTableName();
	
		if($group_id != 0){
			$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();	;
			$group = $datatablegroup->TABLES;
			$group=str_replace("\r","",$group);
			$group=str_replace(" ","",$group);
			$group=str_replace("\t","",$group);
			$group=",".str_replace("\n",",",$group).",";
		}else{
			$group = '';
		}
	
// 		\DB::enableQueryLog();
		$loadApproveData = DB::table($intMapTable.' AS a')
		->leftjoin($auditApproveTable.' AS b', function ($join) use ($facility_id){
			$join->on('a.TABLE_NAME', '=', 'b.TABLE_NAME')
			->where('b.FACILITY_ID', '=', $facility_id);
		})
		->where(function($q) use ($objType_id) {
			if($objType_id != 0){
				$q->where(['a.OBJECT_TYPE' => $objType_id]);
			}
		})
		->select(['b.ID AS T_ID', 'a.ID', 'a.TABLE_NAME', 'a.FRIENDLY_NAME', 'b.DATE_FROM', 'b.DATE_TO'])
		->get();
// 		\Log::info(\DB::getQueryLog());
	
		foreach ($loadApproveData as $v){
	
			if($group){
	
				if (strpos($group,",$v->TABLE_NAME,") === false)
					continue;
			}
	
			$v->T_ID = ($v->T_ID?"checked":"");
	
			array_push($result, $v);
		}
	
		return response ()->json ( array (
				'result' => $result
		) );
	}
	
	public function ApproveData(Request $request){
		$data = $request->all();
		try{
			$result = \DB::transaction(function () use ($data){
				$table_names = explode(',',$data['TABLE_NAMES']);
				
				$current_username 	= '';
				$userId				= null;
				if((auth()->user() != null)){ 
					$current_username = auth()->user()->username;
					$userId			= auth()->user()->ID;
				}
				$dateFrom			= \Helper::parseDate($data['DATE_FROM']);
				$dateTo				= \Helper::parseDate($data['DATE_TO']);
				$facility_id 		= $data['FACILITY_ID'];
				$obj['DATE_FROM'] 	= $dateFrom;
				$obj['DATE_TO'] 	= $dateTo;
				$obj['USER_ID'] 	= $userId;
				$obj['FACILITY_ID'] = $facility_id;
				
				foreach ($table_names as $table){
					$condition = array(
							'TABLE_NAME'	=>$table,
							'FACILITY_ID'	=>$facility_id
					);
					$obj['TABLE_NAME'] = $table;
					AuditApproveTable::updateOrCreate($condition,$obj);
					$this->updateRecordStatus("A",$table,$facility_id,$dateFrom,$dateTo,$current_username);
				}
				
				$objType_id 	= $data['OBJECTTYPE'];
				$group_id 		= $data['GROUP_ID'];
				$result 		= array();
				
				$intMapTable = IntMapTable::getTableName();
				$auditApproveTable = AuditApproveTable::getTableName();
				
				if($group_id != 0){
					$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();	;
					$group = $datatablegroup->TABLES;
					$group=str_replace("\r","",$group);
					$group=str_replace(" ","",$group);
					$group=str_replace("\t","",$group);
					$group=",".str_replace("\n",",",$group).",";
				}else{
					$group = '';
				}
				
				$loadApproveData = DB::table($intMapTable.' AS a')
				->leftjoin($auditApproveTable.' AS b', function ($join) use ($facility_id){
					$join->on('a.TABLE_NAME', '=', 'b.TABLE_NAME')
					->where('b.FACILITY_ID', '=', $facility_id);
				})
				->where(function($q) use ($objType_id) {
					if($objType_id != 0){
						$q->where(['a.OBJECT_TYPE' => $objType_id]);
					}
				})
				->select(['b.ID AS T_ID', 'a.ID', 'a.TABLE_NAME', 'a.FRIENDLY_NAME', 'b.DATE_FROM', 'b.DATE_TO'])
				->get();
				
				foreach ($loadApproveData as $v){
				
					if($group){
				
						if (strpos($group,",$v->TABLE_NAME,") === false)
							continue;
					}
				
					$v->T_ID = ($v->T_ID?"checked":"");
				
					array_push($result, $v);
				}
				return $result;
			});
		}
		catch (\Exception $e)
		{
			\Log::info("\nApproveData\nException wher run transation ApproveData\n ");
			\Log::info($e->getMessage());
			\Log::info($e->getTraceAsString());
			// 			return response($e->getMessage(), 400);
			return response ()->json ("error: ".$e->getMessage());
// 			throw $e;
		}
		
		return response ()->json ( array (
				'result' => $result
		) );
	}
	
	public function updateRecordStatus($value,$table,$facility_id,$dateFrom,$dateTo,$current_username=null){
		$mtableRecord	= IntMapTable::where("TABLE_NAME",$table)->select("MASTER_TABLE")->first();
		if($mtableRecord){
			$mtable			= $mtableRecord->MASTER_TABLE;
			$mdl			= \Helper::getModelName($table);
			$dbtable		= $mdl::getTableName();

			//\DB::enableQueryLog();
			$mdl::join($mtable,function ($query) use ($mtable,$dbtable,$facility_id,$mdl) {
				$query->on("$mtable.ID",'=',"$dbtable.".$mdl::$idField);
				$query->on("$mtable.FACILITY_ID",'=',\DB::raw("$facility_id")) ;
			})
			->whereDate("$table.{$mdl::$dateField}" ,">=", $dateFrom)
			->whereDate("$table.{$mdl::$dateField}" ,"<=", $dateTo)
			->update([	"$table.RECORD_STATUS" 	=> $value,
						"$table.STATUS_BY" 		=> $current_username,
						"$table.STATUS_DATE" 	=> Carbon::now(),
			]);
 			//\Log::info(\DB::getQueryLog());
		}
	}
	
	public function _indexLockData() {
		return view ( 'admin.lockdata');
	}
	
	public function loadLockData(Request $request){
	
		$data = $request->all();
	
		$objType_id = $data['OBJECTTYPE'];
		$facility_id = $data['FACILITY_ID'];
		$group_id = $data['GROUP_ID'];
		$result = array();
	
		$intMapTable = IntMapTable::getTableName();
		$lockTable = LockTable::getTableName();
	
		if($group_id != 0){
			$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();	;
			$group = $datatablegroup->TABLES;
			$group=str_replace("\r","",$group);
			$group=str_replace(" ","",$group);
			$group=str_replace("\t","",$group);
			$group=",".str_replace("\n",",",$group).",";
		}else{
			$group = '';
		}
	
		$loadlockTable = DB::table($intMapTable.' AS a')
		->leftjoin($lockTable.' AS b', function ($join) use ($facility_id){
			$join->on('a.TABLE_NAME', '=', 'b.TABLE_NAME')
			->where('b.FACILITY_ID', '=', $facility_id);
		})
		->where(function($q) use ($objType_id) {
			if($objType_id != 0){
				$q->where(['a.OBJECT_TYPE' => $objType_id]);
			}
		})
		->select(['b.ID AS T_ID', 'a.ID', 'a.TABLE_NAME', 'a.FRIENDLY_NAME', 'b.LOCK_DATE'])
		->get();
	
		foreach ($loadlockTable as $v){
			if($group){
	
				if (strpos($group,",$v->TABLE_NAME,") === false)
					continue;
			}
	
			$v->T_ID = ($v->T_ID?"checked":"");
	
			array_push($result, $v);
		}
	
		return response ()->json ( array (
				'result' => $result
		) );
	}
	
	public function lockData(Request $request){
		$data = $request->all();
		$table_names = explode(',',$data['TABLE_NAMES']);
	
		$obj['LOCK_DATE'] 	= \Helper::parseDate($data['DATE_FROM']);
		$userId				= null;
		if((auth()->user() != null)){
			$current_username = auth()->user()->username;
			$userId			= auth()->user()->ID;
		}
		$obj['USER_ID'] 	= $userId;
		$obj['FACILITY_ID'] = $data['FACILITY_ID'];
		foreach ($table_names as $table){
			$condition = array(
					'TABLE_NAME'=>$table,
					'FACILITY_ID'=>$data['FACILITY_ID']
			);
			$obj['TABLE_NAME'] = $table;
			LockTable::updateOrCreate($condition,$obj);
		}
	
		$objType_id = $data['OBJECTTYPE'];
		$facility_id = $data['FACILITY_ID'];
		$group_id = $data['GROUP_ID'];
		$result = array();
	
		$intMapTable = IntMapTable::getTableName();
		$lockTable = LockTable::getTableName();
	
		if($group_id != 0){
			$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();
			$group = $datatablegroup->TABLES;
			$group=str_replace("\r","",$group);
			$group=str_replace(" ","",$group);
			$group=str_replace("\t","",$group);
			$group=",".str_replace("\n",",",$group).",";
		}else{
			$group = '';
		}
	
		$loadlockTable = DB::table($intMapTable.' AS a')
		->leftjoin($lockTable.' AS b', function ($join) use ($facility_id){
			$join->on('a.TABLE_NAME', '=', 'b.TABLE_NAME')
			->where('b.FACILITY_ID', '=', $facility_id);
		})
		->where(function($q) use ($objType_id) {
			if($objType_id != 0){
				$q->where(['a.OBJECT_TYPE' => $objType_id]);
			}
		})
		->select(['b.ID AS T_ID', 'a.ID', 'a.TABLE_NAME', 'a.FRIENDLY_NAME', 'b.LOCK_DATE'])
		->get();
	
		foreach ($loadlockTable as $v){
	
			if($group){
	
				if (strpos($group,",$v->TABLE_NAME,") === false)
					continue;
			}
	
			$v->T_ID = ($v->T_ID?"checked":"");
	
			array_push($result, $v);
		}
	
		return response ()->json ( array (
				'result' => $result
		) );
	}
	
	public function _indexUserlog() {
		return view ( 'admin.userlog');
	}
	
	public function loadUserLog(Request $request){
		$data = $request->all();
		$date_from 	= \Helper::parseDate($data['DATE_FROM']);
		$date_to 	= \Helper::parseDate($data['DATE_TO']);
		$username = trim($data['USERNAME']);
		$result = array();
	
		$logUser = LogUser::getTableName();
	
		$loadUserLog = DB::table($logUser.' AS a')
		->whereDate('a.LOGIN_TIME', '>=', $date_from)
		->whereDate('a.LOGIN_TIME', '<=', $date_to)
		->where(function($q) use ($username) {
			if($username != "All"){
				$q->where(['a.USERNAME' => $username]);
			}
		})
		->select(['a.USERNAME', 'a.LOGIN_TIME', 'a.LOGOUT_TIME', 'a.IP'])
		->get();
	
		return response ()->json ( array (
				'result' => $loadUserLog
		) );
	}
	
	public function _indexEditGroup() {
		$data = DataTableGroup::all(['ID', 'NAME']);
		
		$datatablegroup = DataTableGroup::where(['ID'=>$data[0]->ID])->select('TABLES')->first();
		
		return view ( 'admin.edit_data_table_group', ['datas'=>$data, 'datatablegroup'=>$datatablegroup]);
	}
	
	public function loadGroup(Request $request) {
		$data = $request->all();
		$datatablegroup = DataTableGroup::where(['ID'=>$data['GROUP_ID']])->select('TABLES')->first();
	
		return response ()->json ( array (
				'result' => $datatablegroup
		) );
	}
	
	public function deleteGroup(Request $request) {
		$data = $request->all();
		$datatablegroup = DataTableGroup::where(['ID'=>$data['GROUP_ID']])->delete();
	
		$data = DataTableGroup::all(['ID', 'NAME']);
	
		$datatablegroup = DataTableGroup::where(['ID'=>$data[0]->ID])->select('TABLES')->first();
	
		return response ()->json ( array (
				'result' => $datatablegroup, 'datatablegroup'=>$data
		) );
	}
	
	public function saveGroup(Request $request) {
		$data = $request->all();
		
		$condition = array(
				'ID'=>$data['GROUP_ID']
		);
		
		$obj['NAME'] = $data['NAME'];
		$obj['TABLES'] = $data['TABLES'];
		
// 		\DB::enableQueryLog();
		DataTableGroup::updateOrCreate($condition,$obj);
// 		\Log::info(\DB::getQueryLog());
	
		$data = DataTableGroup::all(['ID', 'NAME']);
	
		$datatablegroup = DataTableGroup::where(['ID'=>$data[0]->ID])->select('TABLES')->first();
	
		return response ()->json ( array (
				'result' => $datatablegroup, 'datatablegroup'=>$data
		) );
	}
	
	public function _helpEditor() {
		
		$eb_functions = EbFunctions::where('USE_FOR', 'like', '%TASK_GROUP%')->orderBy('CODE')->get();
	
		return view ( 'admin.helpeditor', ['eb_functions'=>$eb_functions]);
	}
	
	public function getFunction(Request $request) {
		$data = $request->all();
// 		\DB::enableQueryLog();
		$subEbFunctions = EbFunctions::where(['PARENT_CODE'=>$data['CODE']])->orderBy('CODE')->get();
// 		\Log::info(\DB::getQueryLog());
		return response ()->json ($subEbFunctions);
	}
	
	public function gethelp(Request $request) {
		$data = $request->all();
		$tmp = EbFunctions::where(['CODE'=>$data['func_code']])->select('HELP')->first();
		
		return response ()->json ($tmp['HELP']);
	}
	
	public function savehelp(Request $request) {
		$data = $request->all();
		
		$help=addslashes($data['help']);
		EbFunctions::where(['CODE'=>$data['func_code']])->update(['HELP'=>$help]);
	
		return response ()->json ("Ok");
	}
}