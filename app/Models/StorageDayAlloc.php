<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class StorageDayAlloc extends DynamicModel 
{ 
	protected $table = 'STORAGE_DAY_ALLOC';
	protected $primaryKey = 'ID';
	protected $fillable  = [
		'ID',
		'OCCUR_DATE',
		'LAST_DAY_READ',
		'STORAGE_ID',
		'BEGIN_LEVEL',
		'END_LEVEL',
		'BEGIN_VOL',
		'END_VOL',
		'SW',
		'STORAGE_GRS_VOL',
		'STORAGE_NET_VOL',
		'STORAGE_DENSITY',
		'STORAGE_GRS_MASS',
		'STORAGE_NET_MASS',
		'RECORD_STATUS',
		'STATUS_BY',
		'STATUS_DATE'
	];
} 
