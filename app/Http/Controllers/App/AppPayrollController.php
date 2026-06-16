<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\Hr\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AppPayrollController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('App/Hr/Payrolls', [
            'payrolls' => Payroll::latest('year')->latest('month')->get()->map(fn (Payroll $p) => [
                'id' => $p->id,
                'year' => $p->year,
                'month' => $p->month,
                'total_gross' => (float) $p->total_gross,
                'total_inss' => (float) $p->total_inss,
                'total_irt' => (float) $p->total_irt,
                'total_net' => (float) $p->total_net,
            ]),
            'currentYear' => (int) Carbon::now()->format('Y'),
            'currentMonth' => (int) Carbon::now()->format('n'),
            'employeesCount' => Employee::active()->count(),
        ]);
    }

    public function store(Request $request, PayrollService $payroll): RedirectResponse
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        // process() lança ValidationException se o período já existir (tratado pelo framework).
        $created = $payroll->process($data['year'], $data['month']);

        return redirect()->route('app.payrolls.show', $created->id)->with('success', 'Folha processada.');
    }

    public function show(Payroll $payroll): Response
    {
        return Inertia::render('App/Hr/PayrollShow', [
            'payroll' => [
                'id' => $payroll->id,
                'year' => $payroll->year,
                'month' => $payroll->month,
                'total_gross' => (float) $payroll->total_gross,
                'total_inss' => (float) $payroll->total_inss,
                'total_irt' => (float) $payroll->total_irt,
                'total_net' => (float) $payroll->total_net,
            ],
            'payslips' => $payroll->payslips()->with('employee')->get()->map(fn ($s) => [
                'employee' => $s->employee?->name,
                'gross' => (float) $s->gross,
                'inss' => (float) $s->inss_employee,
                'irt' => (float) $s->irt,
                'net' => (float) $s->net,
            ]),
        ]);
    }
}
