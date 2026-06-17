<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppSupplierController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('App/Suppliers/Index', [
            'suppliers' => Supplier::orderBy('name')->paginate(20)->through(fn (Supplier $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'nif' => $s->nif,
                'email' => $s->email,
                'phone' => $s->phone,
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
        ]);

        Supplier::create($data + ['status' => 'active']);

        return back()->with('success', 'Fornecedor adicionado.');
    }

    public function destroy(string $supplier): RedirectResponse
    {
        Supplier::findOrFail($supplier)->delete();

        return back()->with('success', 'Fornecedor removido.');
    }
}
