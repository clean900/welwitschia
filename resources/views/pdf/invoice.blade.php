<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { color: #1a1a1a; font-size: 12px; margin: 0; padding: 32px; }
    .head { width: 100%; border-bottom: 2px solid #16a34a; padding-bottom: 12px; margin-bottom: 20px; }
    .head td { vertical-align: top; }
    .brand { font-size: 18px; font-weight: bold; }
    .brand span { color: #16a34a; }
    .muted { color: #777; }
    .doc-title { font-size: 22px; font-weight: bold; text-align: right; }
    .doc-num { text-align: right; color: #555; font-family: DejaVu Sans Mono, monospace; }
    .meta { margin-bottom: 18px; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
    table.items th { background: #f1f5f0; text-align: left; padding: 7px 8px; font-size: 10px; text-transform: uppercase; color: #555; border-bottom: 1px solid #ddd; }
    table.items td { padding: 7px 8px; border-bottom: 1px solid #eee; }
    .right { text-align: right; }
    .totals { width: 240px; float: right; margin-top: 12px; }
    .totals td { padding: 4px 8px; }
    .totals .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #333; }
    .ref { clear: both; margin-top: 60px; background: #f1f5f0; border: 1px solid #cfe3d3; padding: 12px; border-radius: 6px; }
    .ref b { font-family: DejaVu Sans Mono, monospace; color: #15803d; }
    .foot { margin-top: 40px; color: #999; font-size: 10px; text-align: center; }
</style>
</head>
<body>
    <table class="head">
        <tr>
            <td>
                <div class="brand">Welwitschia <span>ERP</span></div>
                <div style="font-weight:bold; margin-top:6px;">{{ $company['name'] }}</div>
                @if($company['nif'])<div class="muted">NIF: {{ $company['nif'] }}</div>@endif
            </td>
            <td>
                <div class="doc-title">FACTURA</div>
                <div class="doc-num">{{ $invoice->number }}</div>
                @if($invoice->issued_at)<div class="doc-num">{{ $invoice->issued_at->format('d/m/Y') }}</div>@endif
            </td>
        </tr>
    </table>

    <div class="meta">
        <strong>Cliente:</strong> {{ $invoice->customer_name ?: '—' }}
        @if($invoice->customer_nif) &nbsp;·&nbsp; <strong>NIF:</strong> {{ $invoice->customer_nif }} @endif
    </div>

    <table class="items">
        <thead>
            <tr><th>Descrição</th><th class="right">Qtd</th><th class="right">Preço</th><th class="right">IVA</th><th class="right">Total</th></tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $it)
            <tr>
                <td>{{ $it->description }}</td>
                <td class="right">{{ rtrim(rtrim(number_format($it->quantity, 2), '0'), '.') }}</td>
                <td class="right">{{ format_currency((float) $it->unit_price) }}</td>
                <td class="right">{{ (float) $it->iva_rate }}%</td>
                <td class="right">{{ format_currency((float) $it->line_total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">{{ format_currency((float) $invoice->subtotal) }}</td></tr>
        <tr><td>IVA</td><td class="right">{{ format_currency((float) $invoice->iva_amount) }}</td></tr>
        <tr class="grand"><td>Total</td><td class="right">{{ format_currency((float) $invoice->total) }}</td></tr>
    </table>

    @if($payment && $payment->reference)
    <div class="ref">
        Pague por ProxyPay com a referência <b>{{ $payment->reference }}</b> — valor {{ format_currency((float) $invoice->total) }}.
    </div>
    @endif

    @if($qr ?? null)
    <table style="clear:both; margin-top:40px; width:100%;">
        <tr>
            <td style="width:130px; vertical-align:top;">
                <img src="{{ $qr }}" style="width:110px; height:110px;" alt="QR" />
            </td>
            <td style="vertical-align:bottom; font-size:10px; color:#555;">
                @if($hashCode)<div><b>{{ $hashCode }}</b> — Processado por programa certificado n.º {{ $cert }}</div>@endif
                <div style="color:#999; margin-top:4px;">// Faturação certificada AGT — formato a validar com a especificação oficial</div>
            </td>
        </tr>
    </table>
    @endif

    <div class="foot">
        Documento gerado por Welwitschia ERP · welwitschia.ao
    </div>
</body>
</html>
