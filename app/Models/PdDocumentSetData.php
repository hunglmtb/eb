<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdDocumentSetData extends EbBussinessModel 
{ 
	protected $table = 'PD_DOCUMENT_SET_DATA'; 
	
	public function PdDocumentSetContactData()
	{
		return $this->hasMany('App\Models\PdDocumentSetContactData', "DOCUMENT_SET_DATA_ID", "ID");
		
	}
} 
