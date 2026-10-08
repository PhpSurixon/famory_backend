<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'business_name',
        'business_type',
        'email',
        'mobile',
        'image',
        'register_date',
        'is_active',
    ];

    public function tagCodes()
    {
        return $this->hasMany(BusinessTagCode::class);
    }

    public function tagPrices()
    {
        return $this->hasMany(BusinessTagPrice::class);
    }

    protected $casts = [
        'register_date' => 'date',
        'is_active' => 'boolean',
    ];
}
