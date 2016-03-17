<?php
class Helper {
	public static function filter($option=null) {
		if ($option==null) return;
		$model='App\\Models\\'.$option['id'];
		$collection = $model::all(['ID', 'NAME']);
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
	
		$htmlFilter = 	"<div class=\"filter\"><div><b>$filterName</b>".
				'</div>
				<select id="'.$id.'" size="1" name="'.$name.'">';
		if ($default) {
			$htmlFilter .= '<option value="'.$default['value'].'" selected >'.$default['name'].'</option>';
		}
	
		$currentId = array_key_exists('currentId', $option)?$option['currentId']:'';
		foreach($collection as $item ){
			$htmlFilter .= '<option value="'.($item->ID).'"'.($currentId==$item->ID?'selected':'').'>'.($item->NAME).'</option>';
				
		}
		
	
	
		$htmlFilter .= '</select></div>';
		if ($id&&array_key_exists('dependences', $option)&&count($option['dependences'])>0) {
			$htmlFilter.= "<script>registerOnChange('$id',['".implode("','", $option['dependences'])."'])</script>";
		}
	
		echo $htmlFilter;
	}
	
	
	public static function selectDate($option=null) {
		
		if ($option==null) return;
		$name=array_key_exists('name', $option)?$option['name']:'';
		$value=array_key_exists('value', $option)?$option['value']:'';
		$id=array_key_exists('id', $option)?$option['id']:'';
		$sName=array_key_exists('sName', $option)?$option['sName']:'';
	
		$value=$value->format('m/d/Y');
		$htmlFilter = '';
		switch ($id) {
    			case 'date_begin':
    			case 'date_end':
					
					$htmlFilter.= "<div class='date_input'><div><b>$name</b></div><input readonly style='width:100%' type='text' id = '$id' name='$sName' size='15' value='$value'></div>";
					$htmlFilter.= '<script>
											$( "#'.$id.'" ).datepicker({
												changeMonth:true,
												changeYear:true,
												dateFormat:"mm/dd/yy"
											});
										</script>';
    				break;
    				case 'cboFilterBy':
    					$htmlFilter = 	"<div class=\"filter\"><div><b>$name</b>".
			    							'</div><select id="'.$id.'" size="1" name="'.$name.'">';
    					$htmlFilter .= "<option value = 'SAMPLE_DATE'>Sample Date</option><option value = 'TEST_DATE'>Test Date</option><option value = 'EFFECTIVE_DATE'>Effective Date</option>";
						$htmlFilter .= '</select></div>';
    					break;
    			default:
    				break;
    		}
		
	
		echo $htmlFilter;
	}
}
