<?php

namespace App\Models;

class FeatureStorageModel  extends FeatureTankModel {
	
	public static function getObjects() {
		return Storage::all();
	}
}
