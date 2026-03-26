<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Carts extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'item_price'
    ];

    /**
     * Cart belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cart belongs to Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->select(
                'id',
                'name',
                'price',
                'reseller_price',
                'count',
                'image',
                'description',
                'type_of_tag',
                'tag_purpose',
                'color',
                'is_favourite'
            );
    }

    /**
     * Cart belongs to Address
     */
    public function address()
    {
        return $this->belongsTo(UserAddress::class);
    }
}