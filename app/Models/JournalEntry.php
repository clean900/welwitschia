<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_date', 'description', 'reference',
        'source_type', 'source_id', 'total_debit', 'total_credit',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function isBalanced(): bool
    {
        return round((float) $this->total_debit - (float) $this->total_credit, 2) === 0.0;
    }
}
