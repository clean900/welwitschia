<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'number', 'customer_name', 'customer_nif',
        'subtotal', 'iva_amount', 'total', 'currency',
        'status', 'issued_at', 'due_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'iva_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
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
