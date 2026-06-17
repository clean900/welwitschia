<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgtSeries extends Model
{
    protected $table = 'agt_series';

    protected $fillable = [
        'document_type', 'year', 'series_code',
        'authorized_qty', 'current_number', 'establishment_number', 'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'authorized_qty' => 'integer',
        'current_number' => 'integer',
    ];

    public function hasCapacity(): bool
    {
        return $this->current_number < $this->authorized_qty;
    }
}
