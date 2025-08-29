<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionHistory extends Model
{
 
    protected $table = "transaction_history"; 
     
     
    protected $fillable = [
        'ads_id',
        'amount',
        'user_id',
        'source',
        'source_type','sticker_purchase_id','product_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
    public function ad() {
        return $this->hasOne(Advertisement::class,'id','ads_id');
    }
    
    public function product() {
        return $this->hasOne(Product::class,'id','product_id');
    }
    
    public function user() {
        return $this->hasOne(User::class,'id','user_id')->withTrashed();
    }
    
    public function sticker() {
        return $this->hasOne(StickerPurchase::class,'id','sticker_purchase_id');
    }
}
