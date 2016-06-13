<?php

namespace App\Models;

use App\Models\EbBussinessModel;

class Comment extends EbBussinessModel
{
    protected $table = 'COMMENT';
    public $timestamps = false;
    public $primaryKey  = 'ID';
    
    protected $fillable  = ['FACILITY_ID', 'CREATED_DATE', 'COMMENT_TYPE', 'COMMENTS', 'STATUS'];
    
    public static function getKeyColumns(&$newData,$occur_date,$postData)
    {
    	if (!array_key_exists('CREATED_DATE', $newData)||!$newData['CREATED_DATE']) {
    		$newData['CREATED_DATE'] = $occur_date;
    	}
    	if (!array_key_exists('COMMENT_TYPE', $newData)||!$newData['COMMENT_TYPE']) {
    		$newData['COMMENT_TYPE'] = $postData['CodeCommentType'];
    	}
    	if (!array_key_exists('FACILITY_ID', $newData)||!$newData['FACILITY_ID']) {
    		$newData['FACILITY_ID'] = $postData['Facility'];
    	}
    	return [
    			'ID' => $newData['ID'],
    	];
    }
    
}
