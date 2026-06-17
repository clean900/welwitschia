<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'number', 'customer_name', 'customer_nif', 'customer_country',
        'subtotal', 'iva_amount', 'total', 'currency',
        'status', 'issued_at', 'due_at',
        'document_type', 'agt_status', 'agt_request_id', 'agt_submitted_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'iva_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'agt_submitted_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
