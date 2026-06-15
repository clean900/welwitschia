<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id', 'plan_id', 'billing_period',
        'amount', 'currency', 'status',
        'proxypay_reference', 'proxypay_entity',
        'paid_at', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
