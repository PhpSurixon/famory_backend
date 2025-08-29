<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class DeceasedReport extends Model
{

    protected $fillable = [
       'user_id',
       'deceased_by',
       'report_by',
    ];
    
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    
}

