<?php

namespace App\Services\Tenant;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

/**
 * Autenticação dentro do tenant (utilizadores da empresa cliente).
 * Login por email/password + 2FA TOTP opcional. Tokens via Sanctum.
 */
class TenantAuthService
{
    public function __construct(protected Google2FA $google2fa = new Google2FA())
    {
    }

    /**
     * @return array{user: User, token: string}
     */
    public function login(string $email, string $password, ?string $code = null): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['email' => 'Credenciais inválidas.']);
        }

        if ($user->two_factor_enabled) {
            if (! $code || ! $this->verifyTotp($user, $code)) {
                throw ValidationException::withMessages(['code' => 'Código de autenticação inválido.']);
            }
        }

        $token = $user->createToken('api')->plainTextToken;

        AuditLog::record('auth.login', ['email' => $email], User::class, $user->id, $user->id);

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Inicia a activação de 2FA: gera o segredo e devolve o otpauth URL (QR).
     */
    public function enableTwoFactor(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        $user->two_factor_secret = $secret;
        $user->two_factor_enabled = false; // só fica activo após confirmação
        $user->save();

        return [
            'secret' => $secret,
            'otpauth_url' => $this->google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret),
        ];
    }

    /** Confirma e activa o 2FA validando um primeiro código. */
    public function confirmTwoFactor(User $user, string $code): bool
    {
        if (! $this->verifyTotp($user, $code)) {
            return false;
        }

        $user->two_factor_enabled = true;
        $user->save();

        AuditLog::record('auth.2fa_enabled', ['email' => $user->email], User::class, $user->id, $user->id);

        return true;
    }

    protected function verifyTotp(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        return $this->google2fa->verifyKey($user->two_factor_secret, $code);
    }
}
