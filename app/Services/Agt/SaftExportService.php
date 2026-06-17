<?php

namespace App\Services\Agt;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Carbon;

/**
 * Exportação SAF-T (AO) — XML de auditoria fiscal.
 * // VALIDAR COM AGT — estrutura/namespaces conforme o esquema oficial.
 */
class SaftExportService
{
    public function build(array $company, string $cert): string
    {
        $e = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $money = fn ($v) => number_format((float) $v, 2, '.', '');
        $year = (int) Carbon::now()->format('Y');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= "<AuditFile>\n";
        $xml .= "  <Header>\n";
        $xml .= "    <CompanyName>{$e($company['name'])}</CompanyName>\n";
        $xml .= "    <TaxRegistrationNumber>{$e($company['nif'])}</TaxRegistrationNumber>\n";
        $xml .= "    <FiscalYear>{$year}</FiscalYear>\n";
        $xml .= "    <CurrencyCode>AOA</CurrencyCode>\n";
        $xml .= "    <ProductID>Welwitschia ERP</ProductID>\n";
        $xml .= "    <SoftwareCertificateNumber>{$e($cert)}</SoftwareCertificateNumber>\n";
        $xml .= "  </Header>\n";

        $xml .= "  <MasterFiles>\n";
        foreach (Customer::all() as $c) {
            $xml .= "    <Customer><CustomerID>{$c->id}</CustomerID><CompanyName>{$e($c->name)}</CompanyName><TaxRegistrationNumber>{$e($c->nif)}</TaxRegistrationNumber></Customer>\n";
        }
        foreach (Product::all() as $p) {
            $xml .= "    <Product><ProductCode>{$e($p->sku ?: $p->id)}</ProductCode><ProductDescription>{$e($p->name)}</ProductDescription></Product>\n";
        }
        $xml .= "  </MasterFiles>\n";

        $invoices = Invoice::whereIn('status', ['issued', 'paid'])->with('items')->get();
        $xml .= "  <SourceDocuments>\n    <SalesInvoices>\n";
        $xml .= "      <NumberOfEntries>{$invoices->count()}</NumberOfEntries>\n";
        foreach ($invoices as $inv) {
            $xml .= "      <Invoice>\n";
            $xml .= "        <InvoiceNo>{$e($inv->number)}</InvoiceNo>\n";
            $xml .= "        <Hash>{$e($inv->hash)}</Hash>\n";
            $xml .= "        <InvoiceDate>{$e(optional($inv->issued_at)->format('Y-m-d'))}</InvoiceDate>\n";
            $xml .= "        <CustomerName>{$e($inv->customer_name)}</CustomerName>\n";
            foreach ($inv->items as $it) {
                $xml .= "        <Line><Description>{$e($it->description)}</Description><Quantity>" . (float) $it->quantity . "</Quantity><UnitPrice>{$money($it->unit_price)}</UnitPrice><TaxPercentage>" . (float) $it->iva_rate . "</TaxPercentage><CreditAmount>{$money($it->line_total)}</CreditAmount></Line>\n";
            }
            $xml .= "        <DocumentTotals><TaxPayable>{$money($inv->iva_amount)}</TaxPayable><NetTotal>{$money($inv->subtotal)}</NetTotal><GrossTotal>{$money($inv->total)}</GrossTotal></DocumentTotals>\n";
            $xml .= "      </Invoice>\n";
        }
        $xml .= "    </SalesInvoices>\n  </SourceDocuments>\n</AuditFile>\n";

        return $xml;
    }
}
