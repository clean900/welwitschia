<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppEmployeeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('App/Hr/Employees', [
            'employees' => Employee::orderBy('name')->paginate(20)->through(fn (Employee $e) => [
                'id' => $e->id,
                'name' => $e->name,
                'position' => $e->position,
                'base_salary' => (float) $e->base_salary,
                'allowances' => (float) $e->allowances,
                'status' => $e->status,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nif' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'phone' => 'nullable|string|max:20',
        ]);

        Employee::create($data + ['status' => 'active']);

        return back()->with('success', 'Colaborador adicionado.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->update(['status' => 'terminated']);

        return back()->with('success', 'Colaborador desactivado.');
    }
}
