<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Faturação certificada AGT (Angola)
    |--------------------------------------------------------------------------
    | // VALIDAR COM AGT — estes valores e formatos devem ser confirmados com a
    | especificação técnica oficial da AGT e com o registo do software.
    */

    // Chave privada RSA do software certificado (PEM). Vazio = chave de TESTE gerada
    // localmente (NÃO válida para a AGT).
    'private_key' => env('AGT_PRIVATE_KEY'),

    // Número de validação/certificado do software emitido pela AGT.
    'software_cert' => env('AGT_SOFTWARE_CERT', 'PENDENTE'),

];
