<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Tenant\TenantAuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Tests\TenancyTestCase;

class TenantAuthTest extends TenancyTestCase
{
    public function test_login_valido_emite_token_sanctum(): void
    {
        $this->makeTenant('a1')->run(function () {
            User::create(['name' => 'Ana', 'email' => 'ana@a.ao', 'password' => Hash::make('password123')]);

            $result = (new TenantAuthService())->login('ana@a.ao', 'password123');

            $this->assertNotEmpty($result['token']);
            $this->assertSame('ana@a.ao', $result['user']->email);
        });
    }

    public function test_credenciais_invalidas_falham(): void
    {
        $this->makeTenant('a2')->run(function () {
            User::create(['name' => 'Ana', 'email' => 'ana@a.ao', 'password' => Hash::make('password123')]);

            $this->expectException(ValidationException::class);
            (new TenantAuthService())->login('ana@a.ao', 'errada');
        });
    }

    public function test_2fa_activa_e_confirma(): void
    {
        $this->makeTenant('a3')->run(function () {
            $user = User::create(['name' => 'Ana', 'email' => 'ana@a.ao', 'password' => Hash::make('password123')]);
            $service = new TenantAuthService();

            $data = $service->enableTwoFactor($user);
            $code = (new Google2FA())->getCurrentOtp($data['secret']);

            $this->assertTrue($service->confirmTwoFactor($user->fresh(), $code));
            $this->assertTrue($user->fresh()->two_factor_enabled);
        });
    }

    public function test_login_com_2fa_exige_codigo_valido(): void
    {
        $this->makeTenant('a4')->run(function () {
            $user = User::create([
                'name' => 'Ana', 'email' => 'ana@a.ao', 'password' => Hash::make('password123'),
            ]);
            $service = new TenantAuthService();
            $data = $service->enableTwoFactor($user);
            $service->confirmTwoFactor($user->fresh(), (new Google2FA())->getCurrentOtp($data['secret']));

            // Com 2FA activo, login com código válido funciona.
            $code = (new Google2FA())->getCurrentOtp($data['secret']);
            $result = $service->login('ana@a.ao', 'password123', $code);
            $this->assertNotEmpty($result['token']);
        });
    }

    public function test_login_com_2fa_sem_codigo_falha(): void
    {
        $this->makeTenant('a5')->run(function () {
            $user = User::create(['name' => 'Ana', 'email' => 'ana@a.ao', 'password' => Hash::make('password123')]);
            $service = new TenantAuthService();
            $data = $service->enableTwoFactor($user);
            $service->confirmTwoFactor($user->fresh(), (new Google2FA())->getCurrentOtp($data['secret']));

            $this->expectException(ValidationException::class);
            $service->login('ana@a.ao', 'password123'); // sem código
        });
    }
}
