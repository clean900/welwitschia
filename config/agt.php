<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Faturação certificada AGT (Angola)
    |--------------------------------------------------------------------------
    | // VALIDAR COM AGT — estes valores e formatos devem ser confirmados com a
    | especificação técnica oficial da AGT e com o registo do software.
    */

    // Chave privada RSA do SOFTWARE (Soluções Simples) — usada em jwsSoftwareSignature.
    'private_key' => env('AGT_PRIVATE_KEY'),

    // Número de validação/certificado do software emitido pela AGT.
    'software_cert' => env('AGT_SOFTWARE_CERT', 'PENDENTE'),

    // --- Integração FE (API) ---
    // Base de homologação por defeito; produção será fornecida pela AGT.
    'base_url' => env('AGT_FE_BASE_URL', 'https://sifphml.minfin.gov.ao/sigt/fe/v1'),
    'username' => env('AGT_FE_USERNAME'),
    'password' => env('AGT_FE_PASSWORD'),
    'product_id' => env('AGT_PRODUCT_ID', 'Welwitschia ERP'),
    'product_version' => env('AGT_PRODUCT_VERSION', '1.0'),
    'schema_version' => '1.0',

];
