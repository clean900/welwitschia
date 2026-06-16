<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPartner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestão dos parceiros/clientes exibidos na landing ("empresas que confiam").
 */
class AdminPartnerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Partners', [
            'partners' => LandingPartner::orderBy('sort_order')->orderBy('name')
                ->get(['id', 'name', 'logo_url', 'active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'logo_url' => 'nullable|url|max:500',
        ]);

        LandingPartner::create($data + ['sort_order' => (LandingPartner::max('sort_order') ?? 0) + 1]);

        return back()->with('success', 'Parceiro adicionado.');
    }

    public function toggle(LandingPartner $partner): RedirectResponse
    {
        $partner->update(['active' => ! $partner->active]);

        return back();
    }

    public function destroy(LandingPartner $partner): RedirectResponse
    {
        $partner->delete();

        return back()->with('success', 'Parceiro removido.');
    }
}
