<?php

namespace App\Services\Accounting;

use App\Exceptions\UnbalancedJournalEntry;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Razão em partidas dobradas (PGC Angola). Garante débito = crédito.
 */
class AccountingService
{
    /**
     * @param  array<int, array{account:string, debit?:float, credit?:float}>  $lines
     */
    public function post(string $description, array $lines, ?DateTimeInterface $date = null, ?Model $source = null, ?string $reference = null): JournalEntry
    {
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($lines as $line) {
            $totalDebit += round((float) ($line['debit'] ?? 0), 2);
            $totalCredit += round((float) ($line['credit'] ?? 0), 2);
        }
        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);

        if ($totalDebit <= 0 || $totalDebit !== $totalCredit) {
            throw new UnbalancedJournalEntry("Lançamento desequilibrado: débito {$totalDebit} ≠ crédito {$totalCredit}.");
        }

        return DB::transaction(function () use ($description, $lines, $date, $source, $reference, $totalDebit, $totalCredit) {
            $entry = JournalEntry::create([
                'entry_date' => $date ?? now(),
                'description' => $description,
                'reference' => $reference,
                'source_type' => $source ? $source::class : null,
                'source_id' => $source?->getKey(),
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            foreach ($lines as $line) {
                if (! Account::where('code', $line['account'])->exists()) {
                    throw new InvalidArgumentException("Conta {$line['account']} inexistente no plano de contas.");
                }

                $entry->lines()->create([
                    'account_code' => $line['account'],
                    'debit' => round((float) ($line['debit'] ?? 0), 2),
                    'credit' => round((float) ($line['credit'] ?? 0), 2),
                ]);
            }

            AuditLog::record('accounting.entry_posted', [
                'description' => $description,
                'amount' => $totalDebit,
                'reference' => $reference,
            ], JournalEntry::class, $entry->id);

            return $entry->load('lines');
        });
    }

    /** Balancete geral: total de débitos e créditos (devem ser iguais). */
    public function trialBalance(): array
    {
        return [
            'debit' => round((float) JournalLine::sum('debit'), 2),
            'credit' => round((float) JournalLine::sum('credit'), 2),
        ];
    }
}
