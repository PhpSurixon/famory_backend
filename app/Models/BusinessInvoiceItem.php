<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessInvoiceItem extends Model
{
    protected $fillable = [
        'business_invoice_id',
        'business_tag_id',
        'tag_name',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(BusinessInvoice::class, 'business_invoice_id');
    }
}
