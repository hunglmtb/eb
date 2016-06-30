<?php
use App\Models\LockTable;
use App\Models\AuditValidateTable;
use App\Models\AuditApproveTable;

class Helper {
	public static function filter($option=null) {
		if ($option==null) return;
		$model='App\\Models\\'.$option['id'];
		$collection = $model::all(['ID', 'NAME']);
// 		$collection = $model::select(['ID', 'NAME'])->orderBy('NAME')->get();
		$option['collection']=$collection;
		Helper::buildFilter($option);
	}
	
	public static function buildFilter($option=null) {
		if ($option==null) return;
		$collection = $option['collection'];
		$filterName = $option['filterName'];
	
		$default=array_key_exists('default', $option)?$option['default']:false;
		$id=array_key_exists('id', $option)?$option['id']:false;
		$name=array_key_exists('name', $option)?$option['name']:false;
	
		$htmlFilter = 	"<div class=\"filter $name\"><div><b>$filterName</b>".
				'</div>
				<select id="'.$id.'" name="'.$name.'">';
		if ($default) {
			$htmlFilter .= '<option value="'.$default['ID'].'" selected >'.$default['NAME'].'</option>';
		}
	
		$currentId = array_key_exists('currentId', $option)?$option['currentId']:'';
		foreach($collection as $item ){
			$htmlFilter .= '<option name="'.(isset($item->CODE)?$item->CODE:"")
						.'" value="'.($item->ID).'"'.($currentId==$item->ID?'selected':'')
						.'>'.($item->NAME).'</option>';
				
		}
		
	
	
		$htmlFilter .= '</select></div>';
		if ($id&&array_key_exists('dependences', $option)&&count($option['dependences'])>0) {
			$dependences = [];
			$more = [];
			$originDependences = $option['dependences'];
			foreach($originDependences as $dependence ){
// 				$dependences[] = $dependence;
				if (is_string($dependence) ) {
					$dependences[] = $dependence;
				}
				/* else if (isset($dependence['independent'])&&$dependence['independent']){
//  					$dependences[] = $dependence['name'];
// 					$more[] = $dependence['name'];
				} */
			}
			
			if (count($dependences)>0) {
				$extra = array_key_exists('extra', $option)&&count($option['extra'])>0?$option['extra']:null;
				$extra = is_array($extra)&&count($extra)>0?",['".implode("','", $extra)."']":'';
				$htmlFilter.= "<script>registerOnChange('$id',['".implode("','", $dependences)."']$extra)</script>";
			}
		}
	
		echo $htmlFilter;
	}
	
	
	public static function selectDate($option=null) {
		
		if ($option==null) return;
		$name=array_key_exists('name', $option)?$option['name']:'';
		$value=array_key_exists('value', $option)?$option['value']:'';
		$id=array_key_exists('id', $option)?$option['id']:'';
		$sName=array_key_exists('sName', $option)?$option['sName']:'';
	
		$htmlFilter = '';
		switch ($id) {
    			case 'date_begin':
    			case 'date_end':
    			case 'f_date_from':
    			case 'f_date_to':
    				$configuration = auth()->user()->getConfiguration();
    				$format = $configuration['time']['DATE_FORMAT_CARBON'];//'m/d/Y';
    				if ($value) {
						$value=$value->format($format);
    				}
    				$jsFormat = $configuration['picker']['DATE_FORMAT_JQUERY'];//'mm/dd/yy';
    				$htmlFilter.= "<div class='date_input'><div><b>$name</b></div><input style='width:85%' type='text' id = '$id' name='$sName' size='15' value='$value'></div>";
					$htmlFilter.= '<script>
											$( "#'.$id.'" ).datepicker({
												changeMonth:true,
												changeYear:true,
												dateFormat:"'.$jsFormat.'"
											});
										</script>';
    				break;
    				case 'cboFilterBy':
    					$htmlFilter = 	"<div class=\"filter\"><div><b>$name</b>".
			    							'</div><select id="'.$id.'" name="'.$name.'">';
    					$htmlFilter .= "<option value = 'SAMPLE_DATE'>Sample Date</option><option value = 'TEST_DATE'>Test Date</option><option value = 'EFFECTIVE_DATE'>Effective Date</option>";
						$htmlFilter .= '</select></div>';
    					break;
    			default:
    				break;
    		}
		
	
		echo $htmlFilter;
	}
	
	
	public static function checkLockedTable($dcTable,$occur_date,$facility_id) {
// 		$mdl = "App\Models\\".$mdlName;
// 		$tableName = $mdl::getTableName();
		$lockTable = LockTable::where(['TABLE_NAME'=>$dcTable,'FACILITY_ID'=>$facility_id])
		      					->whereDate('LOCK_DATE', '>=', $occur_date)
								->first();
		return $lockTable!=null&&$lockTable!=false;
	}
	
	public static function checkApproveTable($dcTable,$occur_date,$facility_id) {
		$lockTable = AuditApproveTable::where(['TABLE_NAME'=>$dcTable,'FACILITY_ID'=>$facility_id])
		->whereDate('DATE_FROM', '<=', $occur_date)
		->whereDate('DATE_TO', '>=', $occur_date)
		->first();
		return $lockTable!=null&&$lockTable!=false;
	}
	
	public static function checkValidateTable($dcTable,$occur_date,$facility_id) {
		$lockTable = AuditValidateTable::where(['TABLE_NAME'=>$dcTable,'FACILITY_ID'=>$facility_id])
		->whereDate('DATE_FROM', '<=', $occur_date)
		->whereDate('DATE_TO', '>=', $occur_date)
		->first();
		return $lockTable!=null&&$lockTable!=false;
	}
	
	
	public static function camelize($input, $separator = '_')
	{
		return str_replace($separator, '', ucwords($input, $separator));
	}
	
	public static function getModelName($table)
	{
		$tableName = strtolower ( $table );
		$mdlName = static::camelize ( $tableName, '_' );
		$mdl = 'App\Models\\' . $mdlName;
		return $mdl;
	}
}
