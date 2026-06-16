<?php

namespace App\Console\Commands;

use App\Models\PlatformAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Cria (ou actualiza) um super-admin da plataforma Welwitschia.
 * Uso: php artisan welwitschia:admin admin@welwitschia.ao --name="Bráulio"
 */
class CreatePlatformAdmin extends Command
{
    protected $signature = 'welwitschia:admin {email} {--name=Admin} {--password=}';

    protected $description = 'Cria ou actualiza um super-admin da plataforma (back-office /admin)';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->option('password') ?: $this->secret('Palavra-passe do admin');

        if (! $password || strlen($password) < 8) {
            $this->error('A palavra-passe deve ter pelo menos 8 caracteres.');

            return self::FAILURE;
        }

        $admin = PlatformAdmin::updateOrCreate(
            ['email' => $email],
            ['name' => $this->option('name'), 'password' => Hash::make($password)],
        );

        $this->info("Super-admin pronto: {$admin->email}");

        return self::SUCCESS;
    }
}
