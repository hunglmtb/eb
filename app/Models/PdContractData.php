<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdContractData extends EbBussinessModel 
{ 
	protected $table = 'PD_CONTRACT_DATA';
	protected $dates = ['ATTRIBUTE_DATE'];
	protected $fillable  = ['ATTRIBUTE_ID', 
							'CONTRACT_ID', 
							'ATTRIBUTE_DATE', 
							'ATTRIBUTE_TEXT', 
							'ATTRIBUTE_VALUE', 
							'ATTRIBUTE_UOM', 
							'ATTRIBUTE_COMMENT'];
} 
