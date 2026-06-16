<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Super-admin da plataforma Welwitschia (back-office /admin).
 */
class PlatformAdmin extends Authenticatable
{
    protected $table = 'platform_admins';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}
