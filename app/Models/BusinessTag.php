<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTag extends Model
{
    protected $fillable = [
        'name',
        'origin_price',
        'selling_price',
        'image',
        'description',
        'type_of_tag',
        'active',
    ];

    protected $casts = [
        'origin_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function codes()
    {
        return $this->hasMany(BusinessTagCode::class);
    }
}
