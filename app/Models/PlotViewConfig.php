<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class PlotViewConfig extends DynamicModel 
{ 
	protected $table = 'plot_view_config'; 
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = ['ID', 'NAME', 'CONFIG', 'TIMELINE', 'CHART_TYPE'];
} 
