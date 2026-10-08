<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessInvoice extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_METHODS = ['Cash', 'Bank Transfer', 'Cheque', 'Card', 'Online', 'Other'];

    protected $fillable = [
        'invoice_no',
        'business_id',
        'status',
        'invoice_date',
        'bill_to_name',
        'bill_to_email',
        'bill_to_mobile',
        'bill_to_address',
        'notes',
        'total_codes',
        'subtotal',
        'tax_percent',
        'tax_amount',
        'total',
        'created_by',
        'issued_at',
        'issued_by',
        'paid_date',
        'payment_method',
        'payment_reference',
        'paid_by',
        'cancelled_at',
        'cancel_reason',
        'cancelled_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'paid_date' => 'date',
        'issued_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function items()
    {
        return $this->hasMany(BusinessInvoiceItem::class);
    }

    /** The tag codes this invoice bills. */
    public function codes()
    {
        return $this->hasMany(BusinessTagCode::class, 'business_invoice_id');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /** Only a draft can be edited. */
    public function canEdit(): bool
    {
        return $this->isDraft();
    }

    public function canIssue(): bool
    {
        return $this->isDraft();
    }

    public function canMarkPaid(): bool
    {
        return $this->isIssued();
    }

    public function canCancel(): bool
    {
        return $this->isDraft() || $this->isIssued();
    }

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }

    /** Bootstrap "label" colour used for the status badge. */
    public function statusBadge(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'bg-label-secondary',
            self::STATUS_ISSUED => 'bg-label-info',
            self::STATUS_PAID => 'bg-label-success',
            self::STATUS_CANCELLED => 'bg-label-danger',
            default => 'bg-label-secondary',
        };
    }
}
