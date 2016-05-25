<?php 
namespace App\Models; 
use App\Models\FeatureTankModel; 

 class StorageDataFdcValue extends FeatureTankModel 
{ 
	public  static $ignorePostData = true;
	protected $table = 'storage_data_fdc_value'; 
} 
