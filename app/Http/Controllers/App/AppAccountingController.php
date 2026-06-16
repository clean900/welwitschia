<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Accounting\AccountingService;
use Inertia\Inertia;
use Inertia\Response;

class AppAccountingController extends Controller
{
    public function trialBalance(AccountingService $accounting): Response
    {
        $accounts = Account::where('is_postable', true)->orderBy('code')->get()
            ->map(fn (Account $a) => [
                'code' => $a->code,
                'name' => $a->name,
                'balance' => $a->balance(),
            ])
            ->filter(fn ($a) => $a['balance'] != 0.0)
            ->values();

        return Inertia::render('App/Accounting/TrialBalance', [
            'totals' => $accounting->trialBalance(),
            'accounts' => $accounts,
        ]);
    }

    public function journal(): Response
    {
        return Inertia::render('App/Accounting/Journal', [
            'entries' => JournalEntry::with('lines')->latest('entry_date')->latest('id')->paginate(20)
                ->through(fn (JournalEntry $e) => [
                    'id' => $e->id,
                    'date' => $e->entry_date?->format('Y-m-d'),
                    'description' => $e->description,
                    'reference' => $e->reference,
                    'total' => (float) $e->total_debit,
                    'lines' => $e->lines->map(fn ($l) => [
                        'account' => $l->account_code,
                        'debit' => (float) $l->debit,
                        'credit' => (float) $l->credit,
                    ]),
                ]),
        ]);
    }
}
