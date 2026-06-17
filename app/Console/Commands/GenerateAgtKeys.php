<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Gera o par de chaves RSA para a faturação certificada AGT.
 * A chave PRIVADA fica em storage (secreta); a PÚBLICA é entregue à AGT.
 *
 * Uso: php artisan welwitschia:agt-keys [--force]
 */
class GenerateAgtKeys extends Command
{
    protected $signature = 'welwitschia:agt-keys {--force : Regenerar mesmo que já exista (CUIDADO)}';

    protected $description = 'Gera o par de chaves RSA para submeter à AGT (faturação certificada)';

    public function handle(): int
    {
        $dir = storage_path('app/agt');
        $privPath = $dir . '/private.pem';
        $pubPath = $dir . '/public.pem';

        if (file_exists($privPath) && ! $this->option('force')) {
            $this->error('Já existe um par de chaves em storage/app/agt/.');
            $this->warn('Regenerar (--force) cria uma chave NOVA e invalida a que já registaste na AGT.');

            return self::FAILURE;
        }

        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            $this->error('Falha a gerar as chaves (extensão openssl do PHP disponível?).');

            return self::FAILURE;
        }

        openssl_pkey_export($resource, $privatePem);
        $publicPem = openssl_pkey_get_details($resource)['key'];

        file_put_contents($privPath, $privatePem);
        chmod($privPath, 0600);
        file_put_contents($pubPath, $publicPem);

        $this->newLine();
        $this->info('✔ Par de chaves RSA 2048 gerado.');
        $this->line("  Privada (SECRETA): {$privPath}");
        $this->line("  Pública:           {$pubPath}");

        $this->newLine();
        $this->warn('1) Entrega a CHAVE PÚBLICA abaixo à AGT (com o registo do software):');
        $this->newLine();
        $this->line($publicPem);

        $this->newLine();
        $this->comment('2) No .env de produção, define (a partir de private.pem):');
        $this->line('   AGT_PRIVATE_KEY="<conteúdo de private.pem, com \\n nas quebras de linha>"');
        $this->line('   AGT_SOFTWARE_CERT="<número que a AGT emitir>"');
        $this->newLine();
        $this->warn('NUNCA commites a chave privada nem a mostres em logs. storage/ está fora do git.');

        return self::SUCCESS;
    }
}
