<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class PlotViewConfig extends DynamicModel 
{ 
	protected $table = 'plot_view_config'; 
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = ['ID', 'NAME', 'CONFIG', 'TIMELINE', 'CHART_TYPE'];
	
	public static function loadBy($sourceData){
		if(array_key_exists('Facility', $sourceData)){
			$facility 		= $sourceData['Facility'];
			if ($facility) {
				$facility_id	= $facility->ID;
				$result			= PlotViewConfig::where("CONFIG",'like',"%#$facility_id:%")->get(); 
				return $result;
				;
			}
		}
		return null;
	}
} 
