<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Services\Hr\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Payroll::latest('year')->latest('month')->paginate(24));
    }

    public function store(Request $request, PayrollService $payroll): JsonResponse
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        return response()->json($payroll->process($data['year'], $data['month']), 201);
    }

    public function show(Payroll $payroll): JsonResponse
    {
        return response()->json($payroll->load('payslips.employee'));
    }
}
