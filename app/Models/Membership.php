<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Identidade de login da plataforma (schema central). Liga um email a uma empresa.
 * Um email pertence a uma só empresa. Após autenticar, a tenancy é inicializada
 * a partir do tenant_id (middleware tenant.account).
 */
class Membership extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'memberships';

    protected $fillable = ['name', 'email', 'password', 'tenant_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
