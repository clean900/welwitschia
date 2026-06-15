<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'year', 'month', 'status', 'processed_at',
        'total_gross', 'total_inss', 'total_irt', 'total_net',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'processed_at' => 'datetime',
        'total_gross' => 'decimal:2',
        'total_inss' => 'decimal:2',
        'total_irt' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }
}
