<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Safety extends Model
{
    protected $table = 'safety';
    public $timestamps = false;
    public $primaryKey  = 'ID';
    
    protected $fillable  = ['ID', 'FACILITY_ID', 'CATEGORY_ID', 'COUNT', 'COMMENTS', 'CREATED_DATE', 'SEVERITY_ID'];
}
