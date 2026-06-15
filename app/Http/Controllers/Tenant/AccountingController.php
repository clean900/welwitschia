<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Accounting\AccountingService;
use Illuminate\Http\JsonResponse;

class AccountingController extends Controller
{
    public function journal(): JsonResponse
    {
        return response()->json(
            JournalEntry::with('lines')->latest('entry_date')->latest('id')->paginate(30)
        );
    }

    public function trialBalance(AccountingService $accounting): JsonResponse
    {
        $accounts = Account::where('is_postable', true)
            ->orderBy('code')
            ->get()
            ->map(fn (Account $a) => [
                'code' => $a->code,
                'name' => $a->name,
                'balance' => $a->balance(),
            ])
            ->filter(fn ($a) => $a['balance'] != 0.0)
            ->values();

        return response()->json([
            'totals' => $accounting->trialBalance(),
            'accounts' => $accounts,
        ]);
    }
}
