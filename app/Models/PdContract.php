<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdContract extends EbBussinessModel 
{ 
	protected $table = 'PD_CONTRACT';
	protected $dates = ['BEGIN_DATE','END_DATE'];
	protected $fillable  = ['CODE', 
							'NAME', 
							'EXTERNAL_CONTRACT_CODE', 
							'EXTERNAL_CONTRACT_NAME', 
							'BEGIN_DATE', 
							'END_DATE', 
							'CONTRACT_TYPE', 
							'CONTRACT_PERIOD', 
							'CONTRACT_TEMPLATE', 
							'CONTRACT_EXPENDITURE'];
} 
