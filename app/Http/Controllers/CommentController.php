<?php

namespace App\Http\Controllers;
use App\Models\Comment;

class CommentController extends CodeController {
    
	public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$safetyTable,$facility_id,$occur_date,$properties){

    	$comment_type = $postData['CodeCommentType'];
    	$comment = Comment::getTableName();
    	//      	\DB::enableQueryLog();
    	$dataSet = Comment::where("FACILITY_ID","=",$facility_id)
				    	->where("COMMENT_TYPE","=",$comment_type)
				    	->where("CREATED_DATE","=",$occur_date)
				    	->select(
				    			"$comment.ID as DT_RowId",
				    			"$comment.*"
				    			)
// 		    			->orderBy($safetyTable)
		    			->get();
    	//  		\Log::info(\DB::getQueryLog());
    	
    	return ['dataSet'=>$dataSet];
    }
}
