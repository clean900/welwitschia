<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Employee::orderBy('name')->paginate(30));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateEmployee($request);

        return response()->json(Employee::create($data), 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json($employee);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $employee->update($this->validateEmployee($request, $employee));

        return response()->json($employee);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->update(['status' => 'terminated']);

        return response()->json(['status' => 'terminated']);
    }

    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'name' => ($employee ? 'sometimes|' : 'required|') . 'string|max:255',
            'nif' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'base_salary' => ($employee ? 'sometimes|' : 'required|') . 'numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'bank_account' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,terminated',
        ]);
    }
}
