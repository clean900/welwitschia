<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'code', 'name', 'class', 'type', 'normal_balance', 'is_postable', 'parent_code',
    ];

    protected $casts = [
        'class' => 'integer',
        'is_postable' => 'boolean',
    ];

    public function lines()
    {
        return $this->hasMany(JournalLine::class, 'account_code', 'code');
    }

    /** Saldo da conta (débitos − créditos para contas de natureza devedora). */
    public function balance(): float
    {
        $debit = (float) $this->lines()->sum('debit');
        $credit = (float) $this->lines()->sum('credit');

        return $this->normal_balance === 'debit'
            ? round($debit - $credit, 2)
            : round($credit - $debit, 2);
    }
}
