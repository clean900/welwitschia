<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Clientes (CRM) — /app/clientes.
 */
class AppCustomerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('App/Customers/Index', [
            'customers' => Customer::orderBy('name')->paginate(20)->through(fn (Customer $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'nif' => $c->nif,
                'email' => $c->email,
                'phone' => $c->phone,
                'credit_limit' => (float) $c->credit_limit,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nif' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        Customer::create($data + ['status' => 'active']);

        return back()->with('success', 'Cliente adicionado.');
    }

    public function destroy(string $customer): RedirectResponse
    {
        // Resolução manual (contexto de tenant).
        Customer::findOrFail($customer)->delete();

        return back()->with('success', 'Cliente removido.');
    }
}
