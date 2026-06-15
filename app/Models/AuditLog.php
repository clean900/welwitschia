<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Auditoria imutável (append-only) com hash-chain.
 * chain_hash = SHA-256(prev_chain_hash + payload_hash).
 * NUNCA permitir update() ou delete() — quebraria a cadeia.
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event', 'auditable_type', 'auditable_id', 'user_id',
        'payload', 'payload_hash', 'prev_chain_hash', 'chain_hash', 'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('AuditLog é imutável (append-only): update() proibido.');
        });
        static::deleting(function () {
            throw new RuntimeException('AuditLog é imutável (append-only): delete() proibido.');
        });
    }

    /**
     * Regista um evento na cadeia de auditoria.
     */
    /**
     * Encode canónico: ordena chaves recursivamente para que o hash seja
     * estável independentemente da reordenação de chaves do jsonb (PostgreSQL).
     */
    protected static function canonicalHash(?array $payload): string
    {
        $canonicalize = function (&$value) use (&$canonicalize) {
            if (is_array($value)) {
                ksort($value);
                foreach ($value as &$v) {
                    $canonicalize($v);
                }
            }
        };

        $data = $payload ?? [];
        $canonicalize($data);

        return hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public static function record(string $event, array $payload, ?string $auditableType = null, $auditableId = null, ?int $userId = null): self
    {
        $payloadHash = static::canonicalHash($payload);
        $prev = static::orderByDesc('id')->first();
        $prevChain = $prev?->chain_hash;
        $chainHash = hash('sha256', ($prevChain ?? '') . $payloadHash);

        return static::create([
            'event' => $event,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId !== null ? (string) $auditableId : null,
            'user_id' => $userId,
            'payload' => $payload,
            'payload_hash' => $payloadHash,
            'prev_chain_hash' => $prevChain,
            'chain_hash' => $chainHash,
            'created_at' => now(),
        ]);
    }

    /**
     * Verifica a integridade de toda a cadeia. Devolve true se intacta.
     */
    public static function verifyChain(): bool
    {
        $prevChain = null;
        foreach (static::orderBy('id')->cursor() as $log) {
            $expectedPayloadHash = static::canonicalHash($log->payload);
            if (! hash_equals($expectedPayloadHash, $log->payload_hash)) {
                return false;
            }
            $expectedChain = hash('sha256', ($prevChain ?? '') . $log->payload_hash);
            if (! hash_equals($expectedChain, $log->chain_hash)) {
                return false;
            }
            $prevChain = $log->chain_hash;
        }

        return true;
    }
}
