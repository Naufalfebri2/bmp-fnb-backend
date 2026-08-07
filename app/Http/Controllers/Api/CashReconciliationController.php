<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashReconciliation;
use App\Services\ReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class CashReconciliationController extends Controller
{
    public function index(Request $request, string $cashAccountId)
    {
        $cashAccount = $this->findOwnedCashAccount($request, $cashAccountId);

        if (!$cashAccount) {
            return response()->json(['message' => 'Cash account not found'], 404);
        }

        $reconciliations = $cashAccount->reconciliations()
            ->with(['reconciledBy', 'approvedBy'])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($reconciliations);
    }

    public function store(Request $request, string $cashAccountId)
    {
        $cashAccount = $this->findOwnedCashAccount($request, $cashAccountId);

        if (!$cashAccount) {
            return response()->json(['message' => 'Cash account not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'physical_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $reconciliation = ReconciliationService::submit(
                $cashAccount,
                $request->physical_balance,
                $request->user(),
                $request->notes
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Reconciliation submitted successfully',
            'reconciliation' => $reconciliation,
        ], 201);
    }

    public function approve(Request $request, string $cashAccountId, string $reconciliationId)
    {
        $reconciliation = $this->findOwnedReconciliation($request, $cashAccountId, $reconciliationId);

        if (!$reconciliation) {
            return response()->json(['message' => 'Reconciliation not found'], 404);
        }

        try {
            $reconciliation = ReconciliationService::approve($reconciliation, $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Reconciliation approved successfully',
            'reconciliation' => $reconciliation->load('adjustmentTransaction'),
        ]);
    }

    public function reject(Request $request, string $cashAccountId, string $reconciliationId)
    {
        $reconciliation = $this->findOwnedReconciliation($request, $cashAccountId, $reconciliationId);

        if (!$reconciliation) {
            return response()->json(['message' => 'Reconciliation not found'], 404);
        }

        try {
            $reconciliation = ReconciliationService::reject($reconciliation, $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Reconciliation rejected',
            'reconciliation' => $reconciliation,
        ]);
    }

    private function findOwnedReconciliation(Request $request, string $cashAccountId, string $reconciliationId): ?CashReconciliation
    {
        $cashAccount = $this->findOwnedCashAccount($request, $cashAccountId);

        if (!$cashAccount) {
            return null;
        }

        return $cashAccount->reconciliations()->find($reconciliationId);
    }

    private function findOwnedCashAccount(Request $request, string $cashAccountId): ?CashAccount
    {
        $cashAccount = CashAccount::whereHas('outlet', function ($query) use ($request) {
            $query->where('tenant_id', $request->user()->tenant_id);
        })->find($cashAccountId);

        if (!$cashAccount) {
            return null;
        }

        $userOutletId = $request->user()->outlet_id;

        if ($userOutletId && $userOutletId !== $cashAccount->outlet_id) {
            return null;
        }

        return $cashAccount;
    }
}