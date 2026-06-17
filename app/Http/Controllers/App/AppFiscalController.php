<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\Agt\SaftExportService;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Exportações fiscais (SAF-T AO). // VALIDAR COM AGT — esquema oficial.
 */
class AppFiscalController extends Controller
{
    public function saft(SaftExportService $saft): HttpResponse
    {
        $xml = $saft->build(['name' => tenant('name'), 'nif' => tenant('nif')], config('agt.software_cert'));

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="saft-' . tenant('id') . '.xml"',
        ]);
    }
}
