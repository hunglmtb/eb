<?php 
namespace App\Models; 

 class KeystoreStorageDataValue extends FeatureKeystore { 
	protected $table 					= 'KEYSTORE_STORAGE_DATA_VALUE';
	protected static $objectModelName	= "KeystoreStorage";
	public  static $foreignKeystore 	= "KEYSTORE_STORAGE_ID";
	protected $dates 					= ['OCCUR_DATE'];
	
	protected $fillable  = ['KEYSTORE_STORAGE_ID', 'OCCUR_DATE', 'BEGIN_VOL', 'END_VOL', 'FILLED_VOL', 'INJECTED_VOL', 'CONSUMED_VOL'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if (!array_key_exists("OCCUR_DATE",$newData)|| !$newData["OCCUR_DATE"]||$newData["OCCUR_DATE"]==''){
			$newData["OCCUR_DATE"] 		= $occur_date;
		}
		return ["KEYSTORE_STORAGE_ID" 	=> $newData["KEYSTORE_STORAGE_ID"],
				"OCCUR_DATE" 			=> $newData["OCCUR_DATE"],
		];
	}
	public function updateBeginValues(){
		//Do nothing
		/*
		$prev_date = date ( "Y-m-d", strtotime ( "-1 DAY", strtotime ( $this->OCCUR_DATE ) ) );
		$values = KeystoreStorageDataValue::where("KEYSTORE_STORAGE_ID",'=',$this->KEYSTORE_STORAGE_ID)
						->whereDate('OCCUR_DATE', '=', $prev_date)
						->get(["END_VOL as BEGIN_VOL", "END_LEVEL as BEGIN_LEVEL"]) 
						->first();
		if($values){
			$values = $values->toArray();
			$values['KEYSTORE_STORAGE_ID'] = $this->KEYSTORE_STORAGE_ID;
			$values['OCCUR_DATE'] = $this->OCCUR_DATE;
			$this->update($values);
		}
		*/
	}
	public function getKeystoreStorageId(){
		return null;
	}
} 
