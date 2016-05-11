<?php 
namespace App\Models; 
use App\Models\FeatureTankModel; 

 class StorageDataForecast extends FeatureTankModel 
{ 
	public  static $ignorePostData = true;
	protected $table = 'storage_data_forecast'; 
} 
