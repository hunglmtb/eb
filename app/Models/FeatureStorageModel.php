<?php

namespace App\Models;

class FeatureStorageModel  extends FeatureTankModel {
	public  static  $idField = 'STORAGE_ID';
	
	public static function getObjects() {
		return Storage::where("ID",">",0)->orderBy("NAME")->get();
	}
}
