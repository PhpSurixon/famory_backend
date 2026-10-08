<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTagPrice extends Model
{
    protected $fillable = [
        'business_id',
        'business_tag_id',
        'business_price',
    ];

    protected $casts = [
        'business_price' => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function businessTag()
    {
        return $this->belongsTo(BusinessTag::class);
    }
}
