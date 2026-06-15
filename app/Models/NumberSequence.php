<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberSequence extends Model
{
    protected $fillable = ['prefix', 'year', 'last_number'];

    protected $casts = [
        'year' => 'integer',
        'last_number' => 'integer',
    ];
}
