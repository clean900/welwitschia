<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $fillable = [
        'payroll_id', 'employee_id', 'base_salary', 'allowances',
        'gross', 'inss_employee', 'inss_employer', 'irt', 'net',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'gross' => 'decimal:2',
        'inss_employee' => 'decimal:2',
        'inss_employer' => 'decimal:2',
        'irt' => 'decimal:2',
        'net' => 'decimal:2',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
