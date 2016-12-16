<?php 
namespace App\Models; 

 class ConstraintDiagram extends EbBussinessModel 
{ 
	protected $table = 'CONSTRAINT_DIAGRAM'; 
	
	protected $fillable  = ['NAME', 'YCAPTION', 'CONFIG'];
} 
