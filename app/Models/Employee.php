<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name', 'nif', 'position', 'base_salary', 'allowances',
        'hire_date', 'phone', 'bank_account', 'status',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'hire_date' => 'date',
    ];

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function grossSalary(): float
    {
        return round((float) $this->base_salary + (float) $this->allowances, 2);
    }
}
