<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Services\CashTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CashTransactionController extends Controller
{
    public function index(Request $request, string $cashAccountId)
    {
        $cashAccount = $this->findOwnedCashAccount($request, $cashAccountId);

        if (!$cashAccount) {
            return response()->json(['message' => 'Cash account not found'], 404);
        }

        return response()->json(
            $cashAccount->cashTransactions()->orderBy('date', 'desc')->get()
        );
    }

    public function store(Request $request, string $cashAccountId)
    {
        $cashAccount = $this->findOwnedCashAccount($request, $cashAccountId);

        if (!$cashAccount) {
            return response()->json(['message' => 'Cash account not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $transaction = CashTransactionService::record(
            $cashAccount,
            $request->date,
            $request->type,
            'manual',
            $request->amount,
            $request->notes
        );

        return response()->json([
            'message' => 'Cash transaction recorded successfully',
            'cash_transaction' => $transaction,
        ], 201);
    }

    private function findOwnedCashAccount(Request $request, string $cashAccountId): ?CashAccount
    {
        return CashAccount::whereHas('outlet', function ($query) use ($request) {
            $query->where('tenant_id', $request->user()->tenant_id);
        })->find($cashAccountId);
    }
}
