<?php

namespace App\Http\Controllers;
 
class FOController extends EBController {
	
	public function safety(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date'],
				)
		);
		return view ( 'front.safety',['filters'=>$filterGroups]);
	}
	
	public function comment(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date']),
				'frequenceFilterGroup'		=> ['CodeCommentType']
		);
		return view ( 'front.comment',['filters'=>$filterGroups]);
	}
	
	public function equipment(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date']),
				'frequenceFilterGroup'		=> ['EquipmentGroup','CodeEquipmentType']
		);
		return view ( 'front.equipment',['filters'=>$filterGroups]);
	}
	
	public function chemical(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date']),
				'frequenceFilterGroup'		=> ['CodeInjectPoint']
		);
		return view ( 'front.chemical',['filters'=>$filterGroups]);
	}
	
}
