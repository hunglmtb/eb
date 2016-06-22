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
		return static :: whereHas('CodeQltySrcType',function ($query) use ($object_type_code) {
													$query->where("CODE",$object_type_code );
											})
					->where('SRC_ID',$object_id)
					->whereDate('EFFECTIVE_DATE','<=',$occur_date)
					->orderBy('EFFECTIVE_DATE','desc')
					->first();
	}
	
	public static function getQualityOil($object_id,$object_type_code,$occur_date){
		$row = static ::getQualityRow($object_id,$object_type_code,$occur_date);
		
		if($row){
			$dataID=$row->ID;
			//     			\DB::enableQueryLog();
			$querys = [
			'OIL_F' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'OIL_SHRK_F' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(VALUE)")),
			'GAS_R' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'FLSH_GAS_R' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(VALUE)")),
			];
		
			$qr = \DB::table(null);
			foreach($querys as $key => $query ){
				$qr = $qr->selectSub($query->getQuery(),$key);
			}
			$qdltDatas = $qr->first();
			if ($qdltDatas==null) {
				$qdltDatas=$row;
			}
			else $qdltDatas['ENGY_RATE'] = $row->ENGY_RATE;
			// 				\Log::info(\DB::getQueryLog());
			return $qdltDatas;
		}
		return null;
	}
	
	public function delete()
	{
		// Delete all of the products that have the same ids...
		QltyDataDetail::where("QLTY_DATA_ID", $this->primaryKey)->delete();
		// Finally, delete this category...
		return parent::delete();
	}
}
