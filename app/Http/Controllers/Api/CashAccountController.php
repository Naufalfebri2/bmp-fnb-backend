<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CashAccountController extends Controller
{
    public function index(Request $request, string $outletId)
    {
        $outlet = $this->findOwnedOutlet($request, $outletId);

        if (!$outlet) {
            return response()->json(['message' => 'Outlet not found'], 404);
        }

        $accounts = $outlet->cashAccounts()->get()->map(function ($account) {
            $totalIn = $account->cashTransactions()->where('type', 'in')->sum('amount');
            $totalOut = $account->cashTransactions()->where('type', 'out')->sum('amount');

            $account->balance = $totalIn - $totalOut;

            return $account;
        });

        return response()->json($accounts);
    }

    public function store(Request $request, string $outletId)
    {
        $outlet = $this->findOwnedOutlet($request, $outletId);

        if (!$outlet) {
            return response()->json(['message' => 'Outlet not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'type' => 'required|in:cash,bank',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $account = $outlet->cashAccounts()->create([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return response()->json([
            'message' => 'Cash account created successfully',
            'cash_account' => $account,
        ], 201);
    }

    private function findOwnedOutlet(Request $request, string $outletId): ?Outlet
    {
        return Outlet::where('tenant_id', $request->user()->tenant_id)->find($outletId);
    }
}
