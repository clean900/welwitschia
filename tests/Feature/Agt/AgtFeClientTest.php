<?php

namespace Tests\Feature\Agt;

use App\Services\Agt\AgtFeClient;
use App\Services\Agt\JwsSigner;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AgtFeClientTest extends TestCase
{
    private string $softwareKey;
    private string $issuerKey;
    private string $issuerPub;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->softwareKey] = $this->keypair();
        [$this->issuerKey, $this->issuerPub] = $this->keypair();

        config([
            'agt.private_key' => $this->softwareKey,
            'agt.base_url' => 'https://agt.test/sigt/fe/v1',
            'agt.username' => 'user',
            'agt.password' => 'pass',
            'agt.software_cert' => 'SW-123',
        ]);
    }

    private function keypair(): array
    {
        $res = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($res, $priv);

        return [$priv, openssl_pkey_get_details($res)['key']];
    }

    public function test_jws_assina_e_verifica(): void
    {
        $signer = new JwsSigner();
        $jws = $signer->sign(['a' => 1, 'b' => 'x'], $this->issuerKey);

        $this->assertCount(3, explode('.', $jws));
        $this->assertTrue($signer->verify($jws, $this->issuerPub));
        $this->assertFalse($signer->verify($jws, $this->softwareKey)); // chave errada → falso
    }

    public function test_solicitar_serie_envia_payload_assinado(): void
    {
        Http::fake(['agt.test/*' => Http::response(['resultCode' => 'OK', 'seriesFEResult' => ['seriesCode' => 'S1']], 200)]);

        $res = app(AgtFeClient::class)->solicitarSerie('5000413178', 'FT', '2026', '001', $this->issuerKey);

        $this->assertSame('OK', $res['resultCode']);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/solicitarSerie')
            && $r['documentType'] === 'FT'
            && $r['taxRegistrationNumber'] === '5000413178'
            && ! empty($r['softwareInfo']['jwsSoftwareSignature'])
            && ! empty($r['jwsSignature'])
            && $r->hasHeader('Username', 'user'));
    }

    public function test_registar_factura_envia_documentos(): void
    {
        Http::fake(['agt.test/*' => Http::response(['requestID' => 'R1', 'errorList' => []], 200)]);

        $doc = ['documentNo' => 'FT WLW/2026/1', 'documentStatus' => 'N', 'documentType' => 'FT'];
        $res = app(AgtFeClient::class)->registarFactura('5000413178', [$doc]);

        $this->assertSame('R1', $res['requestID']);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/registarFactura')
            && $r['numberOfEntries'] === '1'
            && ! empty($r['softwareInfo']['jwsSoftwareSignature']));
    }

    public function test_assinatura_do_documento_e_verificavel(): void
    {
        $jws = app(AgtFeClient::class)->signDocument([
            'documentNo' => 'FT WLW/2026/1',
            'taxRegistrationNumber' => '5000413178',
            'documentType' => 'FT',
            'documentDate' => '2026-01-01',
            'documentTotals' => ['grossTotal' => '11400.00'],
        ], $this->issuerKey);

        $this->assertTrue((new JwsSigner())->verify($jws, $this->issuerPub));
    }
}
