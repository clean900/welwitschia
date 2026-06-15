<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Pagamento (schema do tenant). Gerido pelo PaymentStateEngine.
 */
class Payment extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    // Estados da state machine
    public const CREATED = 'CREATED';
    public const PENDING = 'PENDING';
    public const PAID = 'PAID';
    public const RECONCILED = 'RECONCILED';
    public const REJECTED = 'REJECTED';
    public const EXPIRED = 'EXPIRED';
    public const MANUAL_REVIEW = 'MANUAL_REVIEW';

    protected $fillable = [
        'reference', 'entity', 'payable_type', 'payable_id',
        'amount', 'currency', 'status', 'idempotency_key',
        'webhook_payload', 'paid_amount', 'paid_at', 'reconciled_at', 'created_by',
    ];

    protected $casts = [
        'webhook_payload' => 'array',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'reconciled_at' => 'datetime',
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::PENDING);
    }

    public function scopeReconciled($query)
    {
        return $query->where('status', self::RECONCILED);
    }
}
