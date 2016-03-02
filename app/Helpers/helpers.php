<?php
class Helper {
	public static function filter($model,$filteName,$id='',$name='') {
		
		if (!isset($model)) return;
		
		$htmlFilter = 	"<div><div>$filteName".
						'</div>
				<select id="'.$id.'" size="1" name="'.$name.'">';
		
		$collection = $model::all(['ID', 'NAME'])->toArray();
		
		foreach($collection as $item ){
			$htmlFilter .= '<option value="'.$item['ID'].'">'.$item['NAME'].'</option>';
			
		}
		
		$htmlFilter .= '</select></div>';
		
		echo $htmlFilter;
	}
}
