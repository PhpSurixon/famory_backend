<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTagCode extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_SOLD = 'sold';
    public const STATUS_REGISTERED = 'registered';

    protected $fillable = [
        'business_tag_id',
        'business_id',
        'assigned_at',
        'qr_downloaded_at',
        'sold_at',
        'sold_marked_by',
        'registered_user_id',
        'registered_at',
        'family_tag_id_ref',
        'business_invoice_id',
        'tag_code',
        'reference_no',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'qr_downloaded_at' => 'datetime',
        'sold_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    public function businessTag()
    {
        return $this->belongsTo(BusinessTag::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /** The invoice billing this code (null until it is put on one). */
    public function invoice()
    {
        return $this->belongsTo(BusinessInvoice::class, 'business_invoice_id');
    }

    /** The app user who registered the tag. */
    public function registeredUser()
    {
        return $this->belongsTo(User::class, 'registered_user_id');
    }

    /** Sold by the shop: marked by an admin, or implied by a user registering it. */
    public function isSold(): bool
    {
        return !is_null($this->sold_at);
    }

    /** A user registered the tag in the app (which also counts as sold). */
    public function isRegistered(): bool
    {
        return !is_null($this->registered_user_id);
    }

    /** available -> assigned -> sold -> registered */
    public function status(): string
    {
        if ($this->isRegistered()) {
            return self::STATUS_REGISTERED;
        }
        if ($this->isSold()) {
            return self::STATUS_SOLD;
        }
        return $this->business_id ? self::STATUS_ASSIGNED : self::STATUS_AVAILABLE;
    }
}
