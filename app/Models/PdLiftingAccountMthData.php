<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdLiftingAccountMthData extends EbBussinessModel 
{ 
	protected $table 		= 'PD_LIFTING_ACCOUNT_MTH_DATA';
	protected $dates 		= ['BALANCE_MONTH'];
	protected $fillable  	= ['LIFTING_ACCOUNT_ID', 
								'BALANCE_MONTH', 
								'BAL_VOL', 
								'BAL_MASS', 
								'BAL_ENERGY', 
								'BAL_POWER', 
								'ADJUST_VOL', 
								'ADJUST_MASS', 
								'ADJUST_ENERGY', 
								'ADJUST_POWER', 
								'COMMENT', 
								'ADJUST_CODE'];
} 
