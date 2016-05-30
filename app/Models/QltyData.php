<?php

namespace App\Models;
use App\Models\EbBussinessModel;

class QltyData extends EbBussinessModel
{
	protected $table = 'QLTY_DATA';
	protected $primaryKey = 'ID';
	protected $fillable  = ['CODE',
							'LAB_NAME',
							'NAME',
							'SAMPLE_DATE',
							'TEST_DATE',
							'SAMPLE_TAKER_NAME',
							'LAB_TECHNICIAN_NAME',
							'SRC_TYPE',
							'SRC_ID',
							'PRODUCT_TYPE',
							'EFFECTIVE_DATE',
							'QLTY_VALUE1',
							'QLTY_VALUE2',
							'QLTY_VALUE3',
							'QLTY_VALUE4',
							'QLTY_VALUE5',
							'ENGY_RATE'];
	
	public  static  $idField = 'ID';
	public  static  $typeName = 'QLTY';
	
	public function CodeQltySrcType()
	{
		return $this->belongsTo('App\Models\CodeQltySrcType', 'SRC_TYPE', 'ID');
	}
	
	public static function getQualityRow($object_id,$object_type_code,$occur_date){
// 		$sSQL="select a.* from qlty_data a, code_qlty_src_type b where a.SRC_ID='$object_id' and a.SRC_TYPE=b.ID and b.CODE='$object_type_code' and a.EFFECTIVE_DATE<=STR_TO_DATE('$occur_date', '%m/%d/%Y') order by a.EFFECTIVE_DATE desc limit 1";
		
		return static :: whereHas('CodeQltySrcType',function ($query) use ($object_type_code) {
													$query->where("CODE",$object_type_code );
											})
					->where('SRC_ID',$object_id)
					->whereDate('EFFECTIVE_DATE','<=',$occur_date)
					->orderBy('EFFECTIVE_DATE','desc')
					->first();
	}
	
	public function delete()
	{
		// Delete all of the products that have the same ids...
		QltyDataDetail::where("QLTY_DATA_ID", $this->primaryKey)->delete();
		// Finally, delete this category...
		return parent::delete();
	}
	
}
