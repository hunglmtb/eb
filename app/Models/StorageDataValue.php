<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class StorageDataValue extends DynamicModel 
{ 
	protected $table = 'storage_data_value'; 
	protected $dates = ['LAST_DATA_READ'];
 } 
