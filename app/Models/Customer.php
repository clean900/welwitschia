<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'nif', 'email', 'phone', 'address', 'credit_limit', 'status',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
