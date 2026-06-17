<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
@php
    $months = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
@endphp
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { color: #1a1a1a; font-size: 12px; margin: 0; padding: 32px; }
    .head { width: 100%; border-bottom: 2px solid #16a34a; padding-bottom: 12px; margin-bottom: 20px; }
    .head td { vertical-align: top; }
    .brand { font-size: 18px; font-weight: bold; }
    .brand span { color: #16a34a; }
    .doc-title { font-size: 18px; font-weight: bold; text-align: right; }
    .muted { color: #777; }
    .meta { margin-bottom: 18px; }
    table.lines { width: 100%; border-collapse: collapse; margin-top: 8px; }
    table.lines td { padding: 8px; border-bottom: 1px solid #eee; }
    .right { text-align: right; }
    .add { color: #15803d; }
    .sub { color: #b91c1c; }
    .net { font-weight: bold; font-size: 15px; border-top: 2px solid #333; }
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
                <div class="doc-title">RECIBO DE VENCIMENTO</div>
                <div class="right muted">{{ $months[$payroll->month] }} {{ $payroll->year }}</div>
            </td>
        </tr>
    </table>

    <div class="meta">
        <strong>Colaborador:</strong> {{ $payslip->employee?->name }}
        @if($payslip->employee?->position) &nbsp;·&nbsp; {{ $payslip->employee->position }} @endif
    </div>

    <table class="lines">
        <tr><td>Salário base</td><td class="right">{{ format_currency((float) $payslip->base_salary) }}</td></tr>
        @if((float) $payslip->allowances > 0)
        <tr><td>Subsídios</td><td class="right add">+ {{ format_currency((float) $payslip->allowances) }}</td></tr>
        @endif
        <tr><td>Remuneração bruta</td><td class="right">{{ format_currency((float) $payslip->gross) }}</td></tr>
        <tr><td>INSS (3%)</td><td class="right sub">− {{ format_currency((float) $payslip->inss_employee) }}</td></tr>
        <tr><td>IRT</td><td class="right sub">− {{ format_currency((float) $payslip->irt) }}</td></tr>
        <tr class="net"><td>Líquido a receber</td><td class="right">{{ format_currency((float) $payslip->net) }}</td></tr>
    </table>

    <div class="foot">
        Documento gerado por Welwitschia ERP · welwitschia.ao
        <br>// Tabelas IRT/INSS a validar com consultor fiscal/laboral AO
    </div>
</body>
</html>
