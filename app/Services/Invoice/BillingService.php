<?php

namespace App\Services\Invoice;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payment\ProxyPayService;
use App\Services\Sms\TelcoSmsService;
use Illuminate\Validation\ValidationException;

/**
 * Orquestra a cobrança visível ao cliente:
 * factura emitida → referência ProxyPay → SMS (Sender ID do tenant).
 * O callback/reconciliação marca a factura paga (ver MarkInvoicePaid).
 */
class BillingService
{
    public function __construct(
        protected ProxyPayService $proxypay,
        protected TelcoSmsService $sms,
    ) {
    }

    public function requestPayment(Invoice $invoice, string $phone): Payment
    {
        if ($invoice->status === 'draft') {
            throw ValidationException::withMessages(['invoice' => 'Emita a factura antes de gerar a cobrança.']);
        }

        $reference = $this->proxypay->createReference((float) $invoice->total, [
            'invoice' => $invoice->number,
        ]);

        $payment = Payment::create([
            'reference' => $reference,
            'amount' => $invoice->total,
            'currency' => $invoice->currency,
            'status' => Payment::CREATED,
            'payable_type' => Invoice::class,
            'payable_id' => $invoice->id,
        ]);

        $message = sprintf(
            'Factura %s: pague a referencia %s, valor %s. Obrigado.',
            $invoice->number,
            $reference,
            format_currency((float) $invoice->total),
        );
        $this->sms->send($phone, $message);

        AuditLog::record('billing.payment_requested', [
            'invoice' => $invoice->number,
            'reference' => $reference,
            'phone' => $phone,
        ], Payment::class, $payment->id);

        return $payment;
    }
}
