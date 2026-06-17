<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'nif', 'email', 'phone', 'address', 'status'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
