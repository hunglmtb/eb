<?php
class Helper {
	public static function filter($model,$filteName,$option=array()) {
		if (!isset($model)) return;
		
		$addAllOption=array_key_exists('addAll', $option)?$option['addAll']:false;
		$id=array_key_exists('id', $option)?$option['id']:'';
		$name=array_key_exists('name', $option)?$option['name']:'';
		
		$htmlFilter = 	"<div class=\"filter\"><div><b>$filteName</b>".
						'</div>
				<select id="'.$id.'" size="1" name="'.$name.'">';
		if ($addAllOption) {
			$htmlFilter .= '<option value="0" selected >All</option>';
		}
		
		$collection = $model::all(['ID', 'NAME'])->toArray();
		
		foreach($collection as $item ){
			$htmlFilter .= '<option value="'.$item['ID'].'">'.$item['NAME'].'</option>';
			
		}
		
		
		
		$htmlFilter .= '</select></div>';
		
		echo $htmlFilter;
	}
	
	
	public static function selectDate($id,$dateName,$option=array()) {
		$name=array_key_exists('name', $option)?$option['$name']:'';
		$value=array_key_exists('value', $option)?$option['value']:'';
	
		if (!isset($id)) return;
	
		$htmlDatePicker = 	"<div class='date_input'><div><b>$dateName</b></div><input readonly style='width:100%' type='text' id = '$id' name='$name' size='15' value='$value'></div>";
		$htmlDatePicker.= '<script>
								$( "#'.$id.'" ).datepicker({
									changeMonth:true,
									changeYear:true,
									dateFormat:"mm/dd/yy"
								});
							</script>';
		
	
		echo $htmlDatePicker;
	}
}
