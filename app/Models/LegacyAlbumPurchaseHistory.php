<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyAlbumPurchaseHistory extends Model
{
    protected $table = "legacy_album_purchase_histories"; 
    protected $fillable = [
        'user_id',
        'album_count',
        'package_name',
        'amount',
        'date',
        'status',
        'payment_id',
    ];
}
