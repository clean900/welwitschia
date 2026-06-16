<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPartner extends Model
{
    protected $fillable = ['name', 'logo_url', 'sort_order', 'active'];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeVisible($query)
    {
        return $query->where('active', true)->orderBy('sort_order')->orderBy('name');
    }
}
