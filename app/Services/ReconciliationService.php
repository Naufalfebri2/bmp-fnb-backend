<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\CashReconciliation;
use App\Models\User;
use App\Services\CashTransactionService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReconciliationService
{
    public static function submit(CashAccount $cashAccount, float $physicalBalance, User $reconciledBy, ?string $notes = null): CashReconciliation
    {
        $today = now()->toDateString();

        $alreadyExists = CashReconciliation::where('cash_account_id', $cashAccount->id)
            ->where('date', $today)
            ->exists();

        if ($alreadyExists) {
            throw new InvalidArgumentException('This cash account has already been reconciled today.');
        }

        $systemBalance = self::calculateSystemBalance($cashAccount);
        $difference = round($physicalBalance - $systemBalance, 2);

        return CashReconciliation::create([
            'cash_account_id' => $cashAccount->id,
            'date' => $today,
            'system_balance' => $systemBalance,
            'physical_balance' => $physicalBalance,
            'difference' => $difference,
            'status' => $difference == 0 ? 'completed' : 'pending_approval',
            'reconciled_by' => $reconciledBy->id,
            'notes' => $notes,
        ]);
    }

    public static function approve(CashReconciliation $reconciliation, User $approvedBy): CashReconciliation
    {
        if ($reconciliation->status !== 'pending_approval') {
            throw new InvalidArgumentException("Reconciliation is in status '{$reconciliation->status}' and cannot be approved.");
        }

        $adjustment = DB::transaction(function () use ($reconciliation, $approvedBy) {
            $adjustment = CashTransactionService::record(
                $reconciliation->cashAccount,
                $reconciliation->date->toDateString(),
                $reconciliation->difference > 0 ? 'in' : 'out',
                'adjustment',
                abs($reconciliation->difference),
                "Cash reconciliation adjustment for {$reconciliation->date->toDateString()}"
            );

            $reconciliation->update([
                'status' => 'completed',
                'approved_by' => $approvedBy->id,
                'adjustment_transaction_id' => $adjustment->id,
            ]);

            return $adjustment;
        });

        return $reconciliation->fresh();
    }

    public static function reject(CashReconciliation $reconciliation, User $approvedBy): CashReconciliation
    {
        if ($reconciliation->status !== 'pending_approval') {
            throw new InvalidArgumentException("Reconciliation is in status '{$reconciliation->status}' and cannot be rejected.");
        }

        $reconciliation->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy->id,
        ]);

        return $reconciliation->fresh();
    }

    private static function calculateSystemBalance(CashAccount $cashAccount): float
    {
        $totalIn = $cashAccount->cashTransactions()->where('type', 'in')->sum('amount');
        $totalOut = $cashAccount->cashTransactions()->where('type', 'out')->sum('amount');

        return round($totalIn - $totalOut, 2);
    }
}