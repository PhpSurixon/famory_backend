<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

use Illuminate\Database\Eloquent\SoftDeletes;

class StickerPurchase extends Model
{
 
    protected $table = "sticker_purchases"; 
     
     
    protected $fillable = [
        'product_id',
        'user_id',
        'quantity',
        'payment_intent_id',
        'charge_id',
        'ad_address_id','order_status'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
    
    public function products(): HasMany
    {
        return $this->hasMany(Product::class,'id','product_id');
    }
    
    public function product()
    {
        return $this->hasOne(Product::class,'id','product_id');
    }
    
    public function user()
    {
        return $this->hasOne(User::class,'id','user_id')->withTrashed();
    }
    
    public function address()
    {
        return $this->hasOne(AdAddress::class,'id','ad_address_id');
    }
    
    // public function userProduct(): HasManyThrough
    // {
    //     return $this->hasManyThrough(User::class, Product::class,'user_id','product_id','id','id');
    // }
    
    public function users(): HasMany
    {
        return $this->hasMany(User::class,'id','user_id')->withTrashed();
    }

}
