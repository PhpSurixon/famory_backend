<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyNewTag extends Model
{
    use HasFactory;
    protected $fillable = [
        'tag_id', 'buyer_user_id', 'buyer_user_email'
    ];

    /**
     * Get the family tag associated with this purchase.
     */
    public function tag()
    {
        return $this->belongsTo(FamilyTagId::class, 'tag_id');
    }

    /**
     * Get the buyer user associated with this purchase.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }
}
