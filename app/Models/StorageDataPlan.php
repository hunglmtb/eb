<?php 
namespace App\Models; 
use App\Models\FeatureTankModel; 

 class StorageDataPlan extends FeatureTankModel 
{ 
	protected $table = 'storage_data_plan'; 
	public  static $ignorePostData = true;
 } 
