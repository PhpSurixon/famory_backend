<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
 
    protected $table = "products"; 
     
     
    protected $fillable = [
        'name',
        'price',
        'count',
        'image',
        'description',
        'total_purchased',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
    
    public function stickerpurchase() {
        return $this->hasOne(StickerPurchase::class);
    }
    

}
