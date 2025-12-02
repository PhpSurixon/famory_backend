<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class DeathConfirmation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'trusted_user_id', 'status'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trustedUser()
    {
        return $this->belongsTo(User::class, 'trusted_user_id');
    }
}
