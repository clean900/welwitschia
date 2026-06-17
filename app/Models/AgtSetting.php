<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgtSetting extends Model
{
    protected $fillable = ['tax_registration_number', 'establishment_number', 'private_key', 'active'];

    protected $hidden = ['private_key'];

    protected function casts(): array
    {
        return [
            'private_key' => 'encrypted',
            'active' => 'boolean',
        ];
    }
}
