<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;


class UserSettingController extends EBController {

	public function index()
	{
		$user = auth()->user();
		return view ( 'me.setting',['user'=>$user]);
	}
	
	public function saveSetting(Request $request)
	{
		$postData 		= $request->all();
     	$dateformat 	= array_key_exists('dateformat',  	$postData)?$postData['dateformat']:null;
     	$timeformat 	= array_key_exists('timeformat',  	$postData)?$postData['timeformat']:null;
     	$numberformat 	= array_key_exists('numberformat',  $postData)?$postData['numberformat']:null;
     	$currentUser 	= auth()->user();
		if ($currentUser){
			if (($dateformat!=null||$timeformat!=null)) {
				$currentUser->saveDateTimeFormat($dateformat,$timeformat);
			}
			if (($numberformat!=null)) {
				$currentUser->saveNumberFormat($numberformat);
			}
		}
		else return response()->json(['empty post data']);
		
 		session()->put('configuration', $currentUser->configuration());
		
    	return response()->json(['success']);
	}
	
	
}
