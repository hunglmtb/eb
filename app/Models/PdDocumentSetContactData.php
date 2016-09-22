<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdDocumentSetContactData extends EbBussinessModel 
{ 
	protected $table = 'pd_document_set_contact_data'; 
	
	protected $fillable  = ['DOCUMENT_SET_DATA_ID',
							'CONTACT_ID',
							'ORGINAL_ID',
							'NUMBER_COPY'];
	
			
	public static function deleteWithConfig($mdlData) {
		$valuesIds = array_column($mdlData, 'ID');
		static::whereIn('ID', $valuesIds)->delete();
	}
	
	public function isNotAvailable($attributes){
		$entry = $this->find($attributes);
		return $entry->count()<=0;
	}
} 
